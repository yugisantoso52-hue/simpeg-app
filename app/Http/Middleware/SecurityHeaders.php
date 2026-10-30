<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and apply security hardening headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. MIME type sniffing prevention
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 2. Clickjacking prevention
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 3. Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 4. Permissions policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // 5. Content Security Policy (CSP) khusus untuk respon dokumen HTML yang bukan redirect
        $contentType = $response->headers->get('Content-Type', '');
        $isHtml = empty($contentType) || str_contains($contentType, 'text/html');

        if ($isHtml && ! $response->isRedirection()) {
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.tailwindcss.com",
                "style-src 'self' 'unsafe-inline' https://unpkg.com https://fonts.googleapis.com",
                "font-src 'self' data: https://fonts.gstatic.com",
                "img-src 'self' data: blob: https://*.tile.openstreetmap.org https://unpkg.com https://*.unri.ac.id",
                "connect-src 'self' blob: https://*.tile.openstreetmap.org",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ];

            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        // 6. Optimasi Redirect (Cegah ZAP 'Big Redirect Detected / Information Leak' pada HTTP 302)
        if ($response->isRedirection()) {
            $response->setContent('');
        }

        // 7. Pastikan seluruh Cookie memiliki proteksi HttpOnly (Mengatasi ZAP 'Cookie No HttpOnly Flag')
        foreach ($response->headers->getCookies() as $cookie) {
            if (!$cookie->isHttpOnly()) {
                $response->headers->setCookie($cookie->withHttpOnly(true));
            }
        }

        // 8. Perlindungan HSTS pada koneksi HTTPS (Mengatasi ZAP 'Strict-Transport-Security Header Not Set')
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // 9. Manajemen Cache Ketat pada halaman login/akun (Mengatasi ZAP 'Re-examine Cache-control Directives')
        if ($request->is('login', 'logout', 'change-password*', 'profile*')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
        }

        // 10. Sembunyikan informasi server & versi PHP (Mengatasi ZAP 'Server Leaks Version' & 'PHP Version')
        $response->headers->set('Server', 'Web Server');
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        return $response;
    }
}
