<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 notranslate" translate="no">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Bahan Paparan Pimpinan
                    </span>
                    <span class="text-xs text-slate-500 font-mono">T.A. {{ $year }} / Semester {{ date('n') >= 7 ? 'Ganjil' : 'Genap' }}</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 mt-1 flex items-center gap-2">
                    <span>🎯</span> Lembar Paparan Eksekutif (Executive Brief)
                </h1>
                <p class="text-xs text-slate-600 mt-0.5">
                    Ringkasan Strategis Manajemen SDM, Formasi ABK, Radar Pensiun, dan Kesiapan Sistem Merit FKp UNRI
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

    {{-- Gaya Cetak (Print CSS) --}}
    <style>
        @media print {
            nav, header, .no-print, footer, #sidebar { display: none !important; }
            body { background: white !important; color: black !important; font-size: 11pt !important; margin: 0 !important; }
            .print-container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; border: none !important; }
            .page-break { page-break-before: always; }
            .print-header { display: flex !important; }
            .card-slide { border: 1px solid #cbd5e1 !important; break-inside: avoid; }
        }
    </style>

    <div class="py-6" x-data="{ activeTab: 'semua', showTalkingPoints: true }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 print-container">

            {{-- KOP INSTANSI RESMI (Tampil Khusus Saat Cetak / Header Rapat) --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs flex flex-col md:flex-row items-center justify-between gap-6 notranslate" translate="no">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-16 w-auto object-contain shrink-0">
                    <div>
                        <p class="text-[10px] font-bold tracking-widest text-slate-500 uppercase">
                            Kementerian Pendidikan Tinggi, Sains, dan Teknologi
                        </p>
                        <h2 class="text-base sm:text-lg font-black text-[#007a3d] uppercase tracking-tight">
                            Universitas Riau — Fakultas Keperawatan
                        </h2>
                        <p class="text-xs font-semibold text-slate-700 mt-0.5">
                            Sistem Informasi Kepegawaian &amp; Kinerja Aparatur (SIKAP) • Bahan Sidang/Paparan Pimpinan
                        </p>
                    </div>
                </div>

                <div class="text-left md:text-right text-xs text-slate-500 shrink-0">
                    <div><strong>Tanggal Rilis:</strong> {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}</div>
                    <div><strong>Disusun Oleh:</strong> Tim Pengelola Kepegawaian &amp; TI</div>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                            Status Data: Sinkronisasi Real-Time
                        </span>
                    </div>
                </div>
            </div>

            {{-- 1. KILAS ANGKA STRATEGIS DEKANAT (KPI HIGHLIGHTS) --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total SDM Aktif</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['total_aktif'] }}</div>
                    <div class="text-[11px] text-blue-600 font-semibold mt-0.5">
                        {{ $kpis['total_dosen'] }} Dosen • {{ $kpis['total_tendik'] }} Tendik
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kebutuhan Formasi</div>
                    <div class="text-2xl font-black text-indigo-700 mt-1">{{ $totalKebutuhanFormasi }}</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Kebutuhan Riil ABK (WKE)
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Defisit Pegawai</div>
                    <div class="text-2xl font-black text-rose-600 mt-1">{{ $defisitJabatan->count() }} <span class="text-xs font-normal">Posisi</span></div>
                    <div class="text-[11px] text-rose-700 font-bold mt-0.5">
                        Prioritas Formasi Baru
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Radar Pensiun (3 Th)</div>
                    <div class="text-2xl font-black text-amber-600 mt-1">
                        {{ count($retirementRadar['projection_1_year'] ?? []) + count($retirementRadar['projection_2_years'] ?? []) + count($retirementRadar['projection_3_years'] ?? []) }}
                    </div>
                    <div class="text-[11px] text-amber-700 font-medium mt-0.5">
                        SDM Memasuki BUP
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Talent Pool (K7-K9)</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $talentPoolCount }} <span class="text-xs font-normal">Pegawai</span></div>
                    <div class="text-[11px] text-emerald-700 font-semibold mt-0.5">
                        Kesiapan Suksesi Karir
                    </div>
                </div>

                <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-2xs">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Disiplin Kehadiran</div>
                    <div class="text-2xl font-black text-slate-800 mt-1">{{ $kpis['on_time_rate'] }}%</div>
                    <div class="text-[11px] text-slate-500 font-medium mt-0.5">
                        Tingkat Tepat Waktu
                    </div>
                </div>
            </div>

            {{-- NAVIGASI TAB MODUS PRESENTASI (NO PRINT) --}}
            <div class="flex flex-wrap items-center justify-between gap-3 no-print bg-slate-100 p-2 rounded-2xl">
                <div class="flex flex-wrap items-center gap-1.5">
                    <button type="button" @click="activeTab = 'semua'" :class="activeTab === 'semua' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        📑 Semua Pilar
                    </button>
                    <button type="button" @click="activeTab = 'peta'" :class="activeTab === 'peta' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        1. Peta Formasi &amp; ABK
                    </button>
                    <button type="button" @click="activeTab = 'pensiun'" :class="activeTab === 'pensiun' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        2. Radar Pensiun (BUP)
                    </button>
                    <button type="button" @click="activeTab = 'talenta'" :class="activeTab === 'talenta' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        3. Sistem Merit 9-Kotak
                    </button>
                    <button type="button" @click="activeTab = 'kinerja'" :class="activeTab === 'kinerja' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        4. Disiplin &amp; Logbook
                    </button>
                    <button type="button" @click="activeTab = 'layanan'" :class="activeTab === 'layanan' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3.5 py-1.5 rounded-xl text-xs transition cursor-pointer">
                        5. E-Cuti &amp; Digitalisasi
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 text-xs text-slate-700 cursor-pointer font-medium select-none">
                        <input type="checkbox" x-model="showTalkingPoints" class="rounded text-indigo-600 focus:ring-indigo-500">
                        <span>Tampilkan Catatan Bicara (*Talking Points*)</span>
                    </label>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- PILAR 1: PETA JABATAN & REKAPITULASI KEBUTUHAN FORMASI ABK               --}}
            {{-- ========================================================================= --}}
            <div x-show="activeTab === 'semua' || activeTab === 'peta'" class="card-slide bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Pilar 1 — Landasan Kebijakan SDM</div>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>🏛️</span> Peta Jabatan &amp; Analisis Beban Kerja (PermenPAN-RB No. 1/2020)
                        </h3>
                    </div>
                    <a href="{{ route('anjab.peta-jabatan') }}" target="_blank" class="no-print text-xs font-bold text-indigo-600 hover:underline flex items-center gap-1">
                        Buka Bagan Interaktif &rarr;
                    </a>
                </div>

                {{-- Catatan Bicara Pimpinan --}}
                <div x-show="showTalkingPoints" class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 text-xs text-indigo-950 space-y-1.5">
                    <div class="font-bold text-indigo-900 flex items-center gap-1.5">
                        <x-icon name="volume-2" class="w-4 h-4 text-indigo-700" />
                        Poin Pembicaraan untuk Pimpinan (Talking Points):
                    </div>
                    <p class="leading-relaxed">
                        <em>"Bapak Dekan dan Pimpinan sekalian, sistem SIKAP telah mengkalkulasi beban kerja seluruh jabatan struktural dan fungsional di lingkungan FKp UNRI berdasarkan standar 1.250 jam kerja efektif per tahun. Dari perhitungan ini, kita memiliki data akurat mengenai jabatan mana saja yang mengalami defisit SDM. <strong>Data ini adalah bahan resmi yang sangat kuat untuk kita ajukan saat rapat usulan penambahan formasi CPNS dan PPPK ke Rektorat UNRI dan Kementerian.</strong>"</em>
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    {{-- Rekap Status Formasi --}}
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                        <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Ringkasan Formasi FKp UNRI</h4>
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center py-1.5 border-b border-slate-200">
                                <span class="text-slate-600">Total Kebutuhan Formasi (ABK):</span>
                                <strong class="font-bold text-slate-900">{{ $totalKebutuhanFormasi }} Orang</strong>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-b border-slate-200">
                                <span class="text-slate-600">Bezetting Pegawai Aktif Riil:</span>
                                <strong class="font-bold text-slate-900">{{ $kpis['total_aktif'] }} Orang</strong>
                            </div>
                            <div class="flex justify-between items-center py-1.5 border-b border-slate-200">
                                <span class="text-rose-700 font-medium">Jabatan Mengalami Kekurangan (Defisit):</span>
                                <span class="px-2 py-0.5 rounded-full font-bold bg-rose-100 text-rose-800 text-[11px]">{{ $defisitJabatan->count() }} Jabatan</span>
                            </div>
                            <div class="flex justify-between items-center py-1.5">
                                <span class="text-emerald-700 font-medium">Jabatan Terpenuhi Ideal:</span>
                                <span class="px-2 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800 text-[11px]">{{ $idealJabatan->count() }} Jabatan</span>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Posisi Defisit Prioritas --}}
                    <div class="lg:col-span-2 overflow-x-auto">
                        <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider mb-2">
                            Daftar Posisi Jabatan Defisit (Rekomendasi Usulan Formasi Baru)
                        </h4>
                        <table class="w-full text-xs text-left border border-slate-200 rounded-lg overflow-hidden">
                            <thead class="bg-slate-100 text-slate-700 font-bold">
                                <tr>
                                    <th class="p-2.5">Nama Jabatan</th>
                                    <th class="p-2.5">Unit Penempatan</th>
                                    <th class="p-2.5 text-center">Bezetting</th>
                                    <th class="p-2.5 text-center">Kebutuhan</th>
                                    <th class="p-2.5 text-center">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($defisitJabatan->take(6) as $dj)
                                    <tr class="hover:bg-slate-50">
                                        <td class="p-2.5 font-semibold text-slate-900">{{ $dj->jabatan->nama_jabatan ?? 'Jabatan' }}</td>
                                        <td class="p-2.5 text-slate-600">{{ $dj->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}</td>
                                        <td class="p-2.5 text-center font-bold text-slate-700">{{ $dj->bezetting }}</td>
                                        <td class="p-2.5 text-center font-bold text-indigo-700">{{ $dj->formasi_pembulatan }}</td>
                                        <td class="p-2.5 text-center font-bold text-rose-600 bg-rose-50/50">
                                            {{ $dj->bezetting - $dj->formasi_pembulatan }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-3 text-center text-slate-400">Seluruh formasi jabatan saat ini dalam status ideal.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- PILAR 2: RADAR BATAS USIA PENSIUN (BUP) & REGENERASI SDM                 --}}
            {{-- ========================================================================= --}}
            <div x-show="activeTab === 'semua' || activeTab === 'pensiun'" class="card-slide bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Pilar 2 — Mitigasi Risiko SDM</div>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>⏳</span> Radar Batas Usia Pensiun (BUP) &amp; Perencanaan Suksesi
                        </h3>
                    </div>
                    <span class="text-xs font-bold text-amber-800 bg-amber-100 px-2.5 py-1 rounded-full">
                        Proyeksi 1 s.d 3 Tahun
                    </span>
                </div>

                {{-- Catatan Bicara Pimpinan --}}
                <div x-show="showTalkingPoints" class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-950 space-y-1.5">
                    <div class="font-bold text-amber-900 flex items-center gap-1.5">
                        <x-icon name="volume-2" class="w-4 h-4 text-amber-700" />
                        Poin Pembicaraan untuk Pimpinan (Talking Points):
                    </div>
                    <p class="leading-relaxed">
                        <em>"Pimpinan yang terhormat, salah satu tantangan terbesar fakultas adalah kekosongan posisi strategis ketika pejabat atau dosen senior pensiun. Dengan sistem radar pensiun otomatis ini, pimpinan dapat melihat proyeksi pensiun hingga 3 tahun ke depan. <strong>Kita tidak lagi reaktif menunggu posisi kosong, melainkan dapat mempersiapkan kaderisasi dan pengusulan formasi pengganti jauh-jauh hari.</strong>"</em>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- 1 Tahun ke Depan --}}
                    <div class="p-4 rounded-xl border border-rose-200 bg-rose-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-900 uppercase">Mendesak (&lt; 1 Tahun)</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-rose-200 text-rose-900">
                                {{ count($retirementRadar['projection_1_year'] ?? []) }} Orang
                            </span>
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-700 divide-y divide-rose-100">
                            @forelse(array_slice($retirementRadar['projection_1_year'] ?? [], 0, 4) as $ret)
                                <li class="pt-1.5">
                                    <strong class="text-slate-900 block">{{ $ret['nama'] }}</strong>
                                    <span class="text-slate-500 text-[11px]">{{ $ret['jabatan'] }} • TMT Pensiun: {{ $ret['bup_date'] ?? '-' }}</span>
                                </li>
                            @empty
                                <li class="text-slate-400 italic pt-1.5">Tidak ada pegawai pensiun dalam 1 tahun ke depan.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- 2 Tahun ke Depan --}}
                    <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-900 uppercase">Waspada (1 - 2 Tahun)</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-200 text-amber-900">
                                {{ count($retirementRadar['projection_2_years'] ?? []) }} Orang
                            </span>
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-700 divide-y divide-amber-100">
                            @forelse(array_slice($retirementRadar['projection_2_years'] ?? [], 0, 4) as $ret)
                                <li class="pt-1.5">
                                    <strong class="text-slate-900 block">{{ $ret['nama'] }}</strong>
                                    <span class="text-slate-500 text-[11px]">{{ $ret['jabatan'] }} • TMT Pensiun: {{ $ret['bup_date'] ?? '-' }}</span>
                                </li>
                            @empty
                                <li class="text-slate-400 italic pt-1.5">Tidak ada pegawai pensiun pada rentang 2 tahun.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- 3 Tahun ke Depan --}}
                    <div class="p-4 rounded-xl border border-blue-200 bg-blue-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-900 uppercase">Perencanaan (2 - 3 Tahun)</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-200 text-blue-900">
                                {{ count($retirementRadar['projection_3_years'] ?? []) }} Orang
                            </span>
                        </div>
                        <ul class="space-y-1.5 text-xs text-slate-700 divide-y divide-blue-100">
                            @forelse(array_slice($retirementRadar['projection_3_years'] ?? [], 0, 4) as $ret)
                                <li class="pt-1.5">
                                    <strong class="text-slate-900 block">{{ $ret['nama'] }}</strong>
                                    <span class="text-slate-500 text-[11px]">{{ $ret['jabatan'] }}</span>
                                </li>
                            @empty
                                <li class="text-slate-400 italic pt-1.5">Tidak ada pegawai pensiun pada rentang 3 tahun.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- PILAR 3: SISTEM MERIT & MATRIKS MANAJEMEN TALENTA 9-KOTAK                --}}
            {{-- ========================================================================= --}}
            <div x-show="activeTab === 'semua' || activeTab === 'talenta'" class="card-slide bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Pilar 3 — Meritokrasi &amp; Suksesi ASN</div>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>📈</span> Matriks Manajemen Talenta 9-Kotak (PermenPAN-RB No. 3/2020)
                        </h3>
                    </div>
                    <a href="{{ route('manajemen-talenta.index') }}" target="_blank" class="no-print text-xs font-bold text-emerald-600 hover:underline flex items-center gap-1">
                        Buka Matriks Talenta &rarr;
                    </a>
                </div>

                {{-- Catatan Bicara Pimpinan --}}
                <div x-show="showTalkingPoints" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-xs text-emerald-950 space-y-1.5">
                    <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                        <x-icon name="volume-2" class="w-4 h-4 text-emerald-700" />
                        Poin Pembicaraan untuk Pimpinan (Talking Points):
                    </div>
                    <p class="leading-relaxed">
                        <em>"Sesuai amanat reformasi birokrasi, sistem promosi dan mutasi di FKp UNRI kini mengadopsi standar Sistem Merit nasional. Penilaian dilakukan transparan melalui 2 sumbu objektif: Kinerja Aktual (SKP &amp; Logbook) dan Potensi Kualifikasi (Pendidikan, Pelatihan, Asesmen). <strong>Pegawai pada Kotak 7, 8, dan 9 secara otomatis menjadi Talent Pool yang siap diprioritaskan untuk penugasan strategis dan promosi kepemimpinan.</strong>"</em>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-emerald-50/80 border border-emerald-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-emerald-900 uppercase">Kuadran 7, 8, 9 (Talent Pool)</span>
                            <span class="px-2 py-0.5 rounded-full font-bold bg-emerald-600 text-white text-xs">{{ $talentPoolCount }} ASN</span>
                        </div>
                        <p class="text-[11px] text-emerald-800 leading-relaxed">
                            Kelompok suksesi berkinerja tinggi dan potensial unggul. Direkomendasikan untuk promosi jabatan, penghargaan, dan tugas strategis institusi.
                        </p>
                    </div>

                    <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-blue-900 uppercase">Kuadran 4, 5, 6 (Menengah)</span>
                            <span class="px-2 py-0.5 rounded-full font-bold bg-blue-600 text-white text-xs">{{ $talentMiddleCount }} ASN</span>
                        </div>
                        <p class="text-[11px] text-blue-800 leading-relaxed">
                            Pegawai dengan kinerja stabil yang siap ditingkatkan kompetensinya melalui bimbingan teknis, sertifikasi, atau penyesuaian rotasi penugasan.
                        </p>
                    </div>

                    <div class="bg-slate-100 border border-slate-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-slate-800 uppercase">Kuadran 1, 2, 3 (Pembinaan)</span>
                            <span class="px-2 py-0.5 rounded-full font-bold bg-slate-600 text-white text-xs">{{ $talentLowCount }} ASN</span>
                        </div>
                        <p class="text-[11px] text-slate-700 leading-relaxed">
                            Pegawai yang memerlukan pembinaan intensif dari atasan langsung, peninjauan beban kerja, atau konseling motivasi peningkatan produktivitas.
                        </p>
                    </div>
                </div>
            </div>

            {{-- ========================================================================= --}}
            {{-- PILAR 4 & 5: DISIPLIN KERJA, E-LOGBOOK & PAPERLESS E-CUTI                --}}
            {{-- ========================================================================= --}}
            <div x-show="activeTab === 'semua' || activeTab === 'kinerja' || activeTab === 'layanan'" class="card-slide grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- PILAR 4: DISIPLIN & LOGBOOK --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <div class="text-xs font-bold text-blue-600 uppercase tracking-wider">Pilar 4 — Akuntabilitas Anggaran</div>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>⏱️</span> Disiplin Presensi &amp; Logbook Harian
                        </h3>
                    </div>

                    <div x-show="showTalkingPoints" class="bg-blue-50 border border-blue-200 rounded-xl p-3.5 text-xs text-blue-950 space-y-1">
                        <strong class="text-blue-900 block font-bold">Talking Points:</strong>
                        <p class="leading-relaxed">
                            <em>"Presensi menggunakan validasi Geofencing radius kampus dan foto selfie biometrik dengan sistem anti-titip absen. Didukung E-Logbook yang memastikan seluruh staf memenuhi jam efektif 7,5 jam/hari (450 menit) sebagai akuntabilitas pembayaran Tukin."</em>
                        </p>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Ketepatan Waktu Presensi:</span>
                            <strong class="text-slate-900">{{ $kpis['on_time_rate'] }}%</strong>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Total Logbook Diajukan Bulan Ini:</span>
                            <strong class="text-slate-900">{{ $kpis['logbook_total'] }} Aktivitas</strong>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Tingkat Persetujuan Atasan:</span>
                            <strong class="text-emerald-700">{{ $kpis['logbook_rate'] }}%</strong>
                        </div>
                    </div>
                </div>

                {{-- PILAR 5: E-CUTI PAPERLESS --}}
                <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Pilar 5 — Birokrasi Paperless</div>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2 mt-0.5">
                            <span>🏖️</span> E-Cuti Mandiri &amp; Arsip Digital SK
                        </h3>
                    </div>

                    <div x-show="showTalkingPoints" class="bg-amber-50 border border-amber-200 rounded-xl p-3.5 text-xs text-amber-950 space-y-1">
                        <strong class="text-amber-900 block font-bold">Talking Points:</strong>
                        <p class="leading-relaxed">
                            <em>"Pengajuan cuti kini 100% tanpa kertas. Pegawai mengajukan dari ponsel, atasan langsung memverifikasi, dan Dekan/Wadek mengesahkan dengan satu sentuhan. Seluruh berkas SK tersimpan abadi dalam brankas digital terproteksi QR Code."</em>
                        </p>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Alur Persetujuan:</span>
                            <strong class="text-slate-900">2 Tahap (Atasan &rarr; Pimpinan)</strong>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Format Dokumen Output:</span>
                            <strong class="text-slate-900">PDF Ber-Watermark &amp; QR Code</strong>
                        </div>
                        <div class="flex justify-between items-center p-2 rounded-lg bg-slate-50">
                            <span class="text-slate-600">Efisiensi Arsip:</span>
                            <strong class="text-emerald-700">Zero Paperwork &amp; Cloud Backup</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- AGENDA SUSUNAN PRESENTASI 15 MENIT --}}
            <div class="bg-slate-900 rounded-2xl p-6 text-white shadow-md space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                        <span>⏱️</span> Panduan Jadwal Alur Paparan Pimpinan (Rekomendasi 15 Menit)
                    </h3>
                    <span class="text-xs text-slate-400 font-mono">Presentasi Rapat Pimpinan</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="text-emerald-400 font-bold">01. Menit 0 - 3 (Pembuka)</div>
                        <p class="text-slate-300">Visi digitalisasi kepegawaian FKp UNRI dan kepatuhan terhadap regulasi Kementerian (PermenPAN-RB No. 1/2020 &amp; No. 3/2020).</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="text-emerald-400 font-bold">02. Menit 3 - 7 (Peta Jabatan)</div>
                        <p class="text-slate-300">Demokan live bagan Peta Jabatan. Sorot posisi yang defisit untuk dasar usulan formasi baru ke Rektorat.</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="text-emerald-400 font-bold">03. Menit 7 - 11 (Radar Pensiun &amp; Talenta)</div>
                        <p class="text-slate-300">Tunjukkan daftar pegawai pensiun 1-3 tahun dan kesiapan suksesi pejabat dari Talent Pool Kuadran 7, 8, 9.</p>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-800/80 border border-slate-700 space-y-1">
                        <div class="text-emerald-400 font-bold">04. Menit 11 - 15 (Penutup &amp; Diskusi)</div>
                        <p class="text-slate-300">Simulasikan kemudahan verifikasi presensi &amp; cuti via HP, serta sesi tanggapan/arahan dari Dekan.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
