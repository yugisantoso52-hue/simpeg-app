<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                        Transparansi Sistem Merit
                    </span>
                    <span class="text-xs text-gray-500 font-mono">UU No. 20/2023 &bull; PermenPAN-RB No. 3/2020</span>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>🌟</span> Transparansi Profil Talenta Mandiri
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Informasi capaian Kinerja, Potensi, dan Rekomendasi Rencana Pengembangan Individu (Individual Development Plan) Anda
                </p>
            </div>
            <div>
                <form method="GET" action="{{ route('manajemen-talenta.my-talent') }}">
                    <select name="tahun" onchange="this.form.submit()" class="text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 font-semibold">
                        @foreach(range(date('Y'), date('Y') - 4) as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun Evaluasi {{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Hero Banner Status Talenta --}}
        <div class="bg-gradient-to-r from-emerald-800 to-teal-900 text-white rounded-3xl p-6 md:p-8 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-black bg-white/20 text-emerald-200 border border-white/20 uppercase tracking-wider">
                        Posisi Kuadran Anda (Tahun {{ $tahun }})
                    </span>
                    <h2 class="text-2xl md:text-3xl font-black mt-2">
                        {{ $mapping->box_name }}
                    </h2>
                    <p class="text-emerald-100 text-sm mt-1">
                        Status Kategori: <b class="text-white underline">{{ $mapping->status_talenta }}</b>
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 text-center">
                        <span class="block text-[10px] uppercase tracking-wider text-emerald-200 font-bold">Skor Kinerja (X)</span>
                        <span class="text-2xl font-black font-mono">{{ $mapping->sumbu_kinerja_nilai }}</span>
                        <span class="block text-[10px] text-emerald-200 mt-0.5">{{ $mapping->sumbu_kinerja_kategori }}</span>
                    </div>
                    <div class="px-5 py-3 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 text-center">
                        <span class="block text-[10px] uppercase tracking-wider text-emerald-200 font-bold">Skor Potensi (Y)</span>
                        <span class="text-2xl font-black font-mono">{{ $mapping->sumbu_potensi_nilai }}</span>
                        <span class="block text-[10px] text-emerald-200 mt-0.5">{{ $mapping->sumbu_potensi_kategori }}</span>
                    </div>
                </div>
            </div>

            {{-- Rekomendasi Pengembangan Individu --}}
            <div class="mt-6 pt-6 border-t border-white/15">
                <span class="text-xs font-bold text-emerald-300 block mb-1 uppercase tracking-wider">Rekomendasi Pengembangan Karier Anda:</span>
                <p class="text-xs md:text-sm text-emerald-50 leading-relaxed">
                    {{ $mapping->rekomendasi_kebijakan }}
                </p>
            </div>
        </div>

        {{-- Breakdown Indikator Penilaian --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Sumbu Kinerja --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-4">
                <div class="pb-3 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase">Capaian Hasil Kerja</span>
                        <h3 class="text-base font-black text-gray-900">Sumbu Kinerja (X)</h3>
                    </div>
                    <span class="text-xs font-black px-2.5 py-1 rounded-full {{ $mapping->sumbu_kinerja_kategori === 'Tinggi' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $mapping->sumbu_kinerja_kategori }}
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">SKP Tahun Berjalan ({{ $tahun }})</span>
                            <span class="text-[11px] text-gray-500">Predikat: <b>{{ $mapping->predikat_skp_n ?? '-' }}</b></span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ $mapping->skor_skp_n ?? '-' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">SKP Tahun Sebelumnya ({{ $tahun - 1 }})</span>
                            <span class="text-[11px] text-gray-500">Predikat: <b>{{ $mapping->predikat_skp_n_minus_1 ?? '-' }}</b></span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ $mapping->skor_skp_n_minus_1 ?? '-' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Kedisiplinan Kehadiran</span>
                            <span class="text-[11px] text-gray-500">Rekap Presensi Tepat Waktu</span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ $mapping->skor_disiplin_kehadiran ? $mapping->skor_disiplin_kehadiran . '%' : 'N/A' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Keaktifan Logbook Harian</span>
                            <span class="text-[11px] text-gray-500">Laporan disetujui atasan</span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ $mapping->skor_aktivitas_logbook ? $mapping->skor_aktivitas_logbook . '%' : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Sumbu Potensi --}}
            <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm space-y-4">
                <div class="pb-3 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase">Kapasitas &amp; Kompetensi</span>
                        <h3 class="text-base font-black text-gray-900">Sumbu Potensi (Y)</h3>
                    </div>
                    <span class="text-xs font-black px-2.5 py-1 rounded-full {{ $mapping->sumbu_potensi_kategori === 'Tinggi' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $mapping->sumbu_potensi_kategori }}
                    </span>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Kualifikasi Pendidikan</span>
                            <span class="text-[11px] text-gray-500">Pendidikan Terakhir: <b>{{ $pegawai->pendidikan_terakhir ?? '-' }}</b></span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ number_format($mapping->skor_kualifikasi_pendidikan, 1) }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Hak 20 JP Pengembangan Kompetensi</span>
                            <span class="text-[11px] text-gray-500">Pemenuhan target pelatihan PP 11/2017</span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ number_format($mapping->skor_pengembangan_kompetensi, 1) }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Rekam Jejak &amp; Penghargaan</span>
                            <span class="text-[11px] text-gray-500">Kepangkatan, MKG &amp; Satyalancana</span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ number_format($mapping->skor_rekam_jejak, 1) }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 flex items-center justify-between">
                        <div>
                            <span class="font-bold text-gray-800 block">Asesmen Kompetensi (BKN)</span>
                            <span class="text-[11px] text-gray-500">{{ $mapping->skor_asesmen_kompetensi ? 'Terverifikasi' : 'Belum Asesmen' }}</span>
                        </div>
                        <span class="font-mono font-bold text-sm">{{ $mapping->skor_asesmen_kompetensi ?? '-' }}</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
