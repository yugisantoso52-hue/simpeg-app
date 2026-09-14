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
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
            'must_change_password' => false,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Password Anda berhasil diperbarui. Sekarang Anda dapat menggunakan seluruh fitur SIMPEG.');
    }

    /**
     * Lewati penggantian password default (untuk pengujian atau ditunda).
     */
    public function skip(Request $request): RedirectResponse
    {
        $request->user()->update([
            'must_change_password' => false,
        ]);

        return redirect()->route('dashboard')
            ->with('info', 'Penggantian password dilewati. Anda dapat mengganti password kapan saja melalui menu Profil.');
    }
}
