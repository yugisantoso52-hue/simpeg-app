<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Keaslian Dokumen - SIKAP FKP UNRI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans antialiased text-slate-800 min-h-screen flex flex-col justify-between">

    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-xl mx-auto w-full">
        <!-- Kartu Kop Surat Resmi -->
        <div class="bg-white rounded-2xl shadow-md border border-slate-200 overflow-hidden">
            <div class="p-6 text-center border-b border-slate-100 bg-slate-50/50">
                <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-16 mx-auto mb-2.5 object-contain" onerror="this.style.display='none'">
                <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Kementerian Pendidikan Tinggi, Sains, dan Teknologi</h4>
                <h2 class="text-sm font-extrabold text-slate-900 uppercase">Universitas Riau</h2>
                <h3 class="text-xs font-bold text-emerald-700 uppercase">Fakultas Keperawatan</h3>
                <p class="text-[10px] text-slate-400 mt-1">Sistem Informasi Kepegawaian (SIKAP)</p>
            </div>

            <div class="p-6 sm:p-8">
                @if($isValid)
                    <!-- STATUS: VALID & RESMI -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl mb-3 shadow-inner">
                            ✓
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            ● DOKUMEN ASLI & RESMI
                        </span>
                        <h1 class="text-lg font-extrabold text-slate-800 mt-2">
                            Dokumen Terverifikasi Sah
                        </h1>
                        <p class="text-xs text-slate-500 mt-1">
                            Dokumen ini diterbitkan secara sah melalui sistem SIKAP Fakultas Keperawatan Universitas Riau.
                        </p>
                    </div>

                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 text-xs space-y-3">
                        <div class="flex justify-between border-b border-slate-200 pb-2">
                            <span class="text-slate-500">Jenis Dokumen</span>
                            <strong class="text-slate-800 text-right">{{ $document['doc_type'] ?? 'Dokumen Kepegawaian' }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-2">
                            <span class="text-slate-500">Nomor Dokumen</span>
                            <strong class="text-slate-800 font-mono text-right">{{ $document['doc_no'] ?? '-' }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-2">
                            <span class="text-slate-500">Nama Pegawai</span>
                            <strong class="text-slate-800 text-right">{{ $document['name'] ?? '-' }}</strong>
                        </div>
                        <div class="flex justify-between border-b border-slate-200 pb-2">
                            <span class="text-slate-500">Tanggal Terbit</span>
                            <strong class="text-slate-800 text-right">{{ \Carbon\Carbon::parse($document['issued'] ?? now())->locale('id')->translatedFormat('d F Y') }}</strong>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span class="text-slate-500">Kode Hash Otentikasi</span>
                            <span class="text-slate-600 font-mono text-[10px] break-all text-right">{{ substr($code, 0, 16) }}...</span>
                        </div>
                    </div>

                    <div class="mt-6 p-3 rounded-xl bg-blue-50 border border-blue-200 text-[11px] text-blue-800 flex items-start gap-2.5">
                        <span class="text-base">ℹ️</span>
                        <span>
                            Halaman ini merupakan tanda bukti verifikasi digital resmi SIKAP FKP UNRI. Teks dan isi dokumen fisik/elektronik diakui keberadaannya pada basis data kepegawaian.
                        </span>
                    </div>

                @else
                    <!-- STATUS: INVALID / TIDAK DITEMUKAN -->
                    <div class="text-center mb-6">
                        <div class="w-16 h-16 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto text-3xl mb-3 shadow-inner">
                            ✕
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            ● TIDAK TERVERIFIKASI
                        </span>
                        <h1 class="text-lg font-extrabold text-slate-800 mt-2">
                            Dokumen Tidak Dikenali
                        </h1>
                        <p class="text-xs text-slate-500 mt-1">
                            Kode tanda tangan digital tidak cocok dengan arsip kepegawaian SIKAP FKP UNRI. Dokumen kemungkinan telah dimodifikasi atau tidak diterbitkan resmi oleh sistem.
                        </p>
                    </div>

                    <div class="bg-rose-50 rounded-xl p-4 border border-rose-200 text-xs text-rose-800">
                        <strong class="block mb-1">Perhatian:</strong>
                        Pastikan Anda memindai QR Code langsung dari lembar dokumen asli. Jika Anda memerlukan konfirmasi keabsahan dokumen, silakan hubungi Subbagian Kepegawaian Fakultas Keperawatan UNRI.
                    </div>
                @endif

                <div class="mt-8 text-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center text-xs font-bold text-blue-600 hover:text-blue-800 transition">
                        ← Kembali ke Laman SIKAP FKP UNRI
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 text-slate-400 text-[11px]">
        &copy; {{ date('Y') }} SIKAP - Fakultas Keperawatan Universitas Riau. All rights reserved.
    </footer>

</body>
</html>
