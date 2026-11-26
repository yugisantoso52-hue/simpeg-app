{{-- Panel Ringkasan Pemisahan Hak, Syarat & Ketentuan Cuti PNS vs PPPK (Peraturan BKN Nomor 7 Tahun 2022) --}}
@props(['defaultOpen' => false])

<div x-data="{ openRegulasi: {{ $defaultOpen ? 'true' : 'false' }} }" class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
    {{-- Header Bar Kontras Tinggi (Jelas Dibaca di Semua Perangkat & Tema) --}}
    <div @click="openRegulasi = !openRegulasi"
         class="px-5 py-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer select-none hover:bg-slate-100/80 transition">
        <div class="flex items-start sm:items-center gap-3.5">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-100 border border-amber-300 text-amber-800 text-lg shadow-2xs">
                ⚖️
            </span>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[11px] font-extrabold uppercase rounded-md bg-amber-400 text-slate-950 border border-amber-500/40 shadow-2xs">
                        PERATURAN BKN NOMOR 7 TAHUN 2022
                    </span>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight">
                        Ringkasan Syarat, Ketentuan &amp; Pemisahan Hak Cuti ASN (PNS vs PPPK)
                    </h3>
                </div>
                <p class="text-xs sm:text-[13px] font-semibold text-slate-700 mt-1 leading-snug">
                    Perbandingan jenis hak cuti, masa kerja, batas kuota, serta syarat bukti dukung bagi PNS dan PPPK
                </p>
            </div>
        </div>
        <div class="shrink-0">
            <button type="button"
                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-xs font-bold text-white shadow-xs transition">
                <span x-text="openRegulasi ? 'Tutup Tabel Rincian' : 'Lihat Rincian Syarat & Ketentuan'">Lihat Rincian Syarat &amp; Ketentuan</span>
                <svg class="w-4 h-4 transition-transform duration-200" :class="openRegulasi ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Ringkasan Utama Pemisahan Hak Cuti PNS vs PPPK (Selalu Tampil Jelas) --}}
    <div class="p-4 sm:p-5 bg-white">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-3.5 rounded-xl bg-blue-50/90 border border-blue-200">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-blue-950">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                        1. Hak Cuti Pegawai Negeri Sipil (PNS)
                    </span>
                    <span class="text-[11px] font-extrabold px-2 py-0.5 rounded bg-blue-100 text-blue-900 border border-blue-300">
                        7 Jenis Cuti
                    </span>
                </div>
                <p class="text-xs text-slate-800 leading-relaxed">
                    PNS berhak atas seluruh jenis cuti ASN: <strong>(1) Cuti Tahunan (12 hk), (2) Cuti Besar (3 bln, MK ≥ 5 thn), (3) Cuti Sakit, (4) Cuti Melahirkan (3 bln), (5) Cuti Alasan Penting / CAP (maks. 1 bln), (6) CLTN (maks. 3 thn),</strong> dan <strong>(7) Cuti Bersama</strong>.
                </p>
            </div>

            <div class="p-3.5 rounded-xl bg-amber-50/90 border border-amber-300">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="inline-flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wider text-amber-950">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                        2. Hak Cuti PPPK (Pasal 4 Peraturan BKN No. 7 Tahun 2022)
                    </span>
                    <span class="text-[11px] font-extrabold px-2 py-0.5 rounded bg-amber-200 text-amber-950 border border-amber-400">
                        4 Jenis Cuti
                    </span>
                </div>
                <p class="text-xs text-slate-800 leading-relaxed">
                    PPPK berhak atas <strong>4 jenis cuti</strong>: <strong>(1) Cuti Tahunan (12 hk), (2) Cuti Sakit (maks. 1 bln / kecelakaan kerja), (3) Cuti Melahirkan (3 bln),</strong> dan <strong>(4) Cuti Bersama</strong>. PPPK <u>tidak memiliki</u> Cuti Besar, CAP tersendiri, maupun CLTN. Alasan mendesak (keluarga inti sakit keras/meninggal atau pernikahan pertama) menggunakan <strong>Cuti Tahunan Pasal 9 (maks. 6 hari kerja)</strong>.
                </p>
            </div>
        </div>

        {{-- Tabel Matriks Perbandingan Lengkap (Collapsible) --}}
        <div x-show="openRegulasi" x-cloak class="mt-4 pt-4 border-t border-slate-200 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-2xs">
                <div class="px-4 py-2.5 bg-slate-100 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">
                        📋 Matriks Lengkap Pemisahan Hak, Kuota &amp; Syarat Ketentuan Cuti (PNS vs PPPK)
                    </span>
                    <span class="text-[11px] text-slate-700 font-semibold">Dasar Hukum: Peraturan BKN Nomor 7 Tahun 2022</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-slate-800 border-collapse">
                        <thead class="bg-slate-50 text-[11px] uppercase font-bold text-slate-800 border-b border-gray-200">
                            <tr>
                                <th class="py-2.5 px-3 border-r border-gray-200 w-44">Jenis Cuti</th>
                                <th class="py-2.5 px-3 border-r border-gray-200 bg-blue-50/60 text-blue-950 w-[36%]">Ketentuan Bagi PNS</th>
                                <th class="py-2.5 px-3 border-r border-gray-200 bg-amber-50/60 text-amber-950 w-[36%]">Ketentuan Bagi PPPK (Perbkn No. 7 Tahun 2022)</th>
                                <th class="py-2.5 px-3">Syarat &amp; Bukti Dukung</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            {{-- 1. Cuti Tahunan --}}
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-3 font-bold text-slate-900 border-r border-gray-100">
                                    1. Cuti Tahunan
                                    <span class="block text-[11px] font-semibold text-emerald-700 mt-0.5">Hak: 12 Hari Kerja</span>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak</span>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700">
                                        <li>Masa kerja minimal <strong>1 tahun</strong> terus-menerus (12 hari kerja).</li>
                                        <li>Sisa N-1 dapat dipakai di tahun N maks. total <strong>18 hari kerja</strong>.</li>
                                        <li>Sisa N-2 &amp; N-1 dapat dipakai maks. total <strong>24 hari kerja</strong>.</li>
                                    </ul>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak (Pasal 5 – 11)</span>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700">
                                        <li>Bekerja minimal <strong>1 tahun</strong> terus-menerus: <strong>12 hari kerja</strong>.</li>
                                        <li><strong>Pasal 7 (Akumulasi)</strong>: Maks. <strong>18 hk</strong> (jika masa kontrak &gt; 2 thn) atau maks. <strong>24 hk</strong> (jika masa kontrak &gt; 3 thn).</li>
                                        <li><strong>Pasal 9 (Pengecualian &lt; 1 Tahun / Mendesak)</strong>: Tetap diberikan maks. <strong>6 hari kerja</strong> untuk orang tua/mertua/istri/suami/anak sakit keras/meninggal atau perkawinan pertama (memotong kuota cuti tahunan).</li>
                                    </ul>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-700 leading-relaxed">
                                    Permohonan tertulis via E-Cuti. Khusus Pasal 9 huruf a (keluarga sakit keras) wajib melampirkan <strong>Surat Keterangan Rawat Inap</strong> dari Unit Pelayanan Kesehatan.
                                </td>
                            </tr>

                            {{-- 2. Cuti Sakit --}}
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-3 font-bold text-slate-900 border-r border-gray-100">
                                    2. Cuti Sakit
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak</span>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700">
                                        <li>Diberikan s.d. <strong>1 tahun</strong>, dapat ditambah maks. <strong>6 bulan</strong> (Tim Penguji Kesehatan).</li>
                                        <li>Gugur kandungan: maks. <strong>1,5 bulan</strong>.</li>
                                    </ul>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak (Pasal 12 – 17)</span>
                                    <ul class="list-disc list-inside space-y-0.5 text-[11px] text-slate-700">
                                        <li>Sakit 1 s.d. 14 hari: diajukan tertulis dengan surat dokter.</li>
                                        <li>Sakit &gt; 14 hari: diberikan paling lama <strong>1 (satu) bulan</strong> dengan surat dokter pemerintah (Pasal 13).</li>
                                        <li>Gugur kandungan: paling lama <strong>1,5 bulan</strong> (Pasal 14).</li>
                                        <li>Kecelakaan kerja: s.d. berakhirnya masa perjanjian kerja (Pasal 15).</li>
                                    </ul>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-700 leading-relaxed">
                                    Wajib <strong>Surat Keterangan Dokter</strong> (untuk PPPK sakit &gt; 14 hari wajib dari <strong>Dokter PNS / Unit Pelayanan Kesehatan Pemerintah</strong>).
                                </td>
                            </tr>

                            {{-- 3. Cuti Melahirkan --}}
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-3 font-bold text-slate-900 border-r border-gray-100">
                                    3. Cuti Melahirkan
                                    <span class="block text-[11px] font-semibold text-pink-700 mt-0.5">Hak: 3 Bulan</span>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak</span>
                                    <p class="text-[11px] text-slate-700">Kelahiran anak ke-1 s.d. ke-3 selama <strong>3 bulan</strong>. (Kelahiran anak ke-4 dst diberikan Cuti Besar).</p>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak (Pasal 18 – 19)</span>
                                    <p class="text-[11px] text-slate-700">Kelahiran anak pertama s.d. anak ketiga <strong>pada saat sudah berstatus PPPK</strong>, diberikan paling lama <strong>3 (tiga) bulan</strong>.</p>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-700 leading-relaxed">
                                    Surat Keterangan HPL / Rujukan Persalinan dari Dokter Spesialis Kandungan atau Bidan.
                                </td>
                            </tr>

                            {{-- 4. Cuti Bersama --}}
                            <tr class="hover:bg-slate-50/70">
                                <td class="py-2.5 px-3 font-bold text-slate-900 border-r border-gray-100">
                                    4. Cuti Bersama
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak</span>
                                    <p class="text-[11px] text-slate-700">Ditetapkan melalui Keputusan Presiden dan <strong>tidak mengurangi</strong> hak Cuti Tahunan.</p>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak (Pasal 20)</span>
                                    <p class="text-[11px] text-slate-700">Mengikuti ketentuan Cuti Bersama PNS (Keppres), tidak mengurangi Cuti Tahunan. Jika karena jabatan tidak menikmati cuti bersama, kuota tahunan ditambah sejumlah hari tersebut.</p>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-700 leading-relaxed">
                                    Otomatis berlaku sesuai Keputusan Presiden / Edaran Rektor.
                                </td>
                            </tr>

                            {{-- 5. Cuti Besar --}}
                            <tr class="hover:bg-slate-50/70 bg-rose-50/20">
                                <td class="py-2.5 px-3 font-bold text-slate-900 border-r border-gray-100">
                                    5. Cuti Besar
                                    <span class="block text-[11px] font-semibold text-purple-700 mt-0.5">Khusus PNS</span>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak (Khusus PNS)</span>
                                    <p class="text-[11px] text-slate-700">Bekerja minimal <strong>5 tahun</strong> secara terus-menerus, diberikan paling lama <strong>3 bulan</strong> (menggugurkan Cuti Tahunan di tahun berjalan). Dapat digunakan untuk ibadah haji pertama atau kelahiran anak ke-4.</p>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-rose-100 text-rose-800 mb-1">✖ Tidak Berhak (Pasal 4)</span>
                                    <p class="text-[11px] text-rose-900">PPPK <strong>tidak memiliki</strong> hak Cuti Besar dalam Peraturan BKN Nomor 7 Tahun 2022.</p>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-700 leading-relaxed">
                                    SK Masa Kerja ≥ 5 Tahun / Bukti Porsi Ibadah Keagamaan (Khusus PNS).
                                </td>
                            </tr>

                            {{-- 6. Cuti Karena Alasan Penting (CAP) --}}
                            <tr class="hover:bg-slate-50/70 bg-rose-50/20">
                                <td class="py-2.5 px-3 font-bold text-slate-900 border-r border-gray-100">
                                    6. Cuti Alasan Penting (CAP)
                                    <span class="block text-[11px] font-semibold text-cyan-700 mt-0.5">Khusus PNS</span>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak (Khusus PNS)</span>
                                    <p class="text-[11px] text-slate-700">Paling lama <strong>1 bulan</strong> untuk keluarga inti sakit keras/meninggal, perkawinan, istri melahirkan/caesar, atau bencana alam, <strong>tanpa memotong</strong> Cuti Tahunan.</p>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-amber-100 text-amber-900 mb-1">⚠ Dialihkan ke Cuti Tahunan Pasal 9</span>
                                    <p class="text-[11px] text-amber-950">PPPK tidak memiliki pos CAP tersendiri. Alasan keluarga sakit keras/meninggal atau perkawinan pertama diberikan melalui <strong>Cuti Tahunan Pasal 9 (maks. 6 hari kerja)</strong> dan mengurangi kuota Cuti Tahunan.</p>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-700 leading-relaxed">
                                    Surat Rawat Inap RS / Akta Kematian / Surat Keterangan Nikah.
                                </td>
                            </tr>

                            {{-- 7. CLTN --}}
                            <tr class="hover:bg-slate-50/70 bg-rose-50/20">
                                <td class="py-2.5 px-3 font-bold text-slate-900 border-r border-gray-100">
                                    7. Cuti di Luar Tanggungan Negara (CLTN)
                                    <span class="block text-[11px] font-semibold text-slate-600 mt-0.5">Khusus PNS</span>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-emerald-100 text-emerald-800 mb-1">✔ Berhak (Khusus PNS)</span>
                                    <p class="text-[11px] text-slate-700">Bekerja minimal <strong>5 tahun</strong> terus-menerus karena alasan pribadi mendesak (mendampingi pasangan tugas negara/belajar, dsb.) paling lama <strong>3 tahun</strong>.</p>
                                </td>
                                <td class="py-2.5 px-3 border-r border-gray-100 leading-relaxed">
                                    <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold rounded bg-rose-100 text-rose-800 mb-1">✖ Tidak Berhak (Pasal 4)</span>
                                    <p class="text-[11px] text-rose-900">PPPK <strong>tidak berhak</strong> atas CLTN karena terikat masa perjanjian kerja.</p>
                                </td>
                                <td class="py-2.5 px-3 text-[11px] text-slate-700 leading-relaxed">
                                    Persetujuan Teknis Kepala BKN &amp; SK PPK (Khusus PNS).
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Ketentuan Tambahan Penting (Pasal 8, 10, 21, 22) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div class="p-3.5 rounded-xl bg-slate-50 border border-gray-200 shadow-2xs">
                    <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                        <span>🎓</span> Ketentuan Dosen &amp; Guru (Pasal 10)
                    </div>
                    <p class="text-[11px] text-slate-700 leading-relaxed">
                        Pegawai yang menduduki jabatan <strong>Dosen pada perguruan tinggi</strong> yang mendapat liburan akhir semester menurut kalender akademik, disamakan dengan telah menggunakan hak Cuti Tahunan.
                    </p>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-gray-200 shadow-2xs">
                    <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                        <span>✈️</span> Tempat Sulit Perhubungan &amp; Luar Negeri (Pasal 8 &amp; 22)
                    </div>
                    <p class="text-[11px] text-slate-700 leading-relaxed">
                        Cuti tahunan di lokasi sulit transportasi dapat ditambah maks. <strong>6 hari kalender</strong> (Pasal 8). Pelaksanaan cuti ke luar negeri wajib mendapat izin tertulis dari <strong>Pejabat Pembina Kepegawaian / PPK</strong> (Pasal 22).
                    </p>
                </div>
                <div class="p-3.5 rounded-xl bg-slate-50 border border-gray-200 shadow-2xs">
                    <div class="font-bold text-slate-900 mb-1 flex items-center gap-1.5">
                        <span>🔔</span> Panggilan Dinas Mendesak (Pasal 21)
                    </div>
                    <p class="text-[11px] text-slate-700 leading-relaxed">
                        Pegawai yang sedang menjalani Cuti Tahunan atau Cuti Bersama dapat dipanggil kembali bekerja apabila ada kepentingan dinas mendesak, dan <strong>sisa jangka waktu cuti yang belum dijalankan tetap menjadi hak pegawai</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
