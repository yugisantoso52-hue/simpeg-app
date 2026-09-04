<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Memaksa penggunaan HTTPS di server Production (Mengatasi Error 419 & Tampilan Berantakan)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        /*
        |--------------------------------------------------------------------------
        | Enterprise Pagination
        |--------------------------------------------------------------------------
        */

        Paginator::defaultView('vendor.pagination.enterprise');

        /*
        |--------------------------------------------------------------------------
        | Automatic Non-Blocking Audit Logging
        |--------------------------------------------------------------------------
        */
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            if (isset($event->user) && $event->user instanceof \App\Models\User) {
                \App\Services\ActivityLoggerService::logLogin($event->user);
            }
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if (isset($event->user) && $event->user instanceof \App\Models\User) {
                \App\Services\ActivityLoggerService::logLogout($event->user);
            }
        });
    }
}