<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Jika pengguna belum login
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Tangani jika role dikirim sebagai string terpisah koma (contoh: 'admin,pimpinan')
        $flatRoles = [];
        foreach ($roles as $role) {
            $flatRoles = array_merge($flatRoles, explode(',', $role));
        }
        $flatRoles = array_map('trim', $flatRoles);

        // Cek apakah user memiliki salah satu dari role yang diizinkan
        if (!$request->user()->hasRole($flatRoles)) {
            abort(403, 'Anda tidak memiliki hak akses untuk halaman ini.');
        }

        return $next($request);
    }
}