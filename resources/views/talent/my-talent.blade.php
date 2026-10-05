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
            <div class="flex items-center gap-2.5">
                {{-- Tombol SOP Modul Talenta --}}
                <x-sop-modal title="SOP & Panduan Penilaian Talenta ASN" 
                             buttonLabel="SOP & Cara Naik Kuadran" 
                             badge="PermenPAN-RB No. 3/2020" 
                             color="emerald">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200">
                            <h4 class="font-bold text-emerald-900 text-xs mb-1 flex items-center gap-1.5">
                                <x-icon name="target" class="w-4 h-4 text-emerald-700" />
                                Apa itu Profil Talenta Mandiri?
                            </h4>
                            <p class="text-emerald-800 text-[11px] leading-relaxed">
                                Halaman ini adalah <strong>hasil evaluasi otomatis</strong> yang memetakan ASN ke dalam <strong>Matriks 9-Kotak (9-Box Grid)</strong>. Nilai dihitung langsung oleh sistem dari dua sumbu: <strong>Sumbu Kinerja (X)</strong> dan <strong>Sumbu Potensi (Y)</strong>.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px]">1</span>
                                Cara Meningkatkan Skor Kinerja (Sumbu X)
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-slate-200">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">A. Lengkapi Riwayat SKP (Bobot 80%)</strong>
                                    <span class="text-slate-600 text-[11px]">Buka <em>Profil Saya &rarr; Riwayat SKP</em>. Masukkan SKP tahun berjalan dan tahun sebelumnya dengan predikat minimal <em>Baik</em> (skor 85) atau <em>Sangat Baik</em> (skor 100).</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">B. Kedisiplinan Kehadiran (Bobot 10%)</strong>
                                    <span class="text-slate-600 text-[11px]">Lakukan presensi harian masuk dan pulang tepat waktu sesuai jam kerja di menu <em>Presensi</em>.</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">C. Keaktifan Logbook Harian (Bobot 10%)</strong>
                                    <span class="text-slate-600 text-[11px]">Rutin mencatat aktivitas kerja harian dan pastikan disetujui atasan di menu <em>Logbook</em>.</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px]">2</span>
                                Cara Meningkatkan Skor Potensi (Sumbu Y)
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-slate-200">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">A. Kualifikasi Pendidikan (Bobot 40% / 20%)</strong>
                                    <span class="text-slate-600 text-[11px]">Pastikan jenjang pendidikan terakhir Anda telah diperbarui di menu <em>Profil Saya</em> (S1 = 75, S2 = 85, S3 = 100).</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">B. Penuhi Hak 20 JP Pelatihan / Tahun (Bobot 30% / 15%)</strong>
                                    <span class="text-slate-600 text-[11px]">Sesuai PP 11/2017, unggah sertifikat pelatihan/seminar/workshop di <em>Profil Saya &rarr; Riwayat Diklat</em>. Jika total jam mencapai minimal 20 JP, skor Anda otomatis <strong>100.0</strong>.</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">C. Rekam Jejak &amp; Asesmen BKN (Bobot 30% / 50%)</strong>
                                    <span class="text-slate-600 text-[11px]">Dihitung dari pangkat, masa kerja, piagam tanda kehormatan Satyalancana, serta hasil uji kompetensi (Assessment Center BKN).</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px]">
                            <strong>👑 Syarat Masuk Talent Pool Suksesi (Siap Promosi):</strong><br>
                            Pegawai harus berada di <strong>Kotak VII, VIII, atau IX</strong> (Kategori Potensi Sedang/Tinggi dan Kinerja Tinggi).
                        </div>
                    </div>
                </x-sop-modal>

                <form method="GET" action="{{ route('manajemen-talenta.my-talent') }}">
                    <select name="tahun" onchange="this.form.submit()" class="text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-1.5 font-semibold">
                        @foreach(range(date('Y'), date('Y') - 4) as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Banner Edukasi & Bantuan Cepat Pengisian --}}
        <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-200/90 text-emerald-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-2xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <x-icon name="sparkles" class="w-5 h-5 stroke-[2]" />
                </div>
                <div>
                    <h4 class="font-bold text-xs text-emerald-950">Bagaimana Cara Mengisi atau Menaikkan Nilai Talenta Saya?</h4>
                    <p class="text-[11px] text-emerald-800 mt-0.5">
                        Nilai profil talenta dihitung otomatis dari <strong>Riwayat SKP</strong>, <strong>20 JP Sertifikat Pelatihan</strong> di Profil Saya, serta <strong>Presensi &amp; Logbook</strong> harian Anda.
                    </p>
                </div>
            </div>
            <a href="{{ route('pegawai.my-profile') }}" class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition shrink-0 shadow-2xs flex items-center gap-1.5">
                <x-icon name="user" class="w-3.5 h-3.5 text-white" />
                <span>Lengkapi Profil &amp; Riwayat &rarr;</span>
            </a>
        </div>

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
