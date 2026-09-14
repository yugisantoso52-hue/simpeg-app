<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
        ]);

        $loginInput = $request->input('login');
        $loginClean = str_replace(' ', '', trim($loginInput));

        // Cari user berdasarkan email atau NIP pegawai yang terkait
        $user = \App\Models\User::where('email', $loginInput)
            ->orWhereHas('pegawai', function ($query) use ($loginClean) {
                $query->where('nip', $loginClean);
            })
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'login' => 'NIP atau alamat email tidak terdaftar dalam sistem.',
            ]);
        }

        // Kirim reset link ke email pengguna yang ditemukan
        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('login'))
                        ->withErrors(['login' => __($status)]);
    }
}
