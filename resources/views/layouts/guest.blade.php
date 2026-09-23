@props(['fullWidth' => false])
<!DOCTYPE html>
<html lang="id" class="notranslate" translate="no">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="google" content="notranslate">
        <meta name="googlebot" content="notranslate">

        <title>{{ config('app.name', 'SIKAP FKP UNRI') }}</title>

        <!-- Favicon & PWA -->
        <link rel="icon" href="{{ asset('logo-unri.png') }}" type="image/png">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#007a3d">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-100 min-h-screen">
        @if($fullWidth)
            {{ $slot }}
            @yield('content')
        @else
            <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-slate-100">
                <div class="w-full sm:max-w-md bg-white shadow-xl rounded-2xl border border-slate-200 p-6 sm:p-8">
                    @if(isset($slot))
                        {{ $slot }}
                    @endif
                    @yield('content')
                </div>

                <div class="mt-6 text-center text-xs text-slate-500 font-medium space-y-1">
                    <div>&copy; {{ date('Y') }} <strong>SIKAP</strong> — Fakultas Keperawatan Universitas Riau</div>
                    <div class="text-[11px] text-slate-500">Pengembang Sistem: Rahmad Hidayat Majlan, S.T. (RHM)</div>
                </div>
            </div>
        @endif
    </body>
</html>
