<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 notranslate" translate="no">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Bahan Paparan Pimpinan Dekanat
                    </span>
                    <span class="text-xs text-slate-500 font-mono">T.A. {{ $year }} / Semester {{ date('n') >= 7 ? 'Ganjil' : 'Genap' }}</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mt-1 flex items-center gap-2">
                    <span>🎯</span> Paparan Transformasi Digital Kepegawaian (SIKAP FKp UNRI)
                </h1>
                <p class="text-xs text-slate-600 mt-0.5">
                    Pengenalan Konsep, Garis Besar Arsitektur, dan Penjelasan Fitur Terpadu Manajemen SDM Fakultas Keperawatan
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 no-print">
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-slate-800 hover:bg-slate-900 rounded-xl shadow-xs transition cursor-pointer">
                    <x-icon name="printer" class="w-4 h-4 text-white" />
                    <span>Cetak Lembar Paparan (PDF)</span>
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

    {{-- Gaya Khusus Cetak & Tampilan Presisi --}}
    <style>
        @media print {
            nav, header, .no-print, footer, #sidebar { display: none !important; }
            body { background: white !important; color: #0f172a !important; font-size: 10pt !important; margin: 0 !important; }
            .print-container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
            .print-card { border: 1px solid #cbd5e1 !important; break-inside: avoid; margin-bottom: 14px !important; box-shadow: none !important; }
            .page-break { page-break-before: always; }
            .print-hidden { display: none !important; }
        }
    </style>

    <div class="py-6" x-data="{ activeSection: 'all', showSpeakerNotes: true }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 print-container">

            {{-- ========================================================================= --}}
            {{-- KOP SURAT & IDENTITAS RESMI PRESENTASI                                   --}}
            {{-- ========================================================================= --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs print-card notranslate" translate="no">
                <div class="flex flex-col md:flex-row items-center justify-between gap-6 border-b border-slate-200 pb-5">
                    <div class="flex items-center gap-4 text-center md:text-left">
                        <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-16 w-auto object-contain shrink-0 mx-auto md:mx-0">
                        <div>
                            <p class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">
                                Kementerian Pendidikan Tinggi, Sains, dan Teknologi
                            </p>
                            <h2 class="text-base sm:text-lg font-black text-[#007a3d] uppercase tracking-tight">
                                Universitas Riau — Fakultas Keperawatan
                            </h2>
                            <p class="text-xs font-semibold text-slate-700 mt-0.5">
                                Sistem Informasi Kepegawaian &amp; Kinerja Aparatur (SIKAP) • Bahan Paparan Sidang Pimpinan
                            </p>
                        </div>
                    </div>

                    <div class="text-center md:text-right text-xs text-slate-500 shrink-0">
                        <div><strong>Tanggal Paparan:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}</div>
                        <div><strong>Pemapar:</strong> Pengelola Kepegawaian &amp; Sistem TI FKp UNRI</div>
                        <div class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                Dokumen Resmi Dekanat
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Ringkasan Eksekutif Judul --}}
                <div class="pt-5 text-center md:text-left">
                    <h3 class="text-lg sm:text-xl font-black text-slate-900 leading-tight">
                        Transformasi Tata Kelola Kepegawaian: Dari Manual Menuju Digital Terpadu Berbasis Kedaulatan Data Fakultas
                    </h3>
                    <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                        Membangun sistem mandiri tingkat fakultas yang menjembatani kebutuhan operasional harian Dosen, Tenaga Kependidikan, dan Pimpinan Dekanat yang selama ini terfragmentasi serta melengkapi sistem kepegawaian nasional.
                    </p>
                </div>
            </div>

            {{-- NAVIGASI TAB MODUS PRESENTASI (HANYA TAMPIL DI LAYAR) --}}
            <div class="flex flex-wrap items-center justify-between gap-3 no-print bg-slate-100 p-2 rounded-2xl">
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="activeSection = 'all'" :class="activeSection === 'all' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        📑 Tampilkan Semua Paparan
                    </button>
                    <button type="button" @click="activeSection = 'urgensi'" :class="activeSection === 'urgensi' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        1. Latar Belakang &amp; Urgensi
                    </button>
                    <button type="button" @click="activeSection = 'arsitektur'" :class="activeSection === 'arsitektur' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        2. Garis Besar Aplikasi SIKAP
                    </button>
                    <button type="button" @click="activeSection = 'fitur'" :class="activeSection === 'fitur' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        3. Rincian Fitur &amp; Menu
                    </button>
                    <button type="button" @click="activeSection = 'manfaat'" :class="activeSection === 'manfaat' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        4. Nilai Tambah &amp; Manfaat
                    </button>
                    <button type="button" @click="activeSection = 'alur'" :class="activeSection === 'alur' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        5. Panduan Alur Bicara (15 Menit)
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 cursor-pointer font-medium select-none">
                        <input type="checkbox" x-model="showSpeakerNotes" class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span>Tampilkan Catatan Bicara (*Speaker Talking Points*)</span>
                    </label>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 1. LATAR BELAKANG & URGENSI: MENGAPA FAKULTAS BUTUH APLIKASI SIKAP?       --}}
            {{-- ========================================================================= --}}
            <div x-show="activeSection === 'all' || activeSection === 'urgensi'" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs print-card space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-rose-600 uppercase tracking-wider">Bagian I — Latar Belakang &amp; Analisis Kebutuhan</span>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>🔍</span> Mengapa Fakultas Keperawatan Butuh Sistem Sendiri (SIKAP)?
                        </h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        Manual &rarr; Digital
                    </span>
                </div>

                {{-- Catatan Bicara Pemapar --}}
                <div x-show="showSpeakerNotes" class="bg-rose-50 border border-rose-200 rounded-xl p-4 text-xs text-rose-950 space-y-1.5">
                    <div class="font-bold text-rose-900 flex items-center gap-1.5">
                        <x-icon name="volume-2" class="w-4 h-4 text-rose-700" />
                        Poin Bicara ke Pimpinan (Talking Points):
                    </div>
                    <p class="leading-relaxed">
                        <em>"Bapak Dekan dan Pimpinan yang kami hormati, selama bertahun-tahun pengelolaan data dosen dan tendik di fakultas kita masih mengandalkan berkas fisik kertas, map formulir, dan rekap Excel yang terpisah-pisah. Ketika pimpinan membutuhkan data mendesak—misalnya rekap kehadiran, ketersediaan formasi anjab, atau sisa cuti—staf TU harus membongkar lemari berkas secara manual.  
                        Memang pemerintah memiliki aplikasi nasional (seperti SIASN BKN atau SISTER Kemendikbud), <strong>namun sistem nasional tersebut berfokus makro dan memiliki keterbatasan akses langsung bagi operasional harian Dekanat</strong>. Pimpinan fakultas tidak bisa memantau logbook harian, presensi GPS di gedung fakultas, maupun alur cuti secara cepat. <strong>SIKAP hadir sebagai solusi internal fakultas untuk mengisi ruang kosong tersebut.</strong>"</em>
                    </p>
                </div>

                {{-- Tabel Komparasi: Kondisi Lama vs Solusi SIKAP --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    {{-- Kondisi 1 --}}
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="flex items-center gap-2 text-rose-700 font-bold text-xs uppercase">
                            <span>❌</span> Masalah Pengelolaan Manual
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-700">
                            <li>• Berkas permohonan cuti, KGB, dan SK fisik menumpuk di lemari TU dan rentan hilang/rusak.</li>
                            <li>• Pelaporan aktivitas kerja pegawai tidak terdokumentasi harian (sulit evaluasi SKP).</li>
                            <li>• Penghitungan analisis beban kerja (ABK) dikerjakan manual dan sulit disajikan secara visual.</li>
                        </ul>
                    </div>

                    {{-- Kondisi 2 --}}
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="flex items-center gap-2 text-amber-700 font-bold text-xs uppercase">
                            <span>⚠️</span> Keterbatasan Sistem Nasional
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-700">
                            <li>• Bersifat terpusat nasional (BKN / Kemendikbud) dengan hak akses terbatas untuk pimpinan fakultas.</li>
                            <li>• Tidak mendukung presensi harian berbasis geofencing radius gedung spesifik FKp UNRI.</li>
                            <li>• Tidak menyediakan hirarki persetujuan berjenjang internal (Koordinator &rarr; Dekan).</li>
                        </ul>
                    </div>

                    {{-- Kondisi 3 --}}
                    <div class="p-4 rounded-xl border border-emerald-300 bg-emerald-50/70 space-y-2">
                        <div class="flex items-center gap-2 text-emerald-800 font-bold text-xs uppercase">
                            <span>✅</span> Terobosan Aplikasi SIKAP
                        </div>
                        <ul class="space-y-1.5 text-xs text-emerald-950">
                            <li>• <strong>Kedaulatan Data Fakultas:</strong> Data lengkap 83 SDM riil (Dosen, Tendik, PHL) ada di tangan Dekanat.</li>
                            <li>• <strong>Layanan Mandiri di HP:</strong> Pegawai bisa absen selfie GPS, isi logbook, dan ajukan cuti paperless.</li>
                            <li>• <strong>Dashboard Keputusan Dekanat:</strong> Data siap saji untuk rapat evaluasi dan bahan usulan formasi ke Rektorat.</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 2. GARIS BESAR & ARSITEKTUR APLIKASI SIKAP                                --}}
            {{-- ========================================================================= --}}
            <div x-show="activeSection === 'all' || activeSection === 'arsitektur'" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs print-card space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Bagian II — Konsep &amp; Arsitektur Sistem</span>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>🏗️</span> Garis Besar Aplikasi SIKAP FKp UNRI
                        </h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        3 Tingkat Peran Terintegrasi
                    </span>
                </div>

                {{-- Catatan Bicara Pemapar --}}
                <div x-show="showSpeakerNotes" class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-xs text-indigo-950 space-y-1.5">
                    <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                        <x-icon name="volume-2" class="w-4 h-4 text-indigo-700" />
                        Poin Bicara ke Pimpinan (Talking Points):
                    </div>
                    <p class="leading-relaxed">
                        <em>"SIKAP dirancang bukan untuk mempersulit, melainkan memberi kemudahan bagi seluruh pemangku kepentingan di fakultas. Sistem ini menghubungkan 3 level pengguna dalam satu alur kerja mulus: <strong>Level 1 adalah Pegawai Mandiri</strong> yang dapat mengurus administrasi dari genggaman ponsel; <strong>Level 2 adalah Atasan Langsung</strong> yang memverifikasi tugas harian stafnya dalam 1 kali klik; dan <strong>Level 3 adalah Pimpinan Dekanat</strong> yang memegang dashboard eksekutif untuk melihat kesehatan organisasi secara utuh kapan saja."</em>
                    </p>
                </div>

                {{-- 3 Pilar Arsitektur --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Pilar 1: Pegawai --}}
                    <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs">1</span>
                            <h4 class="font-bold text-blue-900 text-xs uppercase">Pegawai Mandiri (Dosen &amp; Tendik)</h4>
                        </div>
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            Layanan mandiri tanpa kertas (*Self-Service Portal*): Presensi GPS foto live, pengisian logbook 7.5 jam/hari, pengajuan cuti, dan pemutakhiran biodata riwayat karir pribadi.
                        </p>
                        <div class="text-[10px] text-blue-700 font-semibold bg-white p-2 rounded-lg border border-blue-100">
                            📱 Aksesibel via Smartphone &amp; Komputer
                        </div>
                    </div>

                    {{-- Pilar 2: Atasan --}}
                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/50 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-600 text-white flex items-center justify-center font-bold text-xs">2</span>
                            <h4 class="font-bold text-amber-900 text-xs uppercase">Atasan Langsung (Kajur/Kaprodi/Pokja)</h4>
                        </div>
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            Meja supervisi pembinaan bawahan: Verifikasi logbook harian staf, pemberian catatan revisi, dan pertimbangan ketersediaan personil pengganti saat cuti diajukan.
                        </p>
                        <div class="text-[10px] text-amber-800 font-semibold bg-white p-2 rounded-lg border border-amber-100">
                            ⚡ Persetujuan Cepat (One-Click Approval)
                        </div>
                    </div>

                    {{-- Pilar 3: Dekanat --}}
                    <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/50 space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">3</span>
                            <h4 class="font-bold text-emerald-900 text-xs uppercase">Pimpinan Dekanat (Dekan &amp; Wadek)</h4>
                        </div>
                        <p class="text-slate-600 text-[11px] leading-relaxed">
                            Kokpit strategis pengambilan keputusan: Peta formasi ABK, radar pensiun 1-3 tahun, matriks talenta 9-kotak, dan analitik beban kerja seluruh program studi.
                        </p>
                        <div class="text-[10px] text-emerald-800 font-semibold bg-white p-2 rounded-lg border border-emerald-100">
                            📊 Data Real-Time Siap Dibawa ke Rektorat
                        </div>
                    </div>
                </div>

                {{-- Kepatuhan Regulasi Nasional --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 flex flex-wrap items-center justify-between gap-3">
                    <span class="font-bold text-slate-800">Kepatuhan Standar Regulasi Nasional ASN:</span>
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-medium">📜 PermenPAN-RB No. 1/2020 (Anjab-ABK)</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-medium">📜 PermenPAN-RB No. 3/2020 (Manajemen Talenta)</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-medium">📜 Peraturan BKN No. 24/2017 (Cuti ASN)</span>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 3. PAPARAN DAN PENJELASAN MENU / FITUR DALAM APLIKASI                     --}}
            {{-- ========================================================================= --}}
            <div x-show="activeSection === 'all' || activeSection === 'fitur'" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs print-card space-y-6">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Bagian III — Katalog Fitur &amp; Menu Aplikasi</span>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>📑</span> Penjelasan Menu &amp; Fitur Utama dalam SIKAP
                        </h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        Modul Lengkap
                    </span>
                </div>

                {{-- Catatan Bicara Pemapar --}}
                <div x-show="showSpeakerNotes" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-xs text-emerald-950 space-y-1.5">
                    <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                        <x-icon name="volume-2" class="w-4 h-4 text-emerald-700" />
                        Poin Bicara ke Pimpinan (Talking Points):
                    </div>
                    <p class="leading-relaxed">
                        <em>"Berikut kami paparkan menu-menu utama di dalam aplikasi yang siap digunakan: Pertama, pada **Layanan Mandiri Pegawai**, dosen dan staf tidak perlu lagi mengisi kertas formulir manual. Kedua, pada **Meja Verifikasi Atasan**, setiap koordinator dapat memantau kedisiplinan dan capaian harian anggotanya. Dan Ketiga, pada **Menu Pimpinan**, Dekan dan Wakil Dekan memiliki akses langsung ke Peta Jabatan, Analitik Kehadiran, serta Matriks Talenta untuk melihat peta kekuatan SDM fakultas."</em>
                    </p>
                </div>

                {{-- GRID FITUR PER KELOMPOK MENU --}}
                <div class="space-y-5">

                    {{-- KELOMPOK 1: LAYANAN MANDIRI PEGAWAI --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 text-xs font-bold text-blue-900 uppercase tracking-wider">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                            <span>A. Modul Layanan Mandiri Pegawai (Front-Office)</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1.5">
                                <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>📸</span> Presensi Mandiri GPS &amp; Selfie
                                </strong>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Check-in/out online terkunci radius GPS kampus FKp UNRI dan foto wajah langsung. Menggantikan mesin finger manual yang sering rusak atau antre.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1.5">
                                <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>⏱️</span> E-Logbook Kinerja Harian
                                </strong>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Pegawai mengisi rincian tugas harian sesuai SKP dengan target jam efektif 450 menit (7.5 jam/hari) disertai bukti dokumen/foto pendukung.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1.5">
                                <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>🏖️</span> E-Cuti Online Paperless
                                </strong>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Pengajuan cuti tahunan, sakit, melahirkan, dan alasan penting via HP dengan kalkulator sisa hak cuti otomatis (12 hari kerja/tahun).
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1.5">
                                <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>👤</span> Profil &amp; Riwayat Karir Digital
                                </strong>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Biodata terpusat mencakup riwayat kenaikan pangkat, jabatan, pendidikan, STR/SIP, dan brankas unduh berkas SK asli ber-watermark.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1.5">
                                <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>⭐</span> Transparansi Talenta Mandiri
                                </strong>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Pegawai dapat melihat posisi kuadran talenta diri sendiri secara terbuka untuk mengetahui panduan pengembangan karir selanjutnya.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50 space-y-1.5">
                                <strong class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>📖</span> SOP Panduan Interaktif Tiap Menu
                                </strong>
                                <p class="text-[11px] text-slate-600 leading-relaxed">
                                    Setiap halaman dilengkapi tombol SOP interaktif berisi dasar hukum dan panduan pengisian agar pegawai tidak bingung mengoperasikan.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- KELOMPOK 2: SUPERVISI & VERIFIKASI ATASAN --}}
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-amber-900 uppercase tracking-wider">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-600"></span>
                            <span>B. Modul Supervisi Atasan Langsung (Middle-Office)</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 space-y-1.5">
                                <strong class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                                    <span>⚖️</span> Meja Verifikasi &amp; Rekap Logbook
                                </strong>
                                <p class="text-[11px] text-slate-700 leading-relaxed">
                                    Atasan (Kajur, Kaprodi, Kasubbag) memverifikasi keabsahan uraian kerja staf binaannya dengan fitur persetujuan massal (*Batch Approve*) atau penolakan dengan catatan pembinaan.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-amber-200 bg-amber-50/40 space-y-1.5">
                                <strong class="text-xs font-bold text-amber-950 flex items-center gap-1.5">
                                    <span>✍️</span> Verifikasi Pertimbangan Cuti Berjenjang
                                </strong>
                                <p class="text-[11px] text-slate-700 leading-relaxed">
                                    Memeriksa ketersediaan personil pengganti tugas di unit sebelum meneruskan persetujuan cuti ke Dekan / Wakil Dekan secara elektronik.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- KELOMPOK 3: PENGAMBILAN KEPUTUSAN DEKANAT --}}
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-emerald-900 uppercase tracking-wider">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                            <span>C. Modul Manajerial &amp; Pengambilan Keputusan Dekanat (Executive Governance)</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-xl border border-indigo-200 bg-indigo-50/50 space-y-1.5">
                                <strong class="text-xs font-bold text-indigo-950 flex items-center gap-1.5">
                                    <span>🏛️</span> Peta Jabatan Interaktif &amp; ABK
                                </strong>
                                <p class="text-[11px] text-slate-700 leading-relaxed">
                                    Visualisasi bagan struktur organisasi lengkap dengan perbandingan <strong>Bezetting Riil</strong> vs <strong>Formasi Kebutuhan ABK</strong>. Langsung menyorot posisi jabatan yang <strong>Defisit (Kurang)</strong> sebagai dasar usulan ke Rektorat.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-emerald-200 bg-emerald-50/50 space-y-1.5">
                                <strong class="text-xs font-bold text-emerald-950 flex items-center gap-1.5">
                                    <span>📈</span> Matriks Manajemen Talenta 9-Kotak
                                </strong>
                                <p class="text-[11px] text-slate-700 leading-relaxed">
                                    Pemetaan merit sistem ASN (PermenPAN-RB No. 3/2020) berbasis integrasi skor Kinerja dan skor Potensi. Otomatis membentuk <strong>Talent Pool (Kotak 7, 8, 9)</strong> untuk penyiapan suksesi pimpinan.
                                </p>
                            </div>

                            <div class="p-3.5 rounded-xl border border-blue-200 bg-blue-50/50 space-y-1.5">
                                <strong class="text-xs font-bold text-blue-950 flex items-center gap-1.5">
                                    <span>📊</span> Dashboard Analitik Eksekutif
                                </strong>
                                <p class="text-[11px] text-slate-700 leading-relaxed">
                                    Pemantauan rasio kehadiran harian/bulanan, radar pegawai yang akan pensiun (BUP 1-3 tahun), dan distribusi beban jam logbook per program studi.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 4. MANFAAT & NILAI TAMBAH LANGSUNG BAGI FAKULTAS                          --}}
            {{-- ========================================================================= --}}
            <div x-show="activeSection === 'all' || activeSection === 'manfaat'" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs print-card space-y-4">
                <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-blue-600 uppercase tracking-wider">Bagian IV — Dampak Strategis</span>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>🚀</span> Manfaat &amp; Nilai Tambah Langsung bagi Fakultas Keperawatan
                        </h3>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                        Efisiensi &amp; Akuntabilitas
                    </span>
                </div>

                {{-- Catatan Bicara Pemapar --}}
                <div x-show="showSpeakerNotes" class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-950 space-y-1.5">
                    <div class="font-bold text-blue-900 flex items-center gap-1.5">
                        <x-icon name="volume-2" class="w-4 h-4 text-blue-700" />
                        Poin Bicara ke Pimpinan (Talking Points):
                    </div>
                    <p class="leading-relaxed">
                        <em>"Sebagai penutup dari sisi nilai tambah, penerapan SIKAP memberikan 3 dampak nyata: **Pertama, Efisiensi Anggaran & Birokrasi Paperless**, kita menghemat ratusan rim kertas formulir cuti dan map biodata setiap tahunnya; **Kedua, Pengambilan Keputusan Berbasis Data Riil**, pimpinan tidak lagi menebak-nebak kebutuhan pegawai, melainkan memegang data akurat saat rapat formasi bersama Rektorat; dan **Ketiga, Peningkatan Nilai Akreditasi Institusi**, sistem ini menjadi bukti konkret tata kelola SDM modern dan transparan pada Kriteria Penilaian Akreditasi."</em>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="text-2xl">🌱</div>
                        <h4 class="font-bold text-slate-900 text-xs uppercase">1. Zero Paperwork &amp; Hemat Biaya</h4>
                        <p class="text-slate-600 text-xs leading-relaxed">
                            Menghilangkan biaya pencetakan formulir cuti, map arsip, dan binder kertas. Seluruh berkas tersimpan aman dalam format digital terproteksi QR Code.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="text-2xl">⚡</div>
                        <h4 class="font-bold text-slate-900 text-xs uppercase">2. Kecepatan Pelayanan Staf</h4>
                        <p class="text-slate-600 text-xs leading-relaxed">
                            Proses pengajuan cuti dan permohonan kepegawaian yang biasanya memakan waktu berhari-hari kini tuntas dalam hitungan menit via ponsel.
                        </p>
                    </div>

                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-2">
                        <div class="text-2xl">🏆</div>
                        <h4 class="font-bold text-slate-900 text-xs uppercase">3. Penguatan Nilai Akreditasi</h4>
                        <p class="text-slate-600 text-xs leading-relaxed">
                            Menjadi bukti otentik tata pamong perguruan tinggi yang akuntabel, transparan, dan meritokratis pada instrumen akreditasi LAM-PTKes / BAN-PT.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- 5. PANDUAN JADWAL ALUR BICARA PRESENTASI (15 MENIT)                      --}}
            {{-- ========================================================================= --}}
            <div x-show="activeSection === 'all' || activeSection === 'alur'" class="bg-slate-900 rounded-2xl p-6 text-white shadow-md print-card space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                        <span>⏱️</span> Panduan Alur Waktu Presentasi di Depan Dekanat (Durasi 15 Menit)
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">Cheatsheet Paparan Rapat</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 space-y-1.5">
                        <div class="text-emerald-400 font-bold text-xs">01. Menit 00 - 03 (Pengenalan &amp; Masalah)</div>
                        <p class="text-slate-300 leading-relaxed">
                            Buka dengan menyampaikan masalah manual selama ini dan keterbatasan sistem nasional. Tegaskan pentingnya kedaulatan data tingkat fakultas.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 space-y-1.5">
                        <div class="text-emerald-400 font-bold text-xs">02. Menit 03 - 07 (Garis Besar SIKAP)</div>
                        <p class="text-slate-300 leading-relaxed">
                            Jelaskan konsep 3 peran (Pegawai mandiri di HP, Atasan yang memverifikasi, dan Dekanat yang memegang kokpit data agregat).
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 space-y-1.5">
                        <div class="text-emerald-400 font-bold text-xs">03. Menit 07 - 12 (Live Demo Fitur Kunci)</div>
                        <p class="text-slate-300 leading-relaxed">
                            Buka langsung di layar: <strong>Peta Jabatan ABK</strong> (tunjukkan posisi defisit), <strong>E-Cuti Paperless</strong>, dan <strong>Matriks Talenta</strong>.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-800/90 border border-slate-700 space-y-1.5">
                        <div class="text-emerald-400 font-bold text-xs">04. Menit 12 - 15 (Manfaat &amp; Diskusi)</div>
                        <p class="text-slate-300 leading-relaxed">
                            Rangkum manfaat efisiensi anggaran dan kesiapan akreditasi, lalu buka sesi tanggapan dan arahan dari Bapak Dekan.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
