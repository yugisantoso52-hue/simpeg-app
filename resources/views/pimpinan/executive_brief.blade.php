<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 notranslate" translate="no">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white flex items-center justify-center text-lg shadow-xs shrink-0">
                    🎯
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Mode Slide Presentasi Dekanat
                        </span>
                        <span class="text-xs text-slate-500 font-mono">T.A. {{ $year }} / Semester {{ date('n') >= 7 ? 'Ganjil' : 'Genap' }}</span>
                    </div>
                    <h1 class="text-lg sm:text-xl font-black text-slate-900 mt-0.5">
                        Paparan Transformasi Digital Kepegawaian (SIKAP FKp UNRI)
                    </h1>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 no-print">
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-xl shadow-xs transition cursor-pointer">
                    <x-icon name="printer" class="w-4 h-4 text-white" />
                    <span>Cetak Semua Slide (PDF)</span>
                </button>
                <a href="{{ route('pimpinan.analytics') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl shadow-xs transition">
                    <span>📊</span> Dashboard Analitik
                </a>
                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 hover:bg-indigo-100 rounded-xl shadow-xs transition">
                    <span>🏛️</span> Peta Jabatan
                </a>
                <a href="{{ route('manajemen-talenta.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-xl shadow-xs transition">
                    <span>📈</span> Matriks Talenta
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Gaya Khusus Cetak & Fullscreen Slide --}}
    <style>
        @media print {
            nav, header, .no-print, footer, #sidebar { display: none !important; }
            body { background: white !important; color: #0f172a !important; font-size: 10pt !important; margin: 0 !important; }
            .print-container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
            .slide-panel { display: block !important; border: 1px solid #cbd5e1 !important; break-inside: avoid; page-break-after: always; margin-bottom: 20px !important; box-shadow: none !important; min-height: auto !important; }
        }
        #presentationDeck:fullscreen {
            background-color: #0f172a;
            padding: 1.25rem 1.5rem;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 1rem;
        }
        #presentationDeck:fullscreen .slide-panel {
            flex: 1;
        }
    </style>

    <div class="py-4 sm:py-6"
         x-data="{
            currentSlide: 1,
            totalSlides: 6,
            viewMode: 'slides',
            isFullscreen: false,
            slides: [
                { id: 1, short: 'Sampul', title: 'Sampul & Identitas Paparan' },
                { id: 2, short: 'I. Latar Belakang', title: 'Bagian I — Latar Belakang & Analisis Kebutuhan' },
                { id: 3, short: 'II. Arsitektur', title: 'Bagian II — Konsep & Arsitektur Sistem' },
                { id: 4, short: 'III. Fitur & Menu', title: 'Bagian III — Katalog Fitur & Menu Aplikasi' },
                { id: 5, short: 'IV. Manfaat', title: 'Bagian IV — Dampak Strategis & Nilai Tambah' },
                { id: 6, short: 'V. Alur 15 Menit', title: 'Bagian V — Panduan Alur Presentasi' }
            ],
            nextSlide() {
                if (this.currentSlide < this.totalSlides) {
                    this.currentSlide++;
                }
            },
            prevSlide() {
                if (this.currentSlide > 1) {
                    this.currentSlide--;
                }
            },
            goToSlide(n) {
                this.viewMode = 'slides';
                this.currentSlide = n;
            },
            toggleFullscreen() {
                const deck = document.getElementById('presentationDeck');
                if (!document.fullscreenElement) {
                    if (deck.requestFullscreen) {
                        deck.requestFullscreen();
                        this.isFullscreen = true;
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                        this.isFullscreen = false;
                    }
                }
            }
         }"
         @keydown.window.right="if (viewMode === 'slides') nextSlide()"
         @keydown.window.left="if (viewMode === 'slides') prevSlide()"
         @fullscreenchange.window="isFullscreen = !!document.fullscreenElement">

        <div id="presentationDeck" class="w-full max-w-[1600px] mx-auto px-3 sm:px-6 lg:px-8 space-y-4 print-container">

            {{-- ========================================================================= --}}
            {{-- SLIDE 1: SAMPUL & IDENTITAS RESMI PRESENTASI DEKANAT                      --}}
            {{-- ========================================================================= --}}
            <div x-show="viewMode === 'all' || currentSlide === 1"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="slide-panel bg-gradient-to-br from-slate-900 via-slate-900 to-emerald-950 text-white rounded-3xl border border-slate-800 p-6 sm:p-10 shadow-xl min-h-[540px] flex flex-col justify-between relative overflow-hidden notranslate" translate="no">

                {{-- Kop Resmi --}}
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 border-b border-white/15 pb-6 z-10">
                    <div class="flex items-center gap-4 text-center md:text-left">
                        <div class="p-2.5 bg-white rounded-2xl shadow-md shrink-0">
                            <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-14 w-auto object-contain">
                        </div>
                        <div>
                            <p class="text-[11px] font-bold tracking-widest text-emerald-300 uppercase">
                                Kementerian Pendidikan Tinggi, Sains, dan Teknologi
                            </p>
                            <h2 class="text-lg sm:text-xl font-black text-white uppercase tracking-tight mt-0.5">
                                Universitas Riau — Fakultas Keperawatan
                            </h2>
                            <p class="text-xs font-medium text-slate-300 mt-0.5">
                                Sistem Informasi Kepegawaian &amp; Kinerja Aparatur (SIKAP) • Bahan Paparan Sidang Pimpinan
                            </p>
                        </div>
                    </div>

                    <div class="text-center md:text-right text-xs text-slate-300 shrink-0 bg-white/5 border border-white/10 rounded-2xl px-4 py-3">
                        <div><strong class="text-white">Tanggal Paparan:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}</div>
                        <div class="mt-0.5"><strong class="text-white">Ditujukan Kepada:</strong> Ibu Dekan Prof. Wan Nishfa Dewi, S.Kp, MNg, PhD</div>
                        <div class="text-[11px] text-emerald-300 font-semibold mt-0.5">&amp; Jajaran Pimpinan Dekanat FKp UNRI</div>
                    </div>
                </div>

                {{-- Inti Slide Sampul --}}
                <div class="my-auto py-8 space-y-6 z-10">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 text-xs font-bold uppercase tracking-wider">
                        <span>🚀</span> Executive Summary &amp; Strategic Blueprint
                    </div>

                    <h3 class="text-2xl sm:text-4xl font-black text-white leading-tight max-w-4xl">
                        Transformasi Tata Kelola Kepegawaian: Dari Manual Menuju Digital Terpadu Berbasis Kedaulatan Data Fakultas
                    </h3>

                    <p class="text-sm sm:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Membangun sistem mandiri tingkat fakultas yang menjembatani kebutuhan operasional harian Dosen, Tenaga Kependidikan, dan Pimpinan Dekanat serta bertindak sebagai penopang darurat (<em>back-up</em>) saat sistem universitas terkendala.
                    </p>

                    {{-- Highlight Angka Eksekutif Real-Time --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-4">
                        <div class="p-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-xs">
                            <div class="text-[11px] text-slate-300 uppercase font-bold">Total SDM Aktif</div>
                            <div class="text-2xl font-black text-white mt-1">{{ $kpis['total_pegawai'] ?? 83 }} <span class="text-xs font-semibold text-emerald-300">Pegawai</span></div>
                            <div class="text-[11px] text-slate-300 mt-0.5">Dosen, Tendik &amp; PHL</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-xs">
                            <div class="text-[11px] text-slate-300 uppercase font-bold">Bezetting vs Formasi ABK</div>
                            <div class="text-2xl font-black text-white mt-1">{{ $totalBezetting }} <span class="text-xs font-semibold text-amber-300">/ {{ $totalKebutuhanFormasi }}</span></div>
                            <div class="text-[11px] text-slate-300 mt-0.5">{{ $defisitJabatan->count() }} Jabatan Perlu Tambahan</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-xs">
                            <div class="text-[11px] text-slate-300 uppercase font-bold">Talent Pool (Kotak 7–9)</div>
                            <div class="text-2xl font-black text-emerald-400 mt-1">{{ $talentPoolCount }} <span class="text-xs font-semibold text-white">Kandidat</span></div>
                            <div class="text-[11px] text-slate-300 mt-0.5">Siap Suksesi Kepemimpinan</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-white/10 border border-white/15 backdrop-blur-xs">
                            <div class="text-[11px] text-slate-300 uppercase font-bold">Radar Pensiun (1–3 Thn)</div>
                            <div class="text-2xl font-black text-amber-300 mt-1">{{ isset($retirementRadar['within_3_years']) ? count($retirementRadar['within_3_years']) : 0 }} <span class="text-xs font-semibold text-white">ASN</span></div>
                            <div class="text-[11px] text-slate-300 mt-0.5">Perencanaan Regenerasi Dini</div>
                        </div>
                    </div>
                </div>

                {{-- Footer Slide 1 --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-white/10 text-xs text-slate-400 z-10 no-print">
                    <span>Gunakan tombol panah keyboard (<strong>&larr;</strong> / <strong>&rarr;</strong>) atau tombol di bawah untuk memulai slide paparan.</span>
                    <button type="button" @click="nextSlide()" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition cursor-pointer">
                        Mulai Paparan (Bagian I) &rarr;
                    </button>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SLIDE 2: BAGIAN I — LATAR BELAKANG & ANALISIS KEBUTUHAN                   --}}
            {{-- ========================================================================= --}}
            <div x-show="viewMode === 'all' || currentSlide === 2"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="slide-panel bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-md min-h-[540px] flex flex-col justify-between space-y-6">

                <div class="space-y-5">
                    <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Bagian I — Latar Belakang &amp; Analisis Kebutuhan</span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2 mt-1">
                                <span>🔍</span> Mengapa Fakultas Keperawatan Butuh Sistem Mandiri (SIKAP)?
                            </h3>
                        </div>
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 self-start sm:self-auto">
                            Manual &rarr; Digital Terpadu
                        </span>
                    </div>

                    {{-- Narasi Konteks Eksekutif --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4 sm:p-5 text-xs sm:text-sm text-slate-800 leading-relaxed">
                        Pengelolaan data kepegawaian selama bertahun-tahun mengandalkan berkas fisik kertas, map formulir, dan rekap spreadsheet terpisah sehingga menyulitkan pemantauan harian. Di sisi lain, sistem nasional (BKN / Kemendikbud) bersifat makro dengan hak akses terbatas bagi operasional fakultas.
                        Meskipun saat ini sudah ada sistem yang dibuat oleh Universitas, <strong>keberadaan aplikasi SIKAP di Fakultas Keperawatan sangat penting sebagai sistem operasional harian terpadu sekaligus penopang darurat (Back-up) disaat sistem milik Universitas mengalami kendala teknis atau gangguan server (seperti kendala yang pernah terjadi sebelumnya)</strong>. Dengan SIKAP, pelayanan presensi, pelaporan tugas, dan administrasi kepegawaian di Fakultas Keperawatan tetap berjalan stabil tanpa terhenti.
                    </div>

                    {{-- Tabel Komparasi 3 Kolom: Kondisi Lama vs Solusi SIKAP --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Kondisi 1 --}}
                        <div class="p-5 rounded-2xl border border-rose-200 bg-rose-50/40 space-y-3">
                            <div class="flex items-center gap-2 text-rose-700 font-extrabold text-xs sm:text-sm uppercase">
                                <span>❌</span> Masalah Pengelolaan Manual
                            </div>
                            <ul class="space-y-2 text-xs text-slate-700 leading-relaxed">
                                <li>• Berkas kertas permohonan cuti, KGB, dan SK fisik menumpuk di lemari TU serta rentan tercecer/rusak.</li>
                                <li>• Sisa hak cuti rawan terjadi selisih hitung karena pencatatan manual di buku agenda fisik.</li>
                                <li>• Pelaporan aktivitas kerja pegawai tidak terdokumentasi harian, menyulitkan evaluasi SKP yang objektif.</li>
                                <li>• Penghitungan analisis beban kerja (ABK) dilakukan manual di spreadsheet tanpa visualisasi bagan interaktif.</li>
                            </ul>
                        </div>

                        {{-- Kondisi 2 (Telah dihapus poin presensi GPS gedung sesuai instruksi) --}}
                        <div class="p-5 rounded-2xl border border-amber-200 bg-amber-50/40 space-y-3">
                            <div class="flex items-center gap-2 text-amber-800 font-extrabold text-xs sm:text-sm uppercase">
                                <span>⚠️</span> Keterbatasan Sistem Nasional &amp; Universitas
                            </div>
                            <ul class="space-y-2.5 text-xs text-slate-700 leading-relaxed">
                                <li>• Sistem nasional (BKN / Kemendikbud) bersifat makro nasional dengan akses data terbatas bagi Dekanat.</li>
                                <li>• <strong>Kebutuhan Sistem Back-up:</strong> Saat sistem milik Universitas mengalami kendala/maintenance (seperti yang pernah dialami sebelumnya), operasional presensi &amp; kinerja fakultas rawan lumpuh jika tidak memiliki sistem penopang mandiri.</li>
                            </ul>
                        </div>

                        {{-- Kondisi 3 --}}
                        <div class="p-5 rounded-2xl border border-emerald-300 bg-emerald-50/70 space-y-3">
                            <div class="flex items-center gap-2 text-emerald-800 font-extrabold text-xs sm:text-sm uppercase">
                                <span>✅</span> Solusi Terpadu SIKAP FKp UNRI
                            </div>
                            <ul class="space-y-2 text-xs text-emerald-950 leading-relaxed">
                                <li>• <strong>Kedaulatan Data &amp; Siaga Back-up:</strong> Data 83 SDM riil (Dosen, Tendik, PHL) aman di tangan Dekanat, siap mem-backup disaat sistem universitas mengalami kendala seperti sebelumnya.</li>
                                <li>• <strong>Layanan Mandiri di HP:</strong> Pegawai absen selfie GPS kampus, isi logbook 7.5 jam, dan ajukan cuti paperless.</li>
                                <li>• <strong>Dashboard Keputusan Dekanat:</strong> Data siap saji untuk rapat evaluasi dan bahan usulan formasi ke Rektorat.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Footer Navigasi Slide --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 no-print">
                    <span>Slide 2 dari 6 • Bagian I: Latar Belakang &amp; Analisis Kebutuhan</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevSlide()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">&larr; Sebelumnya</button>
                        <button type="button" @click="nextSlide()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold cursor-pointer">Bagian II: Arsitektur &rarr;</button>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SLIDE 3: BAGIAN II — KONSEP & ARSITEKTUR SISTEM                           --}}
            {{-- ========================================================================= --}}
            <div x-show="viewMode === 'all' || currentSlide === 3"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="slide-panel bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-md min-h-[540px] flex flex-col justify-between space-y-6">

                <div class="space-y-5">
                    <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Bagian II — Konsep &amp; Arsitektur Sistem</span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2 mt-1">
                                <span>🏗️</span> Garis Besar Aplikasi SIKAP FKp UNRI
                            </h3>
                        </div>
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 self-start sm:self-auto">
                            3 Tingkat Peran Terintegrasi
                        </span>
                    </div>

                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        SIKAP menghubungkan seluruh pemangku kepentingan kepegawaian dalam satu ekosistem terpadu melalui tiga tingkatan peran yang saling berkesinambungan:
                    </p>

                    {{-- 3 Pilar Arsitektur --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        {{-- Pilar 1: Pegawai --}}
                        <div class="p-5 rounded-2xl border border-blue-200 bg-blue-50/50 flex flex-col justify-between space-y-4">
                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center font-black text-sm shadow-xs">1</span>
                                    <h4 class="font-bold text-blue-950 text-xs sm:text-sm uppercase">Pegawai Mandiri (Dosen &amp; Tendik)</h4>
                                </div>
                                <p class="text-slate-600 text-xs leading-relaxed">
                                    Layanan mandiri tanpa kertas (<em>Self-Service Portal</em>): Presensi GPS foto live 1-klik, pengisian logbook 7.5 jam/hari, pengajuan cuti, dan pemutakhiran biodata riwayat karir pribadi.
                                </p>
                            </div>
                            <div class="text-xs text-blue-700 font-bold bg-white p-3 rounded-xl border border-blue-100">
                                📱 Aksesibel via Smartphone (PWA) &amp; Komputer
                            </div>
                        </div>

                        {{-- Pilar 2: Atasan --}}
                        <div class="p-5 rounded-2xl border border-amber-200 bg-amber-50/50 flex flex-col justify-between space-y-4">
                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center font-black text-sm shadow-xs">2</span>
                                    <h4 class="font-bold text-amber-950 text-xs sm:text-sm uppercase">Atasan Langsung (Kajur/Kaprodi/Pokja)</h4>
                                </div>
                                <p class="text-slate-600 text-xs leading-relaxed">
                                    Meja supervisi pembinaan bawahan: Verifikasi logbook harian staf, pemberian catatan revisi, dan pertimbangan ketersediaan personil pengganti saat cuti diajukan.
                                </p>
                            </div>
                            <div class="text-xs text-amber-800 font-bold bg-white p-3 rounded-xl border border-amber-100">
                                ⚡ Persetujuan Cepat (One-Click Approval)
                            </div>
                        </div>

                        {{-- Pilar 3: Dekanat --}}
                        <div class="p-5 rounded-2xl border border-emerald-200 bg-emerald-50/50 flex flex-col justify-between space-y-4">
                            <div class="space-y-2.5">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-xs">3</span>
                                    <h4 class="font-bold text-emerald-950 text-xs sm:text-sm uppercase">Pimpinan Dekanat (Ibu Dekan &amp; Para Wadek)</h4>
                                </div>
                                <p class="text-slate-600 text-xs leading-relaxed">
                                    Kokpit strategis pengambilan keputusan: Peta formasi ABK, radar pensiun 1–3 tahun, matriks talenta 9-kotak, dan analitik beban kerja seluruh program studi.
                                </p>
                            </div>
                            <div class="text-xs text-emerald-800 font-bold bg-white p-3 rounded-xl border border-emerald-100">
                                📊 Data Real-Time Siap Dibawa ke Rektorat
                            </div>
                        </div>
                    </div>

                    {{-- Kepatuhan Regulasi Nasional --}}
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-700 flex flex-wrap items-center justify-between gap-3">
                        <span class="font-bold text-slate-800">🏛️ Kepatuhan Standar Regulasi Nasional ASN:</span>
                        <div class="flex flex-wrap gap-2 text-xs">
                            <span class="px-3 py-1 rounded-xl bg-white border border-slate-200 font-semibold">📜 PermenPAN-RB No. 1/2020 (Anjab-ABK)</span>
                            <span class="px-3 py-1 rounded-xl bg-white border border-slate-200 font-semibold">📜 PermenPAN-RB No. 3/2020 (Manajemen Talenta)</span>
                            <span class="px-3 py-1 rounded-xl bg-white border border-slate-200 font-semibold">📜 Peraturan BKN No. 24/2017 (Cuti ASN)</span>
                        </div>
                    </div>
                </div>

                {{-- Footer Navigasi Slide --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 no-print">
                    <span>Slide 3 dari 6 • Bagian II: Konsep &amp; Arsitektur Sistem</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevSlide()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">&larr; Sebelumnya</button>
                        <button type="button" @click="nextSlide()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold cursor-pointer">Bagian III: Katalog Fitur &rarr;</button>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SLIDE 4: BAGIAN III — KATALOG FITUR & MENU APLIKASI                       --}}
            {{-- ========================================================================= --}}
            <div x-show="viewMode === 'all' || currentSlide === 4"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="slide-panel bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-md min-h-[540px] flex flex-col justify-between space-y-6">

                <div class="space-y-5">
                    <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Bagian III — Katalog Fitur &amp; Menu Aplikasi</span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2 mt-1">
                                <span>📑</span> Penjelasan Menu &amp; Fitur Utama dalam SIKAP
                            </h3>
                        </div>
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 self-start sm:self-auto">
                            Modul End-to-End Lengkap
                        </span>
                    </div>

                    <div class="space-y-5">
                        {{-- KELOMPOK 1: LAYANAN MANDIRI PEGAWAI --}}
                        <div class="space-y-2.5">
                            <div class="flex items-center gap-2 text-xs font-bold text-blue-900 uppercase tracking-wider">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                <span>A. Modul Layanan Mandiri Pegawai (Front-Office)</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                                    <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>📸</span> Presensi Mandiri GPS &amp; Selfie 1-Klik
                                    </strong>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Check-in/out cepat terkunci radius GPS (≤ 75m), selfie 1-klik, dan dukungan antrean Offline-First PWA saat sinyal lemah.
                                    </p>
                                </div>

                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                                    <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>⏱️</span> E-Logbook Kinerja Harian
                                    </strong>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Pegawai mengisi rincian tugas harian sesuai SKP dengan target jam efektif 450 menit (7.5 jam/hari) disertai bukti dokumen pendukung.
                                    </p>
                                </div>

                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                                    <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>🏖️</span> E-Cuti Online Paperless
                                    </strong>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Pengajuan cuti tahunan, sakit, melahirkan, dan alasan penting via HP dengan kalkulator sisa hak cuti otomatis (12 hari kerja/tahun).
                                    </p>
                                </div>

                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                                    <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>👤</span> Profil &amp; Riwayat Karir Digital
                                    </strong>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Biodata terpusat mencakup riwayat kenaikan pangkat, jabatan, pendidikan, STR/SIP, dan brankas unduh berkas SK asli.
                                    </p>
                                </div>

                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                                    <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>⭐</span> Transparansi Talenta Mandiri
                                    </strong>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Pegawai dapat melihat posisi kuadran talenta diri sendiri secara terbuka untuk mengetahui panduan pengembangan karir.
                                    </p>
                                </div>

                                <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1">
                                    <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span>📖</span> SOP Panduan Interaktif Tiap Menu
                                    </strong>
                                    <p class="text-[11px] text-slate-600 leading-relaxed">
                                        Setiap halaman dilengkapi tombol SOP interaktif berisi dasar hukum dan panduan pengisian agar pegawai mudah mengoperasikan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- KELOMPOK 2 & 3 --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 pt-1">
                            <div class="lg:col-span-5 space-y-2.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-amber-900 uppercase tracking-wider">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                                    <span>B. Supervisi Atasan (Middle-Office)</span>
                                </div>
                                <div class="grid grid-cols-1 gap-2.5">
                                    <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 space-y-1">
                                        <strong class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                                            <span>⚖️</span> Meja Verifikasi &amp; Rekap Logbook
                                        </strong>
                                        <p class="text-[11px] text-slate-700 leading-relaxed">
                                            Atasan memverifikasi keabsahan uraian kerja staf binaannya dengan fitur <em>Batch Approve</em> atau catatan pembinaan.
                                        </p>
                                    </div>
                                    <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 space-y-1">
                                        <strong class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                                            <span>✍️</span> Verifikasi Pertimbangan Cuti Berjenjang
                                        </strong>
                                        <p class="text-[11px] text-slate-700 leading-relaxed">
                                            Memeriksa ketersediaan personil pengganti tugas di unit sebelum meneruskan persetujuan cuti ke Dekan / Wakil Dekan.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="lg:col-span-7 space-y-2.5">
                                <div class="flex items-center gap-2 text-xs font-bold text-emerald-900 uppercase tracking-wider">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                    <span>C. Pengambilan Keputusan Dekanat (Executive Governance)</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                                    <div class="p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/50 space-y-1.5">
                                        <strong class="text-xs font-bold text-indigo-950 flex items-center gap-1.5">
                                            <span>🏛️</span> Peta Jabatan &amp; ABK
                                        </strong>
                                        <p class="text-[11px] text-slate-700 leading-relaxed">
                                            Bagan organisasi interaktif dengan perbandingan <strong>Bezetting Riil</strong> vs <strong>Formasi Kebutuhan ABK</strong> untuk dasar usulan ke Rektorat.
                                        </p>
                                    </div>

                                    <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50 space-y-1.5">
                                        <strong class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                                            <span>📈</span> Matriks Talenta 9-Box
                                        </strong>
                                        <p class="text-[11px] text-slate-700 leading-relaxed">
                                            Pemetaan merit sistem ASN (PermenPAN-RB No. 3/2020) yang otomatis membentuk <strong>Talent Pool (Kotak 7, 8, 9)</strong> suksesi pimpinan.
                                        </p>
                                    </div>

                                    <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50 space-y-1.5">
                                        <strong class="text-xs font-bold text-blue-950 flex items-center gap-1.5">
                                            <span>📊</span> Analitik Eksekutif
                                        </strong>
                                        <p class="text-[11px] text-slate-700 leading-relaxed">
                                            Pemantauan rasio kehadiran harian/bulanan, radar pegawai pensiun (BUP 1–3 tahun), dan beban jam logbook per prodi.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer Navigasi Slide --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 no-print">
                    <span>Slide 4 dari 6 • Bagian III: Katalog Fitur &amp; Menu Aplikasi</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevSlide()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">&larr; Sebelumnya</button>
                        <button type="button" @click="nextSlide()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold cursor-pointer">Bagian IV: Manfaat Strategis &rarr;</button>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SLIDE 5: BAGIAN IV — DAMPAK STRATEGIS & MANFAAT LANGSUNG                  --}}
            {{-- ========================================================================= --}}
            <div x-show="viewMode === 'all' || currentSlide === 5"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="slide-panel bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-md min-h-[540px] flex flex-col justify-between space-y-6">

                <div class="space-y-6">
                    <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Bagian IV — Dampak Strategis</span>
                            <h3 class="text-xl sm:text-2xl font-black text-slate-900 flex items-center gap-2 mt-1">
                                <span>🚀</span> Manfaat &amp; Nilai Tambah Langsung bagi Fakultas Keperawatan
                            </h3>
                        </div>
                        <span class="px-3.5 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 self-start sm:self-auto">
                            Efisiensi &amp; Akuntabilitas
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50 space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl">🌱</div>
                            <h4 class="font-black text-slate-900 text-sm uppercase">1. Zero Paperwork &amp; Hemat Biaya</h4>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                Menghilangkan biaya pencetakan formulir cuti, map arsip, dan binder kertas. Seluruh berkas tersimpan aman dalam format digital terproteksi QR Code.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50 space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl">⚡</div>
                            <h4 class="font-black text-slate-900 text-sm uppercase">2. Kecepatan Pelayanan Staf</h4>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                Proses pengajuan cuti dan permohonan kepegawaian yang biasanya memakan waktu berhari-hari kini tuntas dalam hitungan menit via ponsel.
                            </p>
                        </div>

                        <div class="p-6 rounded-2xl border border-slate-200 bg-slate-50 space-y-3">
                            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl">🏆</div>
                            <h4 class="font-black text-slate-900 text-sm uppercase">3. Penguatan Nilai Akreditasi</h4>
                            <p class="text-slate-600 text-xs sm:text-sm leading-relaxed">
                                Menjadi bukti otentik tata pamong perguruan tinggi yang akuntabel, transparan, dan meritokratis pada instrumen akreditasi LAM-PTKes / BAN-PT.
                            </p>
                        </div>
                    </div>

                    {{-- Pintasan Live Demo untuk Ditampilkan Saat Paparan --}}
                    <div class="p-5 rounded-2xl bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4 no-print">
                        <div>
                            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Pintasan Cepat Live Demo</div>
                            <div class="text-sm font-bold text-white mt-0.5">Buka modul strategis secara langsung di hadapan Pimpinan Dekanat:</div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('anjab.peta-jabatan') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-bold text-white transition">
                                🏛️ Buka Peta Jabatan ABK &nearr;
                            </a>
                            <a href="{{ route('manajemen-talenta.index') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-xs font-bold text-white transition">
                                📈 Buka Matriks Talenta 9-Box &nearr;
                            </a>
                            <a href="{{ route('pimpinan.analytics') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black transition">
                                📊 Buka Dashboard Eksekutif &nearr;
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Footer Navigasi Slide --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400 no-print">
                    <span>Slide 5 dari 6 • Bagian IV: Dampak Strategis &amp; Manfaat</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevSlide()" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer">&larr; Sebelumnya</button>
                        <button type="button" @click="nextSlide()" class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold cursor-pointer">Bagian V: Alur Presentasi &rarr;</button>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- SLIDE 6: BAGIAN V — PANDUAN ALUR WAKTU PRESENTASI (15 MENIT)              --}}
            {{-- ========================================================================= --}}
            <div x-show="viewMode === 'all' || currentSlide === 6"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="slide-panel bg-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl min-h-[540px] flex flex-col justify-between space-y-6">

                <div class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-800 pb-4 gap-2">
                        <div>
                            <span class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Bagian V — Panduan Pelaksanaan Rapat</span>
                            <h3 class="text-xl sm:text-2xl font-black text-white flex items-center gap-2 mt-1">
                                <span>⏱️</span> Panduan Alur Waktu Presentasi di Depan Dekanat (Durasi 15 Menit)
                            </h3>
                        </div>
                        <span class="text-xs text-slate-400 font-mono px-3 py-1 rounded-full bg-slate-800 border border-slate-700 self-start sm:self-auto">
                            Struktur Paparan Rapat Pimpinan
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                        <div class="p-5 rounded-2xl bg-slate-800/90 border border-slate-700 space-y-2">
                            <div class="text-emerald-400 font-black text-xs sm:text-sm">01. Menit 00 - 03 (Pengenalan &amp; Masalah)</div>
                            <p class="text-slate-300 leading-relaxed">
                                Buka dengan menyampaikan masalah manual selama ini, perlunya sistem mandiri fakultas, dan peran SIKAP sebagai back-up handal disaat sistem universitas mengalami kendala teknis.
                            </p>
                        </div>

                        <div class="p-5 rounded-2xl bg-slate-800/90 border border-slate-700 space-y-2">
                            <div class="text-emerald-400 font-black text-xs sm:text-sm">02. Menit 03 - 07 (Garis Besar SIKAP)</div>
                            <p class="text-slate-300 leading-relaxed">
                                Jelaskan konsep 3 peran (Pegawai mandiri di HP, Atasan yang memverifikasi, dan Dekanat yang memegang kokpit data agregat).
                            </p>
                        </div>

                        <div class="p-5 rounded-2xl bg-slate-800/90 border border-slate-700 space-y-2">
                            <div class="text-emerald-400 font-black text-xs sm:text-sm">03. Menit 07 - 12 (Live Demo Fitur Kunci)</div>
                            <p class="text-slate-300 leading-relaxed">
                                Buka langsung di layar: <strong>Peta Jabatan ABK</strong> (tunjukkan posisi defisit), <strong>E-Cuti Paperless</strong>, dan <strong>Matriks Talenta</strong>.
                            </p>
                        </div>

                        <div class="p-5 rounded-2xl bg-slate-800/90 border border-slate-700 space-y-2">
                            <div class="text-emerald-400 font-black text-xs sm:text-sm">04. Menit 12 - 15 (Manfaat &amp; Diskusi)</div>
                            <p class="text-slate-300 leading-relaxed">
                                Rangkum manfaat efisiensi anggaran dan kesiapan akreditasi, lalu buka sesi tanggapan dan arahan dari Ibu Dekan Prof. Wan Nishfa Dewi, S.Kp, MNg, PhD beserta jajaran Pimpinan Dekanat.
                            </p>
                        </div>
                    </div>

                    {{-- Penutup & Sesi Arahan Pimpinan --}}
                    <div class="p-6 rounded-2xl bg-gradient-to-r from-emerald-900/50 via-teal-900/40 to-slate-800 border border-emerald-500/30 text-center space-y-2">
                        <div class="text-xs font-bold uppercase tracking-widest text-emerald-300">Terima Kasih • Sesi Diskusi &amp; Arahan Pimpinan</div>
                        <h4 class="text-lg sm:text-xl font-black text-white">
                            Mohon Arahan &amp; Masukan Ibu Dekan Prof. Wan Nishfa Dewi, S.Kp, MNg, PhD beserta Jajaran Pimpinan Dekanat
                        </h4>
                        <p class="text-xs text-slate-300 max-w-2xl mx-auto">
                            Fakultas Keperawatan Universitas Riau • Mewujudkan Tata Kelola Kepegawaian Modern, Akuntabel, dan Berdaulat Data
                        </p>
                    </div>
                </div>

                {{-- Footer Navigasi Slide --}}
                <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400 no-print">
                    <span>Slide 6 dari 6 • Selesai</span>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="prevSlide()" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold cursor-pointer">&larr; Sebelumnya</button>
                        <button type="button" @click="goToSlide(1)" class="px-3.5 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black cursor-pointer">↺ Kembali ke Slide 1</button>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- BOTTOM CONTROL BAR: NAVIGASI SLIDE PRESENTASI INTERAKTIF                  --}}
            {{-- ========================================================================= --}}
            <div class="no-print bg-slate-900 text-white rounded-2xl p-3 sm:p-4 shadow-md border border-slate-800 space-y-3">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    {{-- Indikator Slide & Progress --}}
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-xl bg-emerald-500 text-slate-950 font-black text-xs tracking-wide uppercase">
                            <span x-show="viewMode === 'slides'" x-text="'Slide ' + currentSlide + ' / ' + totalSlides"></span>
                            <span x-show="viewMode === 'all'">Semua Slide</span>
                        </span>
                        <span class="text-xs sm:text-sm font-bold text-slate-200 truncate"
                              x-text="viewMode === 'slides' ? slides[currentSlide - 1].title : 'Tampilan Dokumen Lengkap (Seluruh Bagian)'">
                        </span>
                    </div>

                    {{-- Tombol Kontrol Slide (Prev / Next / Mode / Fullscreen) --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button"
                                @click="prevSlide()"
                                x-show="viewMode === 'slides'"
                                :disabled="currentSlide === 1"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:cursor-not-allowed text-white border border-slate-700 transition cursor-pointer flex items-center gap-1">
                            <span>&larr;</span>
                            <span class="hidden sm:inline">Sebelumnya</span>
                        </button>

                        <button type="button"
                                @click="nextSlide()"
                                x-show="viewMode === 'slides'"
                                :disabled="currentSlide === totalSlides"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 disabled:opacity-40 disabled:cursor-not-allowed text-slate-950 transition cursor-pointer flex items-center gap-1 shadow-xs">
                            <span class="hidden sm:inline">Slide Berikutnya</span>
                            <span class="sm:hidden">Lanjut</span>
                            <span>&rarr;</span>
                        </button>

                        <div class="h-4 w-px bg-slate-700 mx-1 hidden sm:block"></div>

                        <button type="button"
                                @click="viewMode = (viewMode === 'slides' ? 'all' : 'slides')"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition cursor-pointer"
                                x-text="viewMode === 'slides' ? '📑 Lihat Semua Sekaligus' : '🖥️ Kembali ke Mode Slide'">
                        </button>

                        <button type="button"
                                @click="toggleFullscreen()"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-indigo-600 hover:bg-indigo-500 text-white transition cursor-pointer flex items-center gap-1.5">
                            <span x-text="isFullscreen ? '🗗 Keluar Layar Penuh' : '⛶ Layar Penuh'"></span>
                        </button>
                    </div>
                </div>

                {{-- Tab Lompat Cepat Antar Slide & Progress Bar --}}
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-1.5 pt-1">
                    <template x-for="s in slides" :key="s.id">
                        <button type="button"
                                @click="goToSlide(s.id)"
                                :class="viewMode === 'slides' && currentSlide === s.id
                                    ? 'bg-emerald-500/20 border-emerald-400 text-emerald-300 font-bold'
                                    : 'bg-slate-800/70 border-slate-700/80 text-slate-400 hover:text-white hover:bg-slate-800'"
                                class="px-2.5 py-1.5 rounded-xl border text-[11px] text-left truncate transition cursor-pointer flex items-center gap-1.5">
                            <span class="w-4 h-4 rounded-full text-[10px] flex items-center justify-center shrink-0"
                                  :class="viewMode === 'slides' && currentSlide === s.id ? 'bg-emerald-400 text-slate-950 font-black' : 'bg-slate-700 text-slate-300'"
                                  x-text="s.id"></span>
                            <span class="truncate" x-text="s.short"></span>
                        </button>
                    </template>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
