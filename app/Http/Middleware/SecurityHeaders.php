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

        // 1. Strict Transport Security (HSTS)
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            $response->headers->set('Strict-Transport-Security', 'max-age=63072000; includeSubDomains; preload');
        }

        // 2. MIME type sniffing prevention
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3. Clickjacking prevention
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 4. Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Permissions policy
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // 6. Content Security Policy (CSP)
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

        // 7. Optimasi Redirect (Cegah ZAP 'Big Redirect Detected / Information Leak' pada HTTP 302)
        if ($response->isRedirection()) {
            $response->setContent('');
        }

        // 8. Pastikan seluruh Cookie memiliki proteksi HttpOnly (Mengatasi ZAP 'Cookie No HttpOnly Flag')
        foreach ($response->headers->getCookies() as $cookie) {
            if (!$cookie->isHttpOnly()) {
                $response->headers->setCookie($cookie->withHttpOnly(true));
            }
        }

        // 9. Sembunyikan informasi server & PHP
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        return $response;
    }
}
