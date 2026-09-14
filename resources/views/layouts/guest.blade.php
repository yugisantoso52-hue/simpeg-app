<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-100">
        <div class="min-h-screen flex flex-col justify-center items-center px-4 py-8 bg-slate-100">
            <div class="w-full sm:max-w-md bg-white shadow-xl rounded-2xl border border-slate-200 p-6 sm:p-8">
                @if(isset($slot))
                    {{ $slot }}
                @endif
                @yield('content')
            </div>

            <div class="mt-6 text-center text-xs text-slate-500 font-medium">
                &copy; {{ date('Y') }} Fakultas Keperawatan Universitas Riau
            </div>
        </div>
    </body>
</html>
