<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mode Offline - SIKAP FKP UNRI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
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
            Anda sedang tidak terhubung ke jaringan internet. Jika Anda telah menyimpan draf logbook harian di perangkat ini, draf Anda tetap tersimpan aman di memori lokal ponsel/laptop.
        </p>

        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-xs text-left space-y-2 mb-6">
            <div class="flex items-center gap-2 text-slate-700 font-bold">
                <span>💡</span> Tips Mode Offline:
            </div>
            <p class="text-slate-600 text-[11px]">
                1. Periksa koneksi Wi-Fi atau paket data seluler Anda.<br>
                2. Begitu sinyal kembali aktif, klik tombol "Coba Lagi" di bawah.<br>
                3. Sistem SIKAP akan menyinkronkan data draf Anda ke server otomatis.
            </p>
        </div>

        <button onclick="window.location.reload()" class="w-full py-3 bg-[#007a3d] hover:bg-[#006030] text-white rounded-xl text-xs font-bold transition shadow-md">
            🔄 Coba Hubungkan Kembali
        </button>
    </div>

</body>
</html>
