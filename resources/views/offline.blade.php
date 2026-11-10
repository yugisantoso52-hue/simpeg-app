<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mode Offline - SIKAP FKP UNRI</title>
    <link rel="stylesheet" href="{{ asset('fonts/fonts.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/guest-portal.js') }}" defer></script>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl border border-slate-200 p-8 text-center">
        <div class="w-20 h-20 bg-amber-100 text-amber-600 rounded-3xl flex items-center justify-center mx-auto text-4xl mb-5 shadow-inner">
            📴
        </div>

        <h1 class="text-xl font-black text-slate-800 mb-2">
            Koneksi Internet Terputus
        </h1>
        <p class="text-xs text-slate-500 mb-6 leading-relaxed">
            Anda sedang tidak terhubung ke jaringan internet. Jika Anda telah melakukan Presensi Offline-First (Check-In/Check-Out) atau menyimpan draf Logbook Harian di perangkat ini, data Anda tetap tersimpan aman secara kriptografi di memori lokal ponsel/laptop.
        </p>

        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-xs text-left space-y-2 mb-6">
            <div class="flex items-center gap-2 text-slate-700 font-bold">
                <span>💡</span> Panduan Mode Offline-First PWA:
            </div>
            <p class="text-slate-600 text-[11px]">
                1. Periksa koneksi Wi-Fi kampus atau paket data seluler Anda.<br>
                2. Begitu sinyal kembali aktif, antrean Presensi & Logbook akan disinkronkan otomatis ke server.<br>
                3. Klik tombol "Coba Hubungkan Kembali" di bawah untuk kembali ke halaman aktif.
            </p>
        </div>

        <button type="button" data-action="reload" class="w-full py-3 bg-[#007a3d] hover:bg-[#006030] text-white rounded-xl text-xs font-bold transition shadow-md cursor-pointer">
            🔄 Coba Hubungkan Kembali
        </button>

        <div class="mt-4">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 hover:underline">
                &larr; Kembali ke Halaman Login
            </a>
        </div>
    </div>

</body>
</html>
