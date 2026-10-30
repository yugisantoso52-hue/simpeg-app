<?php

namespace App\Http\Requests\Auth;

use App\Models\ActivityLog;
use App\Models\Pegawai;
use App\Models\Role;
use App\Models\User;
use App\Services\ActivityLoggerService;
use Carbon\Carbon;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
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

        $rawInput = trim((string)$this->input('login'));
        $cleanNip = preg_replace('/[^0-9]/', '', $rawInput);
        $inputPassword = (string)$this->input('password');
        $ip = $this->ip();
        $accountKey = Str::lower($cleanNip ?: $rawInput);

        // 1. Cari Pegawai di database
        $pegawai = null;
        if (!empty($cleanNip)) {
            $pegawai = Pegawai::where('nip', $rawInput)
                ->orWhere('nip', $cleanNip)
                ->orWhere('nip', 'like', '%' . $cleanNip . '%')
                ->first();

            if (!$pegawai) {
                try {
                    $pegawai = Pegawai::whereRaw("REPLACE(REPLACE(nip, ' ', ''), '.', '') = ?", [$cleanNip])->first();
                } catch (\Throwable $e) {}
            }
        }

        if (!$pegawai && filter_var($rawInput, FILTER_VALIDATE_EMAIL)) {
            $pegawai = Pegawai::where('email', $rawInput)->first();
        }

        // 2. Cari User akun login
        $user = null;
        if ($pegawai) {
            $user = User::where('pegawai_id', $pegawai->id)->first();
        }

        if (!$user) {
            $user = User::where('email', $rawInput)
                ->orWhere('name', $rawInput)
                ->orWhere(function ($q) use ($rawInput) {
                    if (in_array(strtolower($rawInput), ['admin', 'administrator'])) {
                        $q->whereHas('role', function ($r) {
                            $r->where('name', 'admin');
                        })->orWhere('email', 'like', 'admin%');
                    }
                })
                ->when(!empty($cleanNip), function ($q) use ($cleanNip) {
                    $q->orWhere('email', $cleanNip . '@staff.unri.ac.id')
                      ->orWhere('email', $cleanNip . '@simpeg.test')
                      ->orWhere('name', $cleanNip);
                })
                ->first();
        }

        // 3. Verifikasi apakah akun sedang dalam status DIBEKUKAN (Suspended)
        if ($user && $user->is_suspended) {
            ActivityLoggerService::log(
                activityType: 'SECURITY_BLOCKED',
                description: "Percobaan login pada akun [{$user->name}] ({$rawInput}) ditolak karena akun sedang dibekukan (Suspended). IP: {$ip}",
                user: $user
            );

            throw ValidationException::withMessages([
                'login' => 'Akun Anda sedang DIBEKUKAN SEMENTARA (Suspended) demi keamanan: ' . ($user->suspended_reason ?? 'Terdeteksi percobaan akses yang mencurigakan.') . ' Silakan hubungi Administrator Kepegawaian FKP UNRI untuk mengaktifkan kembali akun Anda.',
            ]);
        }

        // 4. Jika data pegawai ada tetapi akun user belum ada, buatkan on-the-fly
        $rolePegawai = Role::where('name', 'pegawai')->first();
        $roleId = $rolePegawai ? $rolePegawai->id : 2;

        if ($pegawai && !$user) {
            $emailUser = !empty($cleanNip) ? $cleanNip . '@staff.unri.ac.id' : 'pegawai_' . $pegawai->id . '@staff.unri.ac.id';
            $user = User::create([
                'name'                 => $pegawai->nama,
                'email'                => $emailUser,
                'password'             => Hash::make('Password'),
                'role_id'              => $roleId,
                'pegawai_id'           => $pegawai->id,
                'must_change_password' => true,
            ]);
        }

        // 5. Verifikasi Password yang Aman
        $passwordValid = false;

        if ($user) {
            // A. Cek password hash terdaftar
            if (Hash::check($inputPassword, $user->password)) {
                $passwordValid = true;
            } 
            // B. Self-healing HANYA jika pegawai baru belum pernah mengganti password pertama kali
            elseif ($user->must_change_password && ($user->pegawai_id || ($user->role && $user->role->name === 'pegawai'))) {
                $dob = null;
                if ($pegawai && $pegawai->tanggal_lahir) {
                    try {
                        $dob = Carbon::parse($pegawai->tanggal_lahir)->format('Ymd');
                    } catch (\Throwable $e) {}
                }

                $acceptableDefaults = array_filter([
                    'Password',
                    'password',
                    '19900101',
                    $cleanNip,
                    $dob,
                ]);

                if (in_array($inputPassword, $acceptableDefaults, true)) {
                    $user->password = Hash::make('Password');
                    $user->save();
                    $passwordValid = true;
                }
            }
        }

        // 6. Penanganan Percobaan Login GAGAL (Progressive Lockout & IP Limiter)
        if (!$user || !$passwordValid) {
            $this->handleFailedAttempt($rawInput, $cleanNip, $accountKey, $user);
        }

        // 7. Login Berhasil: Reset Kunci & Catat Log Aktivitas
        $this->clearSecurityLimits($accountKey, $user);

        ActivityLoggerService::logLogin($user);
        Auth::login($user, $this->boolean('remember'));
    }

    /**
     * Memastikan request tidak terkena blokir IP Global atau Lockout Akun.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        $ip = $this->ip();
        $rawInput = trim((string)$this->input('login'));
        $cleanNip = preg_replace('/[^0-9]/', '', $rawInput);
        $accountKey = Str::lower($cleanNip ?: $rawInput);

        // A. Cek Pembatasan Global Berbasis IP (Rencana 3: Anti-Bot / Credential Spraying)
        $ipLockKey = 'security:ip_lockout:' . $ip;
        if (Cache::has($ipLockKey)) {
            $expiresAt = Cache::get($ipLockKey);
            $remaining = max(1, $expiresAt - now()->timestamp);
            $minutes = ceil($remaining / 60);

            throw ValidationException::withMessages([
                'login' => "Akses dari perangkat/alamat IP ini diblokir sementara selama {$minutes} menit demi keamanan karena terdeteksi banyak percobaan login yang gagal.",
            ]);
        }

        // B. Cek Progressive Lockout pada Akun / NIP Ini (Rencana 1)
        $accountLockKey = 'security:account_lockout:' . $accountKey;
        if (Cache::has($accountLockKey)) {
            $expiresAt = Cache::get($accountLockKey);
            $remaining = max(1, $expiresAt - now()->timestamp);

            event(new Lockout($this));

            if ($remaining > 3600) {
                $hours = ceil($remaining / 3600);
                $message = "Akun Anda dikunci selama {$hours} jam dan dibekukan sementara karena terdeteksi 10 kali percobaan sandi salah. Silakan hubungi Administrator Kepegawaian FKP UNRI untuk bantuan pemulihan.";
            } else {
                $minutes = ceil($remaining / 60);
                $message = "Akses ke akun ini dikunci sementara selama {$minutes} menit ({$remaining} detik) karena beberapa kali gagal login. Silakan tunggu sebelum mencoba kembali.";
            }

            throw ValidationException::withMessages([
                'login' => $message,
            ]);
        }
    }

    /**
     * Penanganan Percobaan Gagal Sesuai Spesifikasi Progressive Lockout:
     * - Gagal 3x  -> Kunci 2 menit
     * - Gagal 5x  -> Kunci 15 menit
     * - Gagal 10x -> Kunci 24 jam & Akun dibekukan (Suspended)
     */
    protected function handleFailedAttempt(string $rawInput, ?string $cleanNip, string $accountKey, ?User $user): void
    {
        $ip = $this->ip();

        // 1. Rekam jejak gagal IP Global (Batas 10x gagal dalam 15 menit per IP)
        $ipFailKey = 'security:ip_fails:' . $ip;
        $ipLockKey = 'security:ip_lockout:' . $ip;
        $ipFails = (int) Cache::get($ipFailKey, 0) + 1;
        Cache::put($ipFailKey, $ipFails, now()->addMinutes(15));

        if ($ipFails >= 10) {
            Cache::put($ipLockKey, now()->addMinutes(15)->timestamp, now()->addMinutes(15));
            ActivityLoggerService::log(
                activityType: 'SECURITY_IP_BLOCK',
                description: "Alamat IP [{$ip}] diblokir selama 15 menit karena mencapai {$ipFails} kali percobaan login gagal terhadap akun di sistem (indikasi brute-force)."
            );
        }

        // 2. Rekam jejak gagal per Akun / NIP
        $accountFailKey = 'security:account_fails:' . $accountKey;
        $accountLockKey = 'security:account_lockout:' . $accountKey;
        $fails = (int) Cache::get($accountFailKey, 0) + 1;
        Cache::put($accountFailKey, $fails, now()->addHours(24));

        event(new Lockout($this));

        // Kasus A: Gagal >= 10 kali (Kunci 24 jam & Akun dibekukan)
        if ($fails >= 10) {
            $lockSeconds = 86400; // 24 jam
            Cache::put($accountLockKey, now()->addSeconds($lockSeconds)->timestamp, now()->addSeconds($lockSeconds));

            if ($user) {
                $user->update([
                    'is_suspended'     => true,
                    'suspended_at'     => now(),
                    'suspended_reason' => 'Terdeteksi 10 kali percobaan kata sandi salah berturut-turut pada ' . now()->translatedFormat('d F Y H:i:s'),
                ]);
            }

            ActivityLoggerService::log(
                activityType: 'SECURITY_SUSPEND',
                description: "Akun [{$rawInput}] dibekukan (Suspended) dan dikunci selama 24 jam karena mencapai 10 kali percobaan kata sandi salah. IP: {$ip}",
                user: $user
            );

            throw ValidationException::withMessages([
                'login' => 'Akun Anda telah DIBEKUKAN SEMENTARA dan dikunci selama 24 jam karena telah 10 kali gagal memasukkan kata sandi. Silakan hubungi Administrator Kepegawaian FKP UNRI untuk pemulihan akun.',
            ]);
        }

        // Kasus B: Gagal >= 5 kali (Kunci 15 menit)
        if ($fails >= 5) {
            $lockSeconds = 900; // 15 menit
            Cache::put($accountLockKey, now()->addSeconds($lockSeconds)->timestamp, now()->addSeconds($lockSeconds));

            ActivityLoggerService::log(
                activityType: 'SECURITY_LOCKOUT',
                description: "Akun [{$rawInput}] dikunci selama 15 menit karena mencapai 5 kali percobaan kata sandi salah. IP: {$ip}",
                user: $user
            );

            throw ValidationException::withMessages([
                'login' => 'Terlalu banyak percobaan kata sandi salah (5 kali). Demi keamanan, akses ke akun ini dikunci selama 15 menit. Silakan tunggu.',
            ]);
        }

        // Kasus C: Gagal >= 3 kali (Kunci 2 menit)
        if ($fails >= 3) {
            $lockSeconds = 120; // 2 menit
            Cache::put($accountLockKey, now()->addSeconds($lockSeconds)->timestamp, now()->addSeconds($lockSeconds));

            ActivityLoggerService::log(
                activityType: 'SECURITY_LOCKOUT',
                description: "Akun [{$rawInput}] dikunci selama 2 menit karena mencapai 3 kali percobaan kata sandi salah. IP: {$ip}",
                user: $user
            );

            throw ValidationException::withMessages([
                'login' => 'Terlalu banyak percobaan kata sandi salah (3 kali). Demi keamanan, akses ke akun ini dikunci selama 2 menit. Silakan tunggu sebelum mencoba lagi.',
            ]);
        }

        // Kasus D: Gagal ke-1 atau ke-2
        $sisaPercobaan = 3 - $fails;
        throw ValidationException::withMessages([
            'login' => "NIP/Username atau kata sandi yang Anda masukkan salah. (Percobaan ke-{$fails}. Peringatan: Akses akan dikunci jika gagal {$sisaPercobaan} kali lagi).",
        ]);
    }

    /**
     * Bersihkan batas keamanan saat berhasil login.
     */
    protected function clearSecurityLimits(string $accountKey, ?User $user): void
    {
        Cache::forget('security:account_fails:' . $accountKey);
        Cache::forget('security:account_lockout:' . $accountKey);

        if ($user && $user->email) {
            $emailKey = Str::lower($user->email);
            Cache::forget('security:account_fails:' . $emailKey);
            Cache::forget('security:account_lockout:' . $emailKey);
        }
    }
}

