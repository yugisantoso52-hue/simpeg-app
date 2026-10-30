<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false, // Bersihkan flag jika diganti lewat profile
        ]);

        return back()->with('status', 'password-updated');
    }

    /**
     * Tampilkan form ganti password pertama kali.
     */
    public function edit(Request $request)
    {
        return view('auth.change-password');
    }

    /**
     * Simpan password baru pertama kali (setelah login pertama).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'string',
                Password::min(8)->letters()->numbers(),
                'different:current_password',
                'confirmed',
            ],
        ], [
            'password.min' => 'Kata sandi baru minimal harus terdiri dari 8 karakter.',
            'password.letters' => 'Kata sandi baru harus mengandung setidaknya satu huruf.',
            'password.numbers' => 'Kata sandi baru harus mengandung setidaknya satu angka.',
            'password.different' => 'Kata sandi baru tidak boleh sama dengan kata sandi saat ini / bawaan sistem.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'current_password.current_password' => 'Kata sandi saat ini / bawaan yang Anda masukkan salah.',
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        \App\Services\ActivityLoggerService::log(
            activityType: 'SECURITY_PASSWORD_CHANGED',
            description: "Pengguna {$user->name} ({$user->email}) berhasil memperbarui kata sandi default menjadi kata sandi pribadi yang aman.",
            subjectType: 'User',
            subjectId: $user->id,
            user: $user
        );

        return redirect()->route('dashboard')
            ->with('success', 'Kata sandi Anda berhasil diperbarui. Selamat datang di SIKAP FKP UNRI.');
    }

    /**
     * Penolakan lewati penggantian password default demi kepatuhan keamanan.
     */
    public function skip(Request $request): RedirectResponse
    {
        return redirect()->route('password.change')
            ->with('warning', 'Penggantian kata sandi bawaan wajib dilakukan demi menjaga keamanan akun Anda.');
    }
}
