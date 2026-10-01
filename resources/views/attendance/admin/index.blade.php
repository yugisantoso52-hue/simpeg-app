<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="font-bold text-xl text-gray-800 leading-tight flex items-center gap-2">
                        <span>📊</span> {{ __('Rekap Presensi Pegawai') }}
                    </h2>
                    @if(!empty($scopeLabel))
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 shadow-xs">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                            <span>Cakupan:</span> {{ $scopeLabel }}
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">Pemantauan keberadaan, verifikasi foto selfie, dan radius koordinat GPS.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.presensi.export.excel', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 border border-transparent rounded-lg text-xs font-semibold text-white shadow-sm transition" style="background-color: #059669; color: #ffffff;">
                    <span>📊</span> Ekspor Excel
                </a>
                <a href="{{ route('admin.presensi.export.pdf', request()->query()) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 border border-transparent rounded-lg text-xs font-semibold text-white shadow-sm transition" style="background-color: #dc2626; color: #ffffff;">
                    <span>🖨️</span> Cetak PDF
                </a>
                @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->isPimpinan() || Auth::user()->isAtasan()))
                <a href="{{ route('admin.presensi.locations') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-lg text-xs font-semibold text-white shadow-sm transition" style="background-color: #4f46e5; color: #ffffff;">
                    <span>📍</span> Kelola Titik Acuan
                </a>
                @endif
                <a href="{{ route('presensi.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-300 rounded-lg text-xs font-semibold text-gray-700 hover:bg-gray-50 shadow-sm transition">
                    <span>📸</span> Presensi Mandiri
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ modalOpen: false, modalImgSrc: '', modalTitle: '', detailOpen: false, detailData: {} }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Flash Message -->
            @if(session('success'))
                <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-r-lg shadow-sm flex items-center justify-between">
                    <span class="text-sm font-medium text-green-800">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Banner Peringatan Keamanan & Integritas Presensi -->
            @if(($statistics['total_suspicious'] ?? 0) > 0)
                <div class="p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl shadow-sm flex items-start gap-3">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <h4 class="text-sm font-bold text-rose-900">Peringatan Integritas Presensi: Ditemukan {{ $statistics['total_suspicious'] }} Anomali Hari Ini!</h4>
                        <p class="text-xs text-rose-700 mt-0.5">Sistem mendeteksi indikasi titip absen / penggunaan perangkat multi-akun atau anomali perpindahan lokasi yang tidak wajar (Impossible Travel). Silakan periksa badge peringatan merah pada tabel log harian di bawah.</p>
                    </div>
                </div>
            @endif

            <!-- KPI Cards Statistik Hari Ini -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4">
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">TOTAL PEGAWAI</div>
                    <div class="text-2xl font-black text-gray-900 mt-1">{{ $statistics['total_pegawai'] ?? $statistics['total_users'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Terdaftar dalam sistem</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">HADIR HARI INI</div>
                    <div class="text-2xl font-black text-emerald-600 mt-1">{{ $statistics['total_present'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">{{ \Carbon\Carbon::today()->translatedFormat('d F Y') }}</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">WFO (KANTOR)</div>
                    <div class="text-2xl font-black text-blue-600 mt-1">{{ $statistics['total_wfo'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Pegawai presensi kantor</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">WFH (RUMAH)</div>
                    <div class="text-2xl font-black text-indigo-600 mt-1">{{ $statistics['total_wfh'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Pegawai presensi WFH</div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm">
                    <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">TERLAMBAT</div>
                    <div class="text-2xl font-black text-amber-600 mt-1">{{ $statistics['total_late'] }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Waktu check-in &gt; 07.30 WIB</div>
                </div>

                <div class="bg-white p-4 rounded-xl border {{ ($statistics['total_suspicious'] ?? 0) > 0 ? 'border-rose-300 bg-rose-50/40' : 'border-gray-200' }} shadow-sm">
                    <div class="text-[11px] font-bold {{ ($statistics['total_suspicious'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-500' }} uppercase tracking-wider">ANOMALI / TITIP</div>
                    <div class="text-2xl font-black {{ ($statistics['total_suspicious'] ?? 0) > 0 ? 'text-rose-600' : 'text-gray-900' }} mt-1">{{ $statistics['total_suspicious'] ?? 0 }}</div>
                    <div class="text-[10px] text-gray-400 mt-0.5">Indikasi pelanggaran</div>
                </div>
            </div>

            <!-- Tab Switcher Mode Presensi -->
            <div class="bg-gray-100 p-1 rounded-xl border border-gray-200 flex items-center gap-1">
                <a href="{{ route('admin.presensi.index', ['mode' => 'daily', 'month' => request('month', date('n')), 'year' => request('year', date('Y')), 'kategori' => request('kategori', 'all')]) }}"
                   class="flex-1 text-center py-2.5 px-4 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 {{ ($mode ?? 'daily') === 'daily' ? 'bg-white text-slate-900 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/60' }}">
                    <span>📋</span>
                    <span>Log Harian Per Pegawai (Detail GPS & Foto)</span>
                </a>
                <a href="{{ route('admin.presensi.index', ['mode' => 'monthly', 'month' => request('month', date('n')), 'year' => request('year', date('Y'))]) }}"
                   class="flex-1 text-center py-2.5 px-4 rounded-lg text-xs font-bold transition flex items-center justify-center gap-2 {{ ($mode ?? 'daily') === 'monthly' ? 'bg-white text-slate-900 shadow-sm border border-gray-200' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/60' }}">
                    <span>📅</span>
                    <span>Matriks Kalender Bulanan (1 - 31)</span>
                </a>
            </div>

            @if(($mode ?? 'daily') === 'daily')
                <!-- ============================================================= -->
                <!-- MODE 1: LOG HARIAN MODEL A (ACCORDION LIPATAN PER PEGAWAI)    -->
                <!-- ============================================================= -->

                <!-- 1. Kategori Switcher Pills (Dosen, Tendik, PHL) -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.presensi.index', array_merge(request()->query(), ['mode' => 'daily', 'kategori' => 'all'])) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ ($kategori ?? 'all') === 'all' ? 'bg-slate-900 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200' }}">
                        <span>👥 Semua Pegawai</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($kategori ?? 'all') === 'all' ? 'bg-slate-700 text-slate-100' : 'bg-gray-100 text-gray-700' }}">
                            {{ $accordionData['counts']['all'] ?? 0 }}
                        </span>
                    </a>

                    <a href="{{ route('admin.presensi.index', array_merge(request()->query(), ['mode' => 'daily', 'kategori' => 'dosen'])) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ ($kategori ?? 'all') === 'dosen' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-blue-700 hover:bg-blue-50 border border-blue-200' }}">
                        <span>👨‍🏫 Dosen</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($kategori ?? 'all') === 'dosen' ? 'bg-blue-700 text-white' : 'bg-blue-50 text-blue-800' }}">
                            {{ $accordionData['counts']['dosen'] ?? 0 }}
                        </span>
                    </a>

                    <a href="{{ route('admin.presensi.index', array_merge(request()->query(), ['mode' => 'daily', 'kategori' => 'tendik'])) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ ($kategori ?? 'all') === 'tendik' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200' }}">
                        <span>🧑‍💼 Tenaga Kependidikan</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($kategori ?? 'all') === 'tendik' ? 'bg-emerald-700 text-white' : 'bg-emerald-50 text-emerald-800' }}">
                            {{ $accordionData['counts']['tendik'] ?? 0 }}
                        </span>
                    </a>

                    <a href="{{ route('admin.presensi.index', array_merge(request()->query(), ['mode' => 'daily', 'kategori' => 'phl'])) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs {{ ($kategori ?? 'all') === 'phl' ? 'bg-amber-600 text-white shadow-sm' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200' }}">
                        <span>👷 Pegawai PHL / Kontrak</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] {{ ($kategori ?? 'all') === 'phl' ? 'bg-amber-700 text-white' : 'bg-amber-50 text-amber-800' }}">
                            {{ $accordionData['counts']['phl'] ?? 0 }}
                        </span>
                    </a>
                </div>

                <!-- 2. Filter Periode & Pencarian -->
                <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm">
                    <form method="GET" action="{{ route('admin.presensi.index') }}" class="space-y-4">
                        <input type="hidden" name="mode" value="daily">
                        <input type="hidden" name="kategori" value="{{ $kategori ?? 'all' }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                            <!-- Pilihan Bulan -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Bulan:</label>
                                <select name="month" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @foreach([
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ] as $mNum => $mLabel)
                                        <option value="{{ $mNum }}" {{ (int)request('month', $month ?? date('n')) === $mNum ? 'selected' : '' }}>
                                            {{ $mLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilihan Tahun -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Tahun:</label>
                                <select name="year" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ (int)request('year', $year ?? date('Y')) === $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Pencarian Nama / NIP -->
                            <div class="lg:col-span-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Cari Nama Pegawai / NIP:</label>
                                <div class="flex gap-2">
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau NIP pegawai..." class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold transition shrink-0">
                                        Cari
                                    </button>
                                    <a href="{{ route('admin.presensi.index', ['mode' => 'daily', 'kategori' => $kategori ?? 'all']) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition shrink-0">
                                        Reset
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- 3. Info Bar & Accordion Helper Controls -->
                <div class="bg-blue-50/70 border border-blue-200 p-4 rounded-xl flex flex-col md:flex-row md:items-center md:justify-between gap-3 shadow-xs">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📅</span>
                        <div>
                            <div class="text-xs font-extrabold text-blue-900">
                                Periode Presensi: {{ $accordionData['month_name'] }}
                                <span class="ml-2 font-normal text-blue-700">• {{ $accordionData['officialWorkDaysInMonth'] }} Hari Kerja Resmi (Senin - Jumat)</span>
                            </div>
                            <div class="text-[11px] text-blue-600 mt-0.5">
                                Log harian otomatis terpetakan Senin s/d Jumat. Hari Sabtu dan Minggu hanya muncul secara <strong>kondisional</strong> jika pegawai melakukan presensi lembur/kegiatan akhir pekan.
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" @click="$dispatch('open-all-accordions')" class="px-3 py-1.5 bg-white border border-blue-300 hover:bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                            <span>📂</span> Buka Semua
                        </button>
                        <button type="button" @click="$dispatch('close-all-accordions')" class="px-3 py-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 rounded-lg text-xs font-semibold shadow-2xs transition flex items-center gap-1.5">
                            <span>📁</span> Tutup Semua
                        </button>
                    </div>
                </div>

                <!-- 4. Daftar Accordion Lipatan Pegawai -->
                <div class="space-y-4">
                    @forelse($accordionData['pegawaiItems'] as $idx => $item)
                        @php
                            $peg = $item['pegawai'];
                            $kategoriBadgeClass = match($item['kategori']) {
                                'Dosen' => 'bg-blue-100 text-blue-800 border-blue-200',
                                'Tendik' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                default => 'bg-amber-100 text-amber-800 border-amber-200',
                            };
                        @endphp
                        <div x-data="{ isOpen: false }"
                             @open-all-accordions.window="isOpen = true"
                             @close-all-accordions.window="isOpen = false"
                             class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden transition hover:border-gray-300">

                            <!-- Accordion Header (Baris Identitas & Ringkasan KPI Pegawai) -->
                            <div @click="isOpen = !isOpen"
                                 class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 cursor-pointer select-none bg-white hover:bg-slate-50/70 transition">
                                
                                <!-- Sisi Kiri: Profil Pegawai -->
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-base font-extrabold text-slate-700 shrink-0 shadow-2xs">
                                        {{ strtoupper(substr($peg->nama, 0, 2)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <h3 class="font-bold text-sm sm:text-base text-gray-900 truncate">
                                                {{ $peg->nama_lengkap }}
                                            </h3>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $kategoriBadgeClass }}">
                                                {{ $item['kategori'] }}
                                            </span>
                                            @if($item['total_suspicious'] > 0)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                                    ⚠️ {{ $item['total_suspicious'] }} Anomali
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">
                                            NIP: {{ $peg->nip ?? '-' }}
                                            @if($peg->nidn_nuptk)
                                                <span class="text-gray-400 font-sans">• NIDN: {{ $peg->nidn_nuptk }}</span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 truncate mt-0.5">
                                            {{ $peg->jabatan->nama_jabatan ?? 'Pegawai' }} {{ $peg->unitKerja ? '— ' . $peg->unitKerja->nama_unit : '' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Sisi Kanan: Mini KPI Bulanan & Tombol Accordion -->
                                <div class="flex flex-wrap items-center justify-between lg:justify-end gap-2.5 pt-3 lg:pt-0 border-t lg:border-t-0 border-gray-100">
                                    <!-- Pill Hadir -->
                                    <div class="px-2.5 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-center">
                                        <div class="text-[10px] font-semibold text-emerald-600 uppercase">Hadir</div>
                                        <div class="text-xs font-black">{{ $item['total_hadir'] }} <span class="text-[10px] font-normal text-emerald-600">/ {{ $accordionData['officialWorkDaysInMonth'] }}</span></div>
                                    </div>

                                    <!-- Pill Terlambat -->
                                    <div class="px-2.5 py-1.5 rounded-lg {{ $item['total_late'] > 0 ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-gray-50 border border-gray-200 text-gray-500' }} text-center">
                                        <div class="text-[10px] font-semibold uppercase">Telat</div>
                                        <div class="text-xs font-black">{{ $item['total_late'] }}x <span class="text-[10px] font-normal">({{ $item['total_late_minutes'] }}m)</span></div>
                                    </div>

                                    <!-- Pill Cuti -->
                                    @if($item['total_cuti'] > 0)
                                        <div class="px-2.5 py-1.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 text-center">
                                            <div class="text-[10px] font-semibold uppercase">Cuti</div>
                                            <div class="text-xs font-black">{{ $item['total_cuti'] }} Hari</div>
                                        </div>
                                    @endif

                                    <!-- Pill Jam Kerja -->
                                    <div class="px-2.5 py-1.5 rounded-lg bg-slate-50 border border-slate-200 text-slate-800 text-center hidden sm:block">
                                        <div class="text-[10px] font-semibold text-slate-500 uppercase">Total Durasi</div>
                                        <div class="text-xs font-black">{{ $item['total_work_duration'] }}</div>
                                    </div>

                                    <!-- Tombol Accordion Trigger -->
                                    <button type="button"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-bold transition shrink-0"
                                            :class="isOpen ? 'bg-slate-800 text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'">
                                        <span x-text="isOpen ? 'Tutup Log' : 'Buka Log Harian ({{ $item['timeline_count'] }} Hari)'"></span>
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Accordion Body: Tabel Rincian Hari Kerja (Senin - Jumat + Akhir Pekan Kondisional) -->
                            <div x-show="isOpen"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-150"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 -translate-y-2"
                                 class="border-t border-gray-200 bg-slate-50/50">

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-xs text-left">
                                        <thead class="bg-gray-100 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                            <tr>
                                                <th class="px-4 py-2.5">Hari & Tanggal</th>
                                                <th class="px-4 py-2.5">Jam Masuk</th>
                                                <th class="px-3 py-2.5 text-center">Foto Masuk</th>
                                                <th class="px-4 py-2.5">GPS & Radius</th>
                                                <th class="px-4 py-2.5">Jam Pulang</th>
                                                <th class="px-3 py-2.5 text-center">Foto Pulang</th>
                                                <th class="px-4 py-2.5">Durasi Kerja</th>
                                                <th class="px-4 py-2.5">Status & Integritas</th>
                                                <th class="px-4 py-2.5 text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 bg-white">
                                            @foreach($item['timeline'] as $day)
                                                @php
                                                    $att = $day['attendance'];
                                                    $cuti = $day['cuti'];
                                                    $rowBg = '';
                                                    if ($att && $att->is_suspicious) {
                                                        $rowBg = 'bg-rose-50/50';
                                                    } elseif ($day['is_conditional_weekend']) {
                                                        $rowBg = 'bg-indigo-50/40';
                                                    } elseif ($day['is_today']) {
                                                        $rowBg = 'bg-blue-50/30';
                                                    }
                                                @endphp
                                                <tr class="hover:bg-slate-50/90 transition {{ $rowBg }}">
                                                    <!-- 1. Hari & Tanggal -->
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        <div class="font-bold text-gray-900 flex items-center gap-1.5">
                                                            <span>{{ $day['day_name'] }}</span>
                                                            @if($day['is_today'])
                                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-black bg-blue-600 text-white uppercase">Hari Ini</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-[11px] text-gray-500 font-mono mt-0.5">
                                                            {{ Carbon\Carbon::parse($day['date'])->translatedFormat('d F Y') }}
                                                        </div>
                                                        @if($day['is_conditional_weekend'])
                                                            <div class="mt-1">
                                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[9px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                                    🔵 Akhir Pekan (Lembur/Kondisional)
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <!-- 2. Jam Masuk -->
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @if($att && $att->check_in_time)
                                                            <div class="font-bold font-mono text-emerald-700 text-xs">
                                                                {{ $att->check_in_time->timezone('Asia/Jakarta')->format('H:i:s') }} WIB
                                                            </div>
                                                            <div class="flex items-center gap-1 mt-0.5">
                                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase {{ $att->attendance_type === 'wfh' ? 'bg-indigo-100 text-indigo-700' : 'bg-blue-100 text-blue-700' }}">
                                                                    {{ strtoupper($att->attendance_type ?? 'wfo') }}
                                                                </span>
                                                                @if($att->late_minutes > 0)
                                                                    <span class="text-[10px] font-bold text-rose-600">
                                                                        (+{{ $att->late_minutes }}m)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @elseif($cuti)
                                                            <span class="text-[11px] text-blue-600 font-semibold italic">Sedang Cuti</span>
                                                        @elseif($day['is_future'])
                                                            <span class="text-[11px] text-gray-400 italic">Belum Tiba</span>
                                                        @else
                                                            <span class="text-[11px] text-gray-400 font-mono">-</span>
                                                        @endif
                                                    </td>

                                                    <!-- 3. Foto Selfie Masuk -->
                                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                                        @if($att && $att->check_in_photo_path)
                                                            <button type="button"
                                                                    @click="modalOpen = true; modalImgSrc = '{{ $att->check_in_photo_url }}'; modalTitle = 'Foto Masuk - {{ addslashes($peg->nama) }} ({{ $att->check_in_time ? $att->check_in_time->timezone('Asia/Jakarta')->format('d/m H:i') : '' }})'"
                                                                    class="inline-block w-9 h-9 rounded-lg overflow-hidden border border-gray-300 hover:ring-2 hover:ring-blue-500 shadow-2xs transition">
                                                                <img src="{{ $att->check_in_photo_url }}" class="w-full h-full object-cover" alt="Foto Masuk" loading="lazy">
                                                            </button>
                                                        @else
                                                            <span class="text-[10px] text-gray-300">-</span>
                                                        @endif
                                                    </td>

                                                    <!-- 4. GPS & Radius Masuk -->
                                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-[11px]">
                                                        @if($att && $att->check_in_latitude)
                                                            <div class="font-semibold {{ $att->check_in_distance_meters <= 75 ? 'text-emerald-700' : 'text-rose-600' }}">
                                                                {{ number_format($att->check_in_distance_meters, 1) }} m
                                                                @if($att->check_in_distance_meters <= 75)
                                                                    <span class="text-emerald-600 font-bold">✓</span>
                                                                @else
                                                                    <span class="text-rose-600 font-bold">!</span>
                                                                @endif
                                                            </div>
                                                            <div class="text-[10px] text-gray-400 font-sans">
                                                                ± {{ round($att->gps_accuracy ?? 0) }}m
                                                                @if($att->is_mock_location)
                                                                    <span class="px-1 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-700">MOCK</span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-[10px] text-gray-300">-</span>
                                                        @endif
                                                    </td>

                                                    <!-- 5. Jam Pulang -->
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @if($att && $att->check_out_time)
                                                            <div class="font-bold font-mono text-amber-700 text-xs">
                                                                {{ $att->check_out_time->timezone('Asia/Jakarta')->format('H:i:s') }} WIB
                                                            </div>
                                                            @if($att->early_leave_minutes > 0)
                                                                <div class="text-[10px] font-bold text-orange-600">
                                                                    PSW -{{ $att->early_leave_minutes }}m
                                                                </div>
                                                            @endif
                                                        @elseif($att && $att->check_in_time && !$day['is_future'])
                                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                                                Belum Pulang
                                                            </span>
                                                        @elseif($cuti)
                                                            <span class="text-[11px] text-blue-600 font-semibold italic">Sedang Cuti</span>
                                                        @else
                                                            <span class="text-[10px] text-gray-300">-</span>
                                                        @endif
                                                    </td>

                                                    <!-- 6. Foto Selfie Pulang -->
                                                    <td class="px-3 py-3 text-center whitespace-nowrap">
                                                        @if($att && $att->check_out_photo_path)
                                                            <button type="button"
                                                                    @click="modalOpen = true; modalImgSrc = '{{ $att->check_out_photo_url }}'; modalTitle = 'Foto Pulang - {{ addslashes($peg->nama) }} ({{ $att->check_out_time ? $att->check_out_time->timezone('Asia/Jakarta')->format('d/m H:i') : '' }})'"
                                                                    class="inline-block w-9 h-9 rounded-lg overflow-hidden border border-gray-300 hover:ring-2 hover:ring-amber-500 shadow-2xs transition">
                                                                <img src="{{ $att->check_out_photo_url }}" class="w-full h-full object-cover" alt="Foto Pulang" loading="lazy">
                                                            </button>
                                                        @else
                                                            <span class="text-[10px] text-gray-300">-</span>
                                                        @endif
                                                    </td>

                                                    <!-- 7. Durasi Kerja Efektif -->
                                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs">
                                                        @if($att && $att->work_duration_seconds > 0)
                                                            <div class="font-bold text-gray-800">
                                                                {{ $att->work_duration }}
                                                            </div>
                                                            <div class="text-[10px] text-gray-400 font-sans">
                                                                Efektif ASN
                                                            </div>
                                                        @elseif($cuti)
                                                            <span class="text-[11px] text-blue-600 font-medium">0 Jam (Cuti)</span>
                                                        @else
                                                            <span class="text-[10px] text-gray-300">-</span>
                                                        @endif
                                                    </td>

                                                    <!-- 8. Status & Integritas -->
                                                    <td class="px-4 py-3 whitespace-nowrap">
                                                        @if($att)
                                                            @if($att->status === 'late' || $att->late_minutes > 0)
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                                    Terlambat
                                                                </span>
                                                            @else
                                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                                    Tepat Waktu
                                                                </span>
                                                            @endif

                                                            @if($att->is_suspicious)
                                                                <div class="mt-1">
                                                                    <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-100 text-rose-800 border border-rose-300" title="{{ $att->suspicious_reason }}">
                                                                        ⚠️ Anomali
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @elseif($cuti)
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                                🏖️ Cuti ({{ $cuti->jenis_cuti ?? 'Resmi' }})
                                                            </span>
                                                        @elseif($day['is_future'])
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-400">
                                                                Belum Tiba
                                                            </span>
                                                        @else
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                                Belum Presensi
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <!-- 9. Aksi -->
                                                    <td class="px-4 py-3 whitespace-nowrap text-center space-x-1">
                                                        @if($att)
                                                            <button type="button"
                                                                    @click="detailOpen = true; detailData = {
                                                                        nama: '{{ addslashes($peg->nama_lengkap) }}',
                                                                        nip: '{{ addslashes($peg->nip ?? '-') }}',
                                                                        tanggal: '{{ Carbon\Carbon::parse($day['date'])->translatedFormat('d F Y') }}',
                                                                        status: '{{ $att->status_badge['label'] }}',
                                                                        status_badge: '{{ $att->status_badge['class'] }}',
                                                                        in: '{{ $att->check_in_time ? $att->check_in_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}',
                                                                        out: '{{ $att->check_out_time ? $att->check_out_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}',
                                                                        late_minutes: {{ $att->late_minutes }},
                                                                        early_leave_minutes: {{ $att->early_leave_minutes }},
                                                                        duration: '{{ $att->work_duration }}',
                                                                        tipe: '{{ strtoupper($att->attendance_type) }}',
                                                                        distance: '{{ number_format($att->check_in_distance_meters, 1) }} meter',
                                                                        accuracy: '{{ $att->gps_accuracy ? '± ' . round($att->gps_accuracy) . ' meter' : '-' }}',
                                                                        altitude: '{{ $att->gps_altitude ? round($att->gps_altitude, 1) . ' m' : '-' }}',
                                                                        speed: '{{ $att->gps_speed ? round($att->gps_speed, 1) . ' m/s' : '-' }}',
                                                                        is_mock: {{ $att->is_mock_location ? 'true' : 'false' }},
                                                                        is_suspicious: {{ $att->is_suspicious ? 'true' : 'false' }},
                                                                        suspicious_reason: '{{ addslashes($att->suspicious_reason ?? '') }}',
                                                                        ip_address: '{{ $att->ip_address ?? '-' }}',
                                                                        device_platform: '{{ addslashes($att->device_platform ?? '-') }}',
                                                                        device_fingerprint: '{{ $att->device_fingerprint ?? '-' }}',
                                                                        liveness_verified: {{ $att->liveness_verified ? 'true' : 'false' }},
                                                                        liveness_challenge: '{{ strtoupper($att->liveness_challenge ?? '-') }}',
                                                                        photo_in: '{{ $att->check_in_photo_url ?? '' }}',
                                                                        photo_out: '{{ $att->check_out_photo_url ?? '' }}'
                                                                    }"
                                                                    class="inline-block px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-md text-[11px] font-semibold transition">
                                                                🔍 Detail
                                                            </button>

                                                            @if(Auth::check() && (Auth::user()->hasRole('admin') || Auth::user()->isPimpinan()))
                                                                <form method="POST" action="{{ route('admin.presensi.destroy', $att->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data presensi tanggal {{ $day['date'] }} untuk {{ addslashes($peg->nama) }}?');" class="inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="text-rose-500 hover:text-rose-700 text-[11px] font-medium p-1">
                                                                        🗑️
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        @else
                                                            <span class="text-[10px] text-gray-300">-</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-400">
                            <span class="text-4xl block mb-2">🔍</span>
                            <div class="font-bold text-gray-700 text-sm">Tidak Ada Data Pegawai yang Sesuai</div>
                            <p class="text-xs text-gray-500 mt-1">Coba sesuaikan kata kunci pencarian atau ubah filter kategori kepegawaian.</p>
                        </div>
                    @endforelse
                </div>

                <!-- 5. Pagination Links -->
                @if($accordionData['paginator']->hasPages())
                    <div class="p-4 bg-white rounded-xl border border-gray-200 shadow-sm">
                        {{ $accordionData['paginator']->links() }}
                    </div>
                @endif

            @else
                <!-- ============================================================= -->
                <!-- MODE 2: MATRIKS PRESENSI BULANAN (KALENDER 1 - 31)           -->
                <!-- ============================================================= -->

                <!-- Filter Card Matriks Bulanan -->
                <div class="bg-white p-4 sm:p-5 rounded-xl border border-gray-200 shadow-sm">
                    <form method="GET" action="{{ route('admin.presensi.index') }}" class="space-y-4">
                        <input type="hidden" name="mode" value="monthly">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            <!-- Pilihan Bulan -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Bulan:</label>
                                <select name="month" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @foreach([
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ] as $mNum => $mLabel)
                                        <option value="{{ $mNum }}" {{ (int)request('month', $month ?? date('n')) === $mNum ? 'selected' : '' }}>
                                            {{ $mLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pilihan Tahun -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Tahun:</label>
                                <select name="year" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ (int)request('year', $year ?? date('Y')) === $y ? 'selected' : '' }}>
                                            {{ $y }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Pencarian Nama / NIP -->
                            <div class="lg:col-span-2">
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Cari Nama Pegawai / NIP:</label>
                                <div class="flex gap-2">
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama atau NIP..." class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-lg text-xs font-semibold transition shrink-0">
                                        Cari
                                    </button>
                                    <a href="{{ route('admin.presensi.index', ['mode' => 'monthly']) }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition shrink-0">
                                        Mengatur ulang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabel Matriks Bulanan -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-gray-800 text-sm">📅 Matriks Presensi Bulanan Pegawai ({{ $matrixData['month_name'] }})</span>
                            <span class="px-2 py-0.5 text-[11px] font-semibold bg-blue-100 text-blue-700 rounded-full">
                                Total: {{ $matrixData['paginator']->total() }} Pegawai
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-xs text-left border-collapse">
                            <thead class="bg-gray-50 text-gray-600 font-bold uppercase tracking-wider text-[11px]">
                                <tr>
                                    <th class="px-3 py-2.5 text-center sticky left-0 z-20 bg-gray-50 border-r border-gray-200" style="min-width: 40px;">No</th>
                                    <th class="px-3 py-2.5 sticky left-[40px] z-20 bg-gray-50 border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]" style="min-width: 220px;">Pegawai</th>
                                    @foreach($matrixData['days'] as $d => $dayInfo)
                                        <th class="px-1 py-1.5 text-center border-r border-gray-200 {{ $dayInfo['is_weekend'] ? 'bg-slate-100 text-slate-400' : ($dayInfo['is_today'] ? 'bg-blue-100/60 text-blue-700 font-black' : '') }}" style="min-width: 28px;">
                                            <div class="text-[11px]">{{ $d }}</div>
                                            <div class="text-[8px] font-normal uppercase opacity-75">{{ $dayInfo['day_short'][0] }}</div>
                                        </th>
                                    @endforeach
                                    <th class="px-2.5 py-2 text-center bg-gray-50 border-l border-gray-200" style="min-width: 50px;">Hadir</th>
                                    <th class="px-2.5 py-2 text-center bg-gray-50 border-r border-gray-200" style="min-width: 55px;">Telat</th>
                                    <th class="px-2.5 py-2 text-center bg-gray-50 border-r border-gray-200" style="min-width: 55px;">PSW</th>
                                    <th class="px-3 py-2 text-center bg-gray-50 border-r border-gray-200" style="min-width: 110px;">Total Durasi</th>
                                    <th class="px-3 py-2 text-center bg-gray-50" style="min-width: 110px;">Sanksi Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @forelse($matrixData['rows'] as $index => $row)
                                    <tr class="hover:bg-blue-50/40 transition">
                                        <td class="px-3 py-2 text-center font-bold text-gray-500 sticky left-0 z-10 bg-white border-r border-gray-200">
                                            {{ $matrixData['paginator']->firstItem() + $index }}
                                        </td>
                                        <td class="px-3 py-2 sticky left-[40px] z-10 bg-white border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                                            <div class="font-bold text-gray-900 truncate max-w-[200px]">{{ $row['pegawai']->nama }}</div>
                                            <div class="text-[10px] text-gray-500 font-mono">{{ $row['pegawai']->nip ?? '-' }}</div>
                                            @if($row['pegawai']->unitKerja)
                                                <div class="text-[9px] text-gray-400 truncate max-w-[200px]">{{ $row['pegawai']->unitKerja->nama_unit }}</div>
                                            @endif
                                        </td>

                                        @foreach($matrixData['days'] as $d => $dayInfo)
                                            @php
                                                $dayRecord = $row['days'][$d] ?? null;
                                            @endphp
                                            <td class="p-0.5 text-center border-r border-gray-100 {{ $dayInfo['is_weekend'] ? 'bg-slate-50/70' : ($dayInfo['is_today'] ? 'bg-blue-50/30' : '') }}">
                                                @if(($dayRecord['status'] ?? '') === 'present')
                                                    <button type="button"
                                                            @click="detailOpen = true; detailData = {
                                                                nama: '{{ addslashes($row['pegawai']->nama) }}',
                                                                tanggal: '{{ $d }} {{ $matrixData['month_name'] }}',
                                                                tipe: '{{ strtoupper($dayRecord['type'] ?? 'WFO') }}',
                                                                in: '{{ $dayRecord['in'] ?? '-' }}',
                                                                out: '{{ $dayRecord['out'] ?? '-' }}',
                                                                duration: '{{ $dayRecord['duration'] ?? '-' }}',
                                                                late_minutes: {{ $dayRecord['late_minutes'] ?? 0 }},
                                                                early_leave_minutes: {{ $dayRecord['early_leave_minutes'] ?? 0 }},
                                                                distance: '{{ number_format($dayRecord['distance'] ?? 0, 1) }} m',
                                                                photo_in: '{{ $dayRecord['photo_in'] ?? '' }}',
                                                                photo_out: '{{ $dayRecord['photo_out'] ?? '' }}',
                                                                status: 'Hadir Tepat Waktu',
                                                                status_badge: 'bg-green-100 text-green-800'
                                                            }"
                                                            title="Tgl {{ $d }}: Hadir ({{ $dayRecord['in'] ?? '-' }} s/d {{ $dayRecord['out'] ?? '-' }})"
                                                            class="w-6 h-6 rounded flex items-center justify-center font-bold text-[10px] bg-emerald-100 text-emerald-800 hover:ring-2 hover:ring-emerald-400 mx-auto transition">
                                                        H
                                                    </button>
                                                @elseif(($dayRecord['status'] ?? '') === 'late')
                                                    <button type="button"
                                                            @click="detailOpen = true; detailData = {
                                                                nama: '{{ addslashes($row['pegawai']->nama) }}',
                                                                tanggal: '{{ $d }} {{ $matrixData['month_name'] }}',
                                                                tipe: '{{ strtoupper($dayRecord['type'] ?? 'WFO') }}',
                                                                in: '{{ $dayRecord['in'] ?? '-' }}',
                                                                out: '{{ $dayRecord['out'] ?? '-' }}',
                                                                duration: '{{ $dayRecord['duration'] ?? '-' }}',
                                                                late_minutes: {{ $dayRecord['late_minutes'] ?? 0 }},
                                                                early_leave_minutes: {{ $dayRecord['early_leave_minutes'] ?? 0 }},
                                                                distance: '{{ number_format($dayRecord['distance'] ?? 0, 1) }} m',
                                                                photo_in: '{{ $dayRecord['photo_in'] ?? '' }}',
                                                                photo_out: '{{ $dayRecord['photo_out'] ?? '' }}',
                                                                status: 'Terlambat (+{{ $dayRecord['late_minutes'] ?? 0 }}m)',
                                                                status_badge: 'bg-amber-100 text-amber-800'
                                                            }"
                                                            title="Tgl {{ $d }}: Terlambat {{ $dayRecord['late_minutes'] ?? 0 }} mnt ({{ $dayRecord['in'] ?? '-' }} s/d {{ $dayRecord['out'] ?? '-' }})"
                                                            class="w-6 h-6 rounded flex items-center justify-center font-bold text-[10px] bg-amber-100 text-amber-800 hover:ring-2 hover:ring-amber-400 mx-auto transition">
                                                        T
                                                    </button>
                                                @elseif(($dayRecord['status'] ?? '') === 'absent')
                                                    <span class="w-5 h-5 flex items-center justify-center text-[10px] font-semibold text-rose-500 mx-auto" title="Tgl {{ $d }}: Tidak Hadir">
                                                        A
                                                    </span>
                                                @elseif(($dayRecord['status'] ?? '') === 'weekend')
                                                    <span class="w-5 h-5 flex items-center justify-center text-[10px] text-gray-300 mx-auto" title="Akhir Pekan">
                                                        —
                                                    </span>
                                                @else
                                                    <span class="w-5 h-5 flex items-center justify-center text-[10px] text-gray-200 mx-auto">
                                                        ·
                                                    </span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="px-2.5 py-2 text-center font-bold font-mono text-emerald-700 bg-gray-50/50 border-l border-gray-200">
                                            {{ $row['total_hadir'] }}
                                        </td>
                                        <td class="px-2.5 py-2 text-center font-bold font-mono bg-gray-50/50 border-r border-gray-200">
                                            <div class="{{ $row['total_late'] > 0 ? 'text-amber-700' : 'text-gray-400' }}">{{ $row['total_late'] }}x</div>
                                            @if($row['total_late_minutes'] > 0)
                                                <div class="text-[9px] text-amber-600 font-normal">({{ $row['total_late_minutes'] }}m)</div>
                                            @endif
                                        </td>
                                        <td class="px-2.5 py-2 text-center font-bold font-mono bg-gray-50/50 border-r border-gray-200">
                                            <div class="{{ $row['total_early_count'] > 0 ? 'text-orange-700' : 'text-gray-400' }}">{{ $row['total_early_count'] }}x</div>
                                            @if($row['total_early_minutes'] > 0)
                                                <div class="text-[9px] text-orange-600 font-normal">({{ $row['total_early_minutes'] }}m)</div>
                                            @endif
                                        </td>
                                        <td class="px-3 py-2 text-center font-bold font-mono text-slate-800 bg-gray-50/50 text-[11px] whitespace-nowrap border-r border-gray-200">
                                            {{ $row['total_duration'] }}
                                        </td>
                                        <td class="px-3 py-2 text-center font-mono bg-gray-50/50 text-[11px] whitespace-nowrap">
                                            @if($row['sanksi_hari'] > 0)
                                                <span class="px-2 py-0.5 rounded font-bold text-rose-700 bg-rose-100 border border-rose-300">
                                                    {{ $row['sanksi_hari'] }} Hari
                                                </span>
                                                <div class="text-[9px] text-slate-500 font-normal mt-0.5">Sisa {{ $row['sisa_menit_sanksi'] }} mnt</div>
                                            @elseif($row['total_violation_minutes'] > 0)
                                                <span class="text-slate-600 font-semibold">0 Hari</span>
                                                <div class="text-[9px] text-slate-400 font-normal mt-0.5">Total {{ $row['formatted_violation_time'] }}</div>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ 6 + $matrixData['days_in_month'] }}" class="px-4 py-8 text-center text-gray-400">
                                            Tidak ada data pegawai yang sesuai dengan filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Legenda Keterangan -->
                    <div class="px-5 py-3 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-gray-50/30 text-xs">
                        <div class="flex flex-wrap items-center gap-4 text-gray-600">
                            <span class="font-semibold text-gray-700">Keterangan:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-emerald-100 text-emerald-800 font-bold text-[10px] flex items-center justify-center">H</span>
                                <span>Hadir Tepat Waktu</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded bg-amber-100 text-amber-800 font-bold text-[10px] flex items-center justify-center">T</span>
                                <span>Terlambat</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded font-bold text-[10px] text-rose-500 flex items-center justify-center">A</span>
                                <span>Tidak Hadir (Alpa)</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-gray-400 font-bold">—</span>
                                <span class="text-gray-400">Akhir Pekan / Libur</span>
                            </div>
                        </div>
                    </div>

                    @if($matrixData['paginator']->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $matrixData['paginator']->links() }}
                        </div>
                    @endif
                </div>

            @endif

        </div>

        <!-- MODAL PREVIEW FOTO SELFIE UKURAN PENUH -->
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full overflow-hidden shadow-2xl"
                 @click.outside="modalOpen = false">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <h4 class="text-sm font-bold text-gray-900 truncate" x-text="modalTitle"></h4>
                    <button type="button" @click="modalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>
                <div class="p-4 flex items-center justify-center bg-gray-50">
                    <img :src="modalImgSrc" class="w-full max-h-[480px] object-contain rounded-xl shadow">
                </div>
                <div class="p-3 bg-gray-100 text-right">
                    <button type="button" @click="modalOpen = false" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL PRESENSI DARI MATRIKS BULANAN -->
        <div x-show="detailOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/75 backdrop-blur-sm flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-white rounded-2xl max-w-lg w-full overflow-hidden shadow-2xl"
                 @click.outside="detailOpen = false">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900" x-text="detailData.nama"></h4>
                        <div class="text-[11px] text-gray-500 font-medium" x-text="'Presensi: ' + detailData.tanggal + (detailData.nip && detailData.nip !== '-' ? ' | NIP: ' + detailData.nip : '')"></div>
                    </div>
                    <button type="button" @click="detailOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
                </div>
                <div class="p-4 space-y-3 text-xs bg-gray-50/50 max-h-[80vh] overflow-y-auto">
                    <!-- Status & Peringatan Integritas -->
                    <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-gray-200">
                        <span class="text-gray-500">Status Kehadiran:</span>
                        <span :class="detailData.status_badge" class="px-2.5 py-0.5 rounded-full font-bold text-[11px]" x-text="detailData.status"></span>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-white p-3 rounded-xl border border-gray-200">
                            <div class="text-[10px] text-gray-400 font-bold uppercase">Jam Masuk</div>
                            <div class="text-sm font-black text-emerald-700 mt-0.5" x-text="detailData.in || '-'"></div>
                            <div class="text-[10px] mt-1 font-semibold" :class="detailData.late_minutes > 0 ? 'text-rose-600' : 'text-emerald-600'" x-text="detailData.late_minutes > 0 ? '⚠️ Telat: ' + detailData.late_minutes + ' mnt' : '✅ Tepat Waktu (≤07:30)'"></div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-gray-200">
                            <div class="text-[10px] text-gray-400 font-bold uppercase">Jam Pulang</div>
                            <div class="text-sm font-black text-amber-700 mt-0.5" x-text="detailData.out || '-'"></div>
                            <div class="text-[10px] mt-1 font-semibold" :class="detailData.early_leave_minutes > 0 ? 'text-orange-600' : 'text-emerald-600'" x-text="detailData.early_leave_minutes > 0 ? '⚠️ PSW: ' + detailData.early_leave_minutes + ' mnt' : '✅ Jam Pulang Sah'"></div>
                        </div>
                    </div>

                    <!-- Panel Audit Integritas & Keamanan Perangkat -->
                    <div class="bg-white p-3 rounded-xl border border-gray-200 space-y-2">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-1.5">
                            <span class="text-[11px] font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                                <span>🛡️</span> Audit Keamanan Presensi
                            </span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold"
                                  :class="detailData.is_suspicious ? 'bg-rose-100 text-rose-800 border border-rose-300' : 'bg-emerald-100 text-emerald-800 border border-emerald-300'"
                                  x-text="detailData.is_suspicious ? '⚠️ ANOMALI TERDETEKSI' : '✅ VALID & AMAN'"></span>
                        </div>

                        <!-- Banner Detail Anomali Jika Ada -->
                        <template x-if="detailData.is_suspicious && detailData.suspicious_reason">
                            <div class="p-2.5 bg-rose-50 border border-rose-200 rounded-lg text-[11px] text-rose-800 space-y-1">
                                <div class="font-bold flex items-center gap-1">
                                    <span>⚠️</span> <span>Detail Pelanggaran / Anomali:</span>
                                </div>
                                <div class="font-mono text-[10px] whitespace-pre-line" x-text="detailData.suspicious_reason"></div>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div>
                                <span class="text-gray-400 block text-[10px]">Akurasi GPS Sensor:</span>
                                <span class="font-mono text-gray-800 font-semibold" x-text="detailData.accuracy || '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">Uji Keaktifan (Liveness):</span>
                                <span class="font-semibold text-emerald-700" x-text="detailData.liveness_challenge ? '✓ Lolos (' + detailData.liveness_challenge + ')' : '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">IP Address:</span>
                                <span class="font-mono text-gray-800" x-text="detailData.ip_address || '-'"></span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px]">Platform / OS:</span>
                                <span class="text-gray-800 truncate block" x-text="detailData.device_platform || '-'"></span>
                            </div>
                        </div>

                        <div class="pt-1 border-t border-gray-100 text-[10px]">
                            <span class="text-gray-400">Device Fingerprint:</span>
                            <span class="font-mono text-gray-600 block truncate" x-text="detailData.device_fingerprint || '-'"></span>
                        </div>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-gray-200 space-y-1.5">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total Jam Kerja Efektif:</span>
                            <span class="font-bold text-gray-900" x-text="detailData.duration || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tipe Presensi:</span>
                            <span class="font-semibold text-gray-800" x-text="detailData.tipe || '-'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Jarak Lokasi GPS:</span>
                            <span class="font-mono text-gray-800" x-text="detailData.distance || '-'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2" x-show="detailData.photo_in || detailData.photo_out">
                        <div x-show="detailData.photo_in" class="bg-white p-2 rounded-xl border border-gray-200 text-center">
                            <div class="text-[10px] font-bold text-gray-500 mb-1">Foto Masuk</div>
                            <img :src="detailData.photo_in" class="w-full h-28 object-cover rounded-lg shadow-sm">
                        </div>
                        <div x-show="detailData.photo_out" class="bg-white p-2 rounded-xl border border-gray-200 text-center">
                            <div class="text-[10px] font-bold text-gray-500 mb-1">Foto Pulang</div>
                            <img :src="detailData.photo_out" class="w-full h-28 object-cover rounded-lg shadow-sm">
                        </div>
                    </div>
                </div>
                <div class="p-3 bg-gray-100 text-right">
                    <button type="button" @click="detailOpen = false" class="px-4 py-1.5 bg-slate-800 text-white rounded-lg text-xs font-semibold">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
