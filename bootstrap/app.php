<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // Pendaftaran Alias Middleware Role & ForcePasswordChange
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,
        ]);

        // Opsional: Redirect Guest jika belum terautentikasi (Custom Redirection)
        $middleware->redirectTo(
            guests: '/login',
            users: '/dashboard'
        );

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Ukuran berkas yang diunggah melebihi batas maksimal (maksimal 25MB).'
                ], 413);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Ukuran berkas yang Anda unggah melebihi batas maksimal (maksimal total unggahan 25MB).');
        });
    })->create();