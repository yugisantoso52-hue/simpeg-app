<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $cleanNip = preg_replace('/[^0-9]/', '', (string)$this->input('login'));
        $rawInput = $this->input('login');

        // 1. Cari Pegawai terlebih dahulu jika input adalah NIP (numeric)
        $pegawai = null;
        if (!empty($cleanNip)) {
            $pegawai = \App\Models\Pegawai::whereRaw("REPLACE(REPLACE(nip, ' ', ''), CHAR(39), '') = ?", [$cleanNip])->first();
        }

        // 2. Jika pegawai ditemukan, pastikan akun User-nya ada dan tersinkronisasi (On-the-Fly Sync)
        if ($pegawai) {
            $rolePegawai = \App\Models\Role::where('name', 'pegawai')->first();
            $emailTemp = $pegawai->nip . '@staff.unri.ac.id';

            $user = \App\Models\User::where('pegawai_id', $pegawai->id)
                ->orWhere('email', $emailTemp)
                ->first();

            $dob = '19900101';
            if ($pegawai->tanggal_lahir) {
                try {
                    $dob = \Carbon\Carbon::parse($pegawai->tanggal_lahir)->format('Ymd');
                } catch (\Exception $e) {
                    $dob = '19900101';
                }
            }

            if ($user) {
                // Update jika data pegawai berubah
                $user->name = $pegawai->nama;
                $user->email = $emailTemp;
                $user->pegawai_id = $pegawai->id;
                $user->role_id = $user->role_id ?? ($rolePegawai->id ?? 2);
                if ($user->must_change_password) {
                    $user->password = \Illuminate\Support\Facades\Hash::make($dob);
                }
                $user->save();
            } else {
                // Buat user baru secara otomatis
                \App\Models\User::create([
                    'name'                 => $pegawai->nama,
                    'email'                => $emailTemp,
                    'password'             => \Illuminate\Support\Facades\Hash::make($dob),
                    'role_id'              => $rolePegawai->id ?? 2,
                    'pegawai_id'           => $pegawai->id,
                    'must_change_password' => true,
                ]);
            }
        }

        // 3. Cari User untuk proses login
        $user = \App\Models\User::where('email', $rawInput)
            ->orWhere('email', $cleanNip . '@staff.unri.ac.id')
            ->when($pegawai, function ($query) use ($pegawai) {
                $query->orWhere('pegawai_id', $pegawai->id);
            })
            ->first();

        // 4. Verifikasi Kredensial
        if (!$user || !\Illuminate\Support\Facades\Hash::check($this->input('password'), $user->password)) {
            \Illuminate\Support\Facades\RateLimiter::hit($this->throttleKey());

            throw \Illuminate\Validation\ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        \Illuminate\Support\Facades\Auth::login($user, $this->boolean('remember'));
        \Illuminate\Support\Facades\RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login')).'|'.$this->ip());
    }
}
