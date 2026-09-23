<!DOCTYPE html>
<html lang="id" class="notranslate" translate="no">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="google" content="notranslate">
        <meta name="googlebot" content="notranslate">

        <title>SIKAP FKP UNRI</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- PWA Settings -->
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#007a3d">
        <link rel="apple-touch-icon" href="/logo-unri.png">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 flex flex-col justify-between">
            <div>
                @include('layouts.navigation')

                <!-- Page Heading -->
                @if(isset($header))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @elseif(View::hasSection('header'))
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            @yield('header')
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main>
                    @if(isset($slot))
                        {{ $slot }}
                    @endif
                    @yield('content')
                </main>
            </div>

            <!-- Footer Hak Cipta & Pengembang -->
            <footer class="bg-white border-t border-slate-200 mt-12 py-5">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-2.5 text-xs text-slate-500">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-[#007a3d]">SIKAP</span>
                        <span class="text-slate-300">|</span>
                        <span>&copy; {{ date('Y') }} Fakultas Keperawatan, Universitas Riau</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-slate-600">
                        <span class="text-slate-400">Pengembang Sistem:</span>
                        <span class="font-semibold text-slate-800 bg-slate-100 px-2 py-0.5 rounded border border-slate-200/60">Rahmad Hidayat Majlan, S.T. (RHM)</span>
                    </div>
                </div>
            </footer>
        </div>

        <!-- PWA Service Worker Registration -->
        <script>
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', () => {
                    navigator.serviceWorker.register('/sw.js')
                        .then((reg) => console.log('SIKAP Service Worker Registered:', reg.scope))
                        .catch((err) => console.log('Service Worker Failed:', err));
                });
            }
        </script>
    </body>
</html>
