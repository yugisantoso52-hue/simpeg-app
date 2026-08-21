<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->must_change_password) {
                // Pengecualian rute agar tidak terjadi loop redirect:
                // - /change-password (GET & POST)
                // - /logout (POST)
                if (!$request->is('change-password*') &&
                    !$request->routeIs('password.change') &&
                    !$request->is('logout') &&
                    !$request->routeIs('logout')) {

                    return redirect()->route('password.change')
                        ->with('warning', 'Anda wajib mengganti password default Anda saat login pertama kali demi keamanan akun.');
                }
            }
        }

        return $next($request);
    }
}
