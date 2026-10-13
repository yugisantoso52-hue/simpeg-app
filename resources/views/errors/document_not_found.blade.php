<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Berkas Fisik Belum Tersedia - SIKAP FKP UNRI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden text-center p-8 space-y-5">
        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto shadow-inner">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        </div>

        <div class="space-y-2">
            <span class="inline-block px-3 py-1 bg-amber-50 text-amber-700 text-xs font-bold rounded-full border border-amber-200">
                Informasi Dokumen Digital SIKAP
            </span>
            <h2 class="text-xl font-bold text-slate-800">Berkas Scan Belum Diunggah</h2>
            <p class="text-xs text-slate-500 leading-relaxed">
                {{ $message ?? 'Berkas fisik pindaian (scan) belum diunggah ke server penyimpanan. Namun demikian, data administratif (Nomor SK, Tanggal, dan TMT) tetap sah tercatat pada database SIKAP FKP UNRI.' }}
            </p>
        </div>

        <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-left text-xs space-y-1">
            <div class="text-slate-400 font-medium">Petunjuk Pengelola / Pegawai:</div>
            <div class="text-slate-700">
                Unggah berkas pindaian resmi (format PDF/JPG/PNG) melalui modul <strong>Kepegawaian</strong> terkait (Riwayat Pangkat / Jabatan / SKP / Penghargaan) agar dokumen fisik dapat ditinjau langsung.
            </div>
        </div>

        <div class="pt-2 flex items-center justify-center gap-3">
            <button type="button" onclick="window.close()" class="px-5 py-2.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition shadow-2xs">
                Tutup Jendela Ini
            </button>
            <a href="javascript:history.back()" class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition shadow-2xs">
                Kembali
            </a>
        </div>
    </div>
</body>
</html>
