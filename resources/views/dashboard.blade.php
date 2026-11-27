<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-5 sm:py-6">
        <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 space-y-6">

            @if(!Auth::user()->shouldShowManagerialDashboard())
                {{-- ========================================================================= --}}
                {{-- 👤 DASHBOARD MANDIRI PEGAWAI (PERSONAL INDIVIDUAL DASHBOARD)              --}}
                {{-- ========================================================================= --}}
                @php
                    $p = $myPegawai ?? Auth::user()->pegawai;
                @endphp

                @if($p)
                {{-- Banner Ucapan Selamat Datang Personal --}}
                <div class="bg-gradient-to-r from-blue-700 via-indigo-700 to-blue-900 rounded-2xl p-6 shadow-lg text-white relative overflow-hidden" style="background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 50%, #1e3a8a 100%) !important; color: #ffffff !important;">
                    <div class="absolute -right-10 -bottom-10 opacity-15 pointer-events-none">
                        <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    </div>

                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                        <div class="flex items-center gap-5">
                            @if(isset($p->foto) && $p->foto)
                                <img src="{{ $p->foto_url }}" alt="{{ $p->nama }}" class="w-20 h-24 object-cover rounded-xl border-2 border-white/40 shadow-md">
                            @else
                                <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center text-3xl font-bold border border-white/30 shadow-md" style="background-color: rgba(255, 255, 255, 0.2) !important;">
                                    👤
                                </div>
                            @endif
                            <div>
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold mb-1.5 border border-white/20" style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important;">
                                    <span>● {{ $p->status_pegawai ?? 'Aktif' }}</span>
                                    <span>•</span>
                                    <span>{{ $p->jenis_pegawai ?? 'Pegawai' }}</span>
                                </div>
                                <h1 class="text-xl md:text-2xl font-bold tracking-tight" style="color: #ffffff !important;">Selamat Datang, {{ $p->nama_lengkap ?? $p->nama ?? Auth::user()->name }}</h1>
                                <p class="text-xs md:text-sm mt-1" style="color: #dbeafe !important;">
                                    NIP: <strong class="font-mono" style="color: #ffffff !important;">{{ $p->nip ?? '-' }}</strong> | {{ $p->jabatan?->nama_jabatan ?? 'Pegawai' }} - {{ $p->unitKerja?->nama_unit ?? 'Fakultas Keperawatan' }}
                                </p>
                            </div>
                        </div>

                        {{-- Tombol Aksi Eksklusif Profil (Bebas redundansi dengan navbar atas) --}}
                        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto shrink-0">
                            @if(isset($p->id))
                                <a href="{{ route('pegawai.download-pdf', $p->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs transition shadow-md hover:bg-slate-100" style="background-color: #ffffff !important; color: #1e3a8a !important;" title="Unduh Lembar Profil Lengkap Pegawai Resmi PDF">
                                    <span>📄</span> Unduh Profil (PDF)
                                </a>
                                <a href="{{ route('pegawai.edit', $p->id) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs transition border border-white/30 hover:bg-white/30" style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important;" title="Perbarui Biodata & Berkas Pribadi">
                                    <span>✏️</span> Edit Biodata
                                </a>
                                @if(Auth::user()->canAccessAnjabAbk())
                                    <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs transition border border-white/30 hover:bg-white/30" style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important;" title="Lihat Struktur Organisasi & Peta Jabatan">
                                        <span>🏛️</span> Peta Organisasi
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ⚡ PUSAT LAYANAN CEPAT PEGAWAI MANDIRI (QUICK ACTIONS) --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
                    <a href="{{ route('layanan-surat.index') }}" class="p-3.5 rounded-2xl border border-blue-200/80 bg-white hover:bg-blue-50/50 hover:border-blue-400 shadow-2xs hover:shadow-xs transition duration-150 flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 border border-blue-200 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-blue-600 group-hover:text-white transition">
                            <x-icon name="file-text" class="w-5 h-5 stroke-[2]" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 group-hover:text-blue-700 truncate">Surat Keterangan Aktif</h4>
                            <p class="text-[11px] text-slate-500 truncate">E-Surat Sah TTE</p>
                        </div>
                    </a>

                    <a href="{{ route('pengajuan-cuti.create') }}" class="p-3.5 rounded-2xl border border-amber-200/80 bg-white hover:bg-amber-50/50 hover:border-amber-400 shadow-2xs hover:shadow-xs transition duration-150 flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center shrink-0 group-hover:bg-amber-600 group-hover:text-white transition">
                            <x-icon name="calendar-off" class="w-5 h-5 stroke-[2]" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 group-hover:text-amber-700 truncate">Ajukan Cuti Online</h4>
                            <p class="text-[11px] text-slate-500 truncate">Sisa: {{ $p->sisa_cuti_tahunan ?? 12 }} Hari</p>
                        </div>
                    </a>

                    @php
                        $isExecKepeg = Auth::user()->canAccessExecutiveKepegawaianMenus();
                        $targetKp = $isExecKepeg ? route('kp.index') : route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP']);
                        $targetKgb = $isExecKepeg ? route('kgb.index') : route('pengajuan-karir.index', ['tab' => 'kgb', 'jenis' => 'KGB']);
                        $targetSatya = $isExecKepeg ? route('satyalancana.index') : route('pengajuan-karir.index', ['tab' => 'satyalancana']);
                    @endphp

                    <a href="{{ $targetKp }}" class="p-3.5 rounded-2xl border border-purple-200/80 bg-white hover:bg-purple-50/50 hover:border-purple-400 shadow-2xs hover:shadow-xs transition duration-150 flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 border border-purple-200 text-purple-600 flex items-center justify-center shrink-0 group-hover:bg-purple-600 group-hover:text-white transition">
                            <x-icon name="award" class="w-5 h-5 stroke-[2]" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 group-hover:text-purple-700 truncate">Kenaikan Pangkat (KP)</h4>
                            <p class="text-[11px] text-slate-500 truncate">Usulan Periode</p>
                        </div>
                    </a>

                    <a href="{{ $targetKgb }}" class="p-3.5 rounded-2xl border border-teal-200/80 bg-white hover:bg-teal-50/50 hover:border-teal-400 shadow-2xs hover:shadow-xs transition duration-150 flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-teal-50 border border-teal-200 text-teal-600 flex items-center justify-center shrink-0 group-hover:bg-teal-600 group-hover:text-white transition">
                            <x-icon name="banknote" class="w-5 h-5 stroke-[2]" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 group-hover:text-teal-700 truncate">Gaji Berkala (KGB)</h4>
                            <p class="text-[11px] text-slate-500 truncate">Kenaikan 2 Tahun</p>
                        </div>
                    </a>

                    <a href="{{ $targetSatya }}" class="p-3.5 rounded-2xl border border-indigo-200/80 bg-white hover:bg-indigo-50/50 hover:border-indigo-400 shadow-2xs hover:shadow-xs transition duration-150 flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition">
                            <x-icon name="award" class="w-5 h-5 stroke-[2]" />
                        </div>
                        <div class="min-w-0">
                            <h4 class="text-xs font-bold text-slate-800 group-hover:text-indigo-700 truncate">Satyalancana</h4>
                            <p class="text-[11px] text-slate-500 truncate">Penghargaan 10/20/30</p>
                        </div>
                    </a>
                </div>

                {{-- ===================================================================== --}}
                {{-- ⚡ DAILY WORKSPACE: PRESENSI & E-LOGBOOK (GRID 2 KOLOM SEJAJAR)        --}}
                {{-- ===================================================================== --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                    {{-- 📍 KARTU STATUS PRESENSI HARI INI PEGAWAI --}}
                    @php
                        $todayAttendance = Auth::user()->todayAttendance;
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between h-full">
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-xl {{ $todayAttendance ? ($todayAttendance->check_out_time ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-blue-50 text-blue-600 border border-blue-200') : 'bg-amber-50 text-amber-600 border border-amber-200' }} flex items-center justify-center shrink-0 shadow-2xs">
                                        <x-icon name="map-pin" class="w-5 h-5 stroke-[1.75]" />
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-base text-slate-800">Presensi Hari Ini</h3>
                                        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y') }}</p>
                                    </div>
                                </div>
                                <span class="text-xs px-2.5 py-1 rounded-full font-bold {{ $todayAttendance ? ($todayAttendance->status === 'late' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200') : 'bg-rose-100 text-rose-700 border border-rose-200' }}">
                                    {{ $todayAttendance ? ($todayAttendance->status === 'late' ? 'Terlambat' : 'Hadir Tepat Waktu') : 'Belum Presensi' }}
                                </span>
                            </div>

                            <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 grid grid-cols-2 gap-3 text-xs mb-4">
                                <div>
                                    <span class="text-slate-400 block text-[11px] font-semibold uppercase">Jam Masuk</span>
                                    <strong class="text-slate-800 font-mono text-sm">{{ $todayAttendance && $todayAttendance->check_in_time ? $todayAttendance->check_in_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : '-' }}</strong>
                                </div>
                                <div>
                                    <span class="text-slate-400 block text-[11px] font-semibold uppercase">Jam Pulang</span>
                                    <strong class="text-slate-800 font-mono text-sm">{{ $todayAttendance && $todayAttendance->check_out_time ? $todayAttendance->check_out_time->timezone('Asia/Jakarta')->format('H:i') . ' WIB' : ($todayAttendance ? 'Belum Check-Out' : '-') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2.5 pt-2 border-t border-slate-100">
                            <a href="{{ route('presensi.index') }}" class="flex-1 px-4 py-2.5 rounded-xl font-bold text-xs text-white transition shadow-sm flex items-center justify-center gap-2 {{ !$todayAttendance ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : (!$todayAttendance->check_out_time ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20' : 'bg-slate-800 hover:bg-slate-700') }}">
                                @if(!$todayAttendance)
                                    <x-icon name="camera" class="w-4 h-4 stroke-[2]" />
                                    <span>Presensi Masuk Sekarang</span>
                                @elseif(!$todayAttendance->check_out_time)
                                    <x-icon name="log-out" class="w-4 h-4 stroke-[2]" />
                                    <span>Presensi Pulang (Check-Out)</span>
                                @else
                                    <x-icon name="check-circle" class="w-4 h-4 stroke-[2]" />
                                    <span>Buka Menu Presensi</span>
                                @endif
                            </a>
                            <a href="{{ route('presensi.history') }}" class="px-3.5 py-2.5 rounded-xl font-semibold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 transition shrink-0">
                                Riwayat
                            </a>
                        </div>
                    </div>

                    {{-- 📝 KARTU CAPAIAN E-LOGBOOK KINERJA BULAN INI --}}
                    @if(isset($myLogbookStats))
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between h-full">
                            <div>
                                <div class="flex items-center justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center shrink-0 shadow-2xs">
                                            <x-icon name="clipboard-check" class="w-5 h-5 stroke-[1.75]" />
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-base text-slate-800">E-Logbook Kinerja</h3>
                                            <p class="text-xs text-slate-500">Bulan: {{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('MMMM Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $myLogbookStats['total_aktivitas'] }} Aktivitas
                                    </span>
                                </div>

                                <div class="bg-slate-50 rounded-xl p-3.5 border border-slate-100 grid grid-cols-3 gap-2 text-center text-xs mb-4">
                                    <div>
                                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Total Jam</span>
                                        <strong class="text-slate-800 text-sm font-bold">{{ $myLogbookStats['total_jam'] }}j</strong>
                                        <span class="text-[10px] text-slate-400">({{ $myLogbookStats['total_menit'] }}m)</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Disetujui</span>
                                        <strong class="text-emerald-600 text-sm font-bold">{{ $myLogbookStats['disetujui'] }}</strong>
                                        <span class="text-[10px] text-emerald-500">Aktivitas</span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[10px] font-bold uppercase">Menunggu</span>
                                        <strong class="text-amber-600 text-sm font-bold">{{ $myLogbookStats['diajukan'] }}</strong>
                                        @if($myLogbookStats['perlu_revisi'] > 0)
                                            <span class="text-[10px] text-rose-600 font-bold block animate-pulse">Revisi: {{ $myLogbookStats['perlu_revisi'] }}</span>
                                        @else
                                            <span class="text-[10px] text-slate-400">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2.5 pt-2 border-t border-slate-100">
                                <a href="{{ route('logbook.create') }}" class="flex-1 px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-700 shadow-sm transition flex items-center justify-center gap-1.5">
                                    <x-icon name="plus" class="w-4 h-4 stroke-[2]" />
                                    <span>Catat Aktivitas</span>
                                </a>
                                <a href="{{ route('logbook.index') }}" class="px-3.5 py-2.5 rounded-xl font-semibold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 transition shrink-0">
                                    Buka Logbook
                                </a>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- 📑 PUSAT PELACAKAN STATUS USULAN MANDIRI PEGAWAI (STEPPER 4 TAHAP) --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6" x-data="{ activeTab: 'all' }">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b border-slate-100 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center shadow-2xs shrink-0">
                                <x-icon name="check-circle" class="w-5 h-5 stroke-[2]" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-bold text-base text-slate-900">Pelacakan Berkas &amp; Progres Usulan Mandiri</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase">
                                        Live Stepper
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">Monitoring real-time perjalanan dokumen Anda melalui SOP 4 Tahap Pengesahan Resmi.</p>
                            </div>
                        </div>

                        {{-- Tab Filter Alpine.js --}}
                        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs overflow-x-auto">
                            <button type="button" @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                Semua Berkas
                            </button>
                            <button type="button" @click="activeTab = 'surat'" :class="activeTab === 'surat' ? 'bg-white text-blue-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                Surat Aktif
                            </button>
                            <button type="button" @click="activeTab = 'karir'" :class="activeTab === 'karir' ? 'bg-white text-purple-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                KP &amp; KGB
                            </button>
                            <button type="button" @click="activeTab = 'satya'" :class="activeTab === 'satya' ? 'bg-white text-indigo-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                Satyalancana
                            </button>
                            <button type="button" @click="activeTab = 'cuti'" :class="activeTab === 'cuti' ? 'bg-white text-amber-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 font-medium'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                                Cuti
                            </button>
                        </div>
                    </div>

                    {{-- Daftar Berkas Aktif --}}
                    @php
                        $hasAnyUsulan = (isset($myLayananSurat) && $myLayananSurat->isNotEmpty())
                            || (isset($myPengajuanKarir) && $myPengajuanKarir->isNotEmpty())
                            || (isset($mySatyalancana) && $mySatyalancana->isNotEmpty())
                            || (isset($myCuti) && $myCuti->isNotEmpty());
                    @endphp

                    @if($hasAnyUsulan)
                        <div class="space-y-3.5">
                            {{-- 1. Surat Keterangan Aktif --}}
                            @if(isset($myLayananSurat))
                                @foreach($myLayananSurat as $surat)
                                    <div x-show="activeTab === 'all' || activeTab === 'surat'" class="p-4 rounded-xl border border-slate-200/90 bg-slate-50/50 hover:bg-white hover:border-blue-300 hover:shadow-xs transition duration-150">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase">
                                                    📄 {{ $surat->jenis_surat ?? 'Surat Keterangan Aktif Bekerja' }}
                                                </span>
                                                @if($surat->nomor_surat)
                                                    <span class="text-xs font-mono font-bold text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">
                                                        No: {{ $surat->nomor_surat }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                                        ⏳ Menunggu Penomoran Admin
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-[11px] text-slate-400">Diajukan: {{ \Carbon\Carbon::parse($surat->tanggal_surat ?? $surat->created_at)->format('d-m-Y') }}</span>
                                                @if($surat->status === 'selesai')
                                                    <a href="{{ route('layanan-surat.surat-aktif.cetak', $surat->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                                        <span>📥</span> Unduh Dokumen (PDF)
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        <p class="text-xs text-slate-600 mb-3">
                                            Keperluan: <strong class="text-slate-800">{{ $surat->keperluan }}</strong>
                                            @if($surat->penandatangan_jabatan)
                                                <span class="text-slate-400">• Penandatangan: {{ $surat->penandatangan_jabatan }}</span>
                                            @endif
                                        </p>

                                        {{-- 4-Stage Stepper Bar --}}
                                        <div class="grid grid-cols-4 gap-2 pt-2 border-t border-slate-200/60 text-center">
                                            <div class="p-2 rounded-lg {{ $surat->verifikasi_admin_at ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $surat->verifikasi_admin_at ? '✓' : '1' }} Screening Admin
                                            </div>
                                            <div class="p-2 rounded-lg {{ $surat->paraf_kapokja_at ? 'bg-sky-50 text-sky-800 border border-sky-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $surat->paraf_kapokja_at ? '✓' : '2' }} Paraf Pokja
                                            </div>
                                            <div class="p-2 rounded-lg {{ $surat->paraf_kabag_at ? 'bg-purple-50 text-purple-800 border border-purple-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $surat->paraf_kabag_at ? '✓' : '3' }} Paraf Kabag
                                            </div>
                                            <div class="p-2 rounded-lg {{ $surat->status === 'selesai' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $surat->status === 'selesai' ? '✓' : '4' }} TTE Sah
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- 2. Kenaikan Pangkat & KGB --}}
                            @if(isset($myPengajuanKarir))
                                @foreach($myPengajuanKarir as $karir)
                                    <div x-show="activeTab === 'all' || activeTab === 'karir'" class="p-4 rounded-xl border border-slate-200/90 bg-slate-50/50 hover:bg-white hover:border-purple-300 hover:shadow-xs transition duration-150">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold {{ $karir->jenis_pengajuan === 'kp' ? 'bg-purple-100 text-purple-800 border border-purple-200' : 'bg-teal-100 text-teal-800 border border-teal-200' }} uppercase">
                                                    {{ $karir->jenis_pengajuan === 'kp' ? '🎖️ Kenaikan Pangkat' : '💵 Gaji Berkala (KGB)' }}
                                                </span>
                                                @if($karir->nomor_surat)
                                                    <span class="text-xs font-mono font-bold text-slate-800 bg-white px-2 py-0.5 rounded border border-slate-200">
                                                        No: {{ $karir->nomor_surat }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-slate-400">Periode: {{ $karir->periode_kp ?? $karir->tahun_periode ?? '-' }}</span>
                                        </div>

                                        <p class="text-xs text-slate-600 mb-3">
                                            @if($karir->jenis_pengajuan === 'kp')
                                                Tujuan Golongan: <strong class="text-slate-800">{{ $karir->golonganTujuan?->nama_golongan ?? '-' }}</strong> (TMT: {{ $karir->tmt_baru ? \Carbon\Carbon::parse($karir->tmt_baru)->format('d-m-Y') : '-' }})
                                            @else
                                                Gaji Pokok Baru: <strong class="text-slate-800 font-mono">Rp {{ number_format($karir->gaji_pokok_baru ?? 0, 0, ',', '.') }}</strong> (TMT: {{ $karir->tmt_baru ? \Carbon\Carbon::parse($karir->tmt_baru)->format('d-m-Y') : '-' }})
                                            @endif
                                        </p>

                                        {{-- 4-Stage Stepper Bar --}}
                                        <div class="grid grid-cols-4 gap-2 pt-2 border-t border-slate-200/60 text-center">
                                            <div class="p-2 rounded-lg {{ $karir->verifikasi_admin_at ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $karir->verifikasi_admin_at ? '✓' : '1' }} Screening Admin
                                            </div>
                                            <div class="p-2 rounded-lg {{ $karir->paraf_kapokja_at ? 'bg-sky-50 text-sky-800 border border-sky-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $karir->paraf_kapokja_at ? '✓' : '2' }} Paraf Pokja
                                            </div>
                                            <div class="p-2 rounded-lg {{ $karir->paraf_kabag_at ? 'bg-purple-50 text-purple-800 border border-purple-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $karir->paraf_kabag_at ? '✓' : '3' }} Paraf Kabag
                                            </div>
                                            <div class="p-2 rounded-lg {{ ($karir->paraf_wd2_at || in_array($karir->status, ['disetujui_wd2', 'disetujui_dekan', 'selesai'])) ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ ($karir->paraf_wd2_at || in_array($karir->status, ['disetujui_wd2', 'disetujui_dekan', 'selesai'])) ? '✓' : '4' }} Disetujui WD II
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- 3. Satyalancana --}}
                            @if(isset($mySatyalancana))
                                @foreach($mySatyalancana as $satya)
                                    <div x-show="activeTab === 'all' || activeTab === 'satya'" class="p-4 rounded-xl border border-slate-200/90 bg-slate-50/50 hover:bg-white hover:border-indigo-300 hover:shadow-xs transition duration-150">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase">
                                                    🏅 Satyalancana Karya Satya
                                                </span>
                                                <span class="text-xs font-semibold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">
                                                    Kategori: {{ $satya->kategori ?? '10 Tahun' }}
                                                </span>
                                            </div>
                                            <span class="text-[11px] text-slate-400">Tahun: {{ $satya->tahun_usulan ?? date('Y') }}</span>
                                        </div>

                                        {{-- 4-Stage Stepper Bar --}}
                                        <div class="grid grid-cols-4 gap-2 pt-2 border-t border-slate-200/60 text-center">
                                            <div class="p-2 rounded-lg {{ $satya->verifikasi_admin_at ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $satya->verifikasi_admin_at ? '✓' : '1' }} Screening Admin
                                            </div>
                                            <div class="p-2 rounded-lg {{ $satya->paraf_kapokja_at ? 'bg-sky-50 text-sky-800 border border-sky-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $satya->paraf_kapokja_at ? '✓' : '2' }} Paraf Pokja
                                            </div>
                                            <div class="p-2 rounded-lg {{ $satya->paraf_kabag_at ? 'bg-purple-50 text-purple-800 border border-purple-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $satya->paraf_kabag_at ? '✓' : '3' }} Paraf Kabag
                                            </div>
                                            <div class="p-2 rounded-lg {{ ($satya->ttd_wd2_at || in_array($satya->status, ['disetujui_wd2', 'selesai'])) ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ ($satya->ttd_wd2_at || in_array($satya->status, ['disetujui_wd2', 'selesai'])) ? '✓' : '4' }} Disetujui WD II
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            {{-- 4. Pengajuan Cuti --}}
                            @if(isset($myCuti))
                                @foreach($myCuti as $cuti)
                                    <div x-show="activeTab === 'all' || activeTab === 'cuti'" class="p-4 rounded-xl border border-slate-200/90 bg-slate-50/50 hover:bg-white hover:border-amber-300 hover:shadow-xs transition duration-150">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200 uppercase">
                                                    🏖️ {{ $cuti->jenis_cuti }}
                                                </span>
                                                <span class="text-xs font-semibold text-slate-700 bg-white px-2 py-0.5 rounded border border-slate-200">
                                                    Durasi: {{ $cuti->jumlah_hari }} Hari Kerja
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <span class="text-[11px] text-slate-400 font-mono">{{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d-m-Y') }} s.d {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d-m-Y') }}</span>
                                                @if($cuti->status === 'Disetujui')
                                                    <a href="{{ route('pengajuan-cuti.cetak-pdf', $cuti->id) }}" target="_blank" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow-2xs">
                                                        <span>🖨️</span> Cetak Formulir BKN
                                                    </a>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- 3-Stage Cuti Stepper Bar --}}
                                        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-200/60 text-center">
                                            <div class="p-2 rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold text-[11px]">
                                                ✓ Diajukan Pemohon
                                            </div>
                                            <div class="p-2 rounded-lg {{ $cuti->pertimbangan_atasan_at ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }} text-[11px]">
                                                {{ $cuti->pertimbangan_atasan_at ? '✓' : '2' }} Pertimbangan Atasan
                                            </div>
                                            <div class="p-2 rounded-lg {{ $cuti->status === 'Disetujui' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : ($cuti->status === 'Ditolak' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-400') }} text-[11px]">
                                                {{ $cuti->status === 'Disetujui' ? '✓ Disetujui PYBMC' : ($cuti->status === 'Ditolak' ? '✕ Ditolak' : '3 Keputusan PYBMC') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <span class="text-3xl block mb-2">📋</span>
                            <p class="font-semibold text-slate-600">Belum ada usulan aktif atau permohonan surat yang sedang berjalan.</p>
                            <p class="text-slate-400 mt-1">Gunakan tombol pintasan cepat di atas untuk mengajukan Surat Keterangan Aktif, Cuti, KP, atau KGB.</p>
                        </div>
                    @endif
                </div>

                {{-- PUSAT TINDAKAN VERIFIKASI ATASAN LANGSUNG (JIKA PEGAWAI ADALAH ATASAN) --}}
                @if(isset($isAtasan) && $isAtasan)
                    <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-5 text-white shadow-md">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-blue-800 pb-3 mb-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center text-white">
                                    <x-icon name="users" class="w-5 h-5 stroke-[1.75]" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-base text-white">Meja Verifikasi Atasan Langsung</h3>
                                    <p class="text-xs text-blue-200">Pengawasan dan pengesahan aktivitas logbook harian serta cuti pegawai di bawah tanggung jawab Anda.</p>
                                </div>
                            </div>
                        </div>

                        <x-enterprise.stat-grid :cols="2">
                            <x-enterprise.stat-card
                                title="Logbook Bawahan Menunggu"
                                :value="$pendingLogbookCount ?? 0"
                                icon="clipboard-check"
                                color="indigo"
                                subtitle="Periksa & Berikan Pengesahan →"
                                :href="route('admin.logbook.index', ['status' => 'diajukan'])"
                            />

                            <x-enterprise.stat-card
                                title="Permohonan Cuti Bawahan"
                                :value="$pendingCutiCount ?? 0"
                                icon="calendar-off"
                                color="amber"
                                subtitle="Tinjau & Berikan Keputusan →"
                                :href="route('pengajuan-cuti.index', ['status' => 'Menunggu Persetujuan'])"
                            />
                        </x-enterprise.stat-grid>
                    </div>
                @endif

                {{-- KARTU PERSENTASE KELENGKAPAN DATA PEGAWAI MANDIRI --}}
                @if(isset($completenessData))
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <x-icon name="chart" class="w-5 h-5 text-indigo-600 stroke-[1.75]" />
                                    <h3 class="font-bold text-base text-slate-800">Persentase Kelengkapan Data Profil Saya</h3>
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $completenessData['badge_color'] }}">
                                        {{ $completenessData['status_label'] }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    Telah memenuhi {{ $completenessData['completed_count'] }} dari {{ $completenessData['total_count'] }} indikator kelengkapan data & berkas kepegawaian.
                                </p>
                            </div>
                            <div class="text-right w-full md:w-auto shrink-0">
                                <span class="text-3xl font-black text-slate-900">{{ $completenessData['score'] }}%</span>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="w-full bg-slate-100 rounded-full h-3.5 p-0.5 overflow-hidden border border-slate-200 mb-4">
                            <div class="{{ $completenessData['progress_color'] }} h-2.5 rounded-full transition-all duration-500 shadow-sm" style="width: {{ $completenessData['score'] }}%"></div>
                        </div>

                        {{-- Checklist Panduan Item yang Belum Dilengkapi --}}
                        @if(count($completenessData['missing_items']) > 0)
                            <div class="bg-amber-50/70 border border-amber-200/80 rounded-xl p-4">
                                <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                                    <x-icon name="bell" class="w-4 h-4 text-amber-700 stroke-[2]" />
                                    Harap Lengkapi {{ count($completenessData['missing_items']) }} Item Berikut Agar Data Anda 100% Terverifikasi:
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                    @foreach($completenessData['missing_items'] as $item)
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-amber-200/60 shadow-2xs">
                                            <div class="flex items-center gap-2">
                                                <span class="text-rose-500 font-bold text-sm leading-none">✕</span>
                                                <span class="text-xs font-medium text-slate-700">{{ $item['label'] }}</span>
                                            </div>
                                            <a href="{{ $item['action_url'] }}" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded-md text-[11px] font-bold transition shrink-0">
                                                {{ $item['action_label'] }} &rarr;
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-emerald-900 flex items-center gap-3">
                                <x-icon name="check-circle" class="w-6 h-6 text-emerald-600 stroke-[2] shrink-0" />
                                <div>
                                    <h4 class="font-bold text-xs">Selamat! Data Profil Anda Sudah 100% Lengkap.</h4>
                                    <p class="text-[11px] text-emerald-700 mt-0.5">Seluruh berkas SK, identitas, dan riwayat karir Anda telah memenuhi standar kelengkapan dokumen SIMPEG.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- 4 KARTU STATISTIK PROFIL SAYA --}}
                <x-enterprise.stat-grid :cols="4">
                    {{-- Kartu 1: Status Kepegawaian --}}
                    <x-enterprise.stat-card
                        title="Status Kepegawaian"
                        :value="$p->jenis_pegawai ?? 'Pegawai'"
                        icon="user-check"
                        color="blue"
                        :subtitle="($p->status_asn ?? 'ASN') . ' (' . ($p->status_pegawai ?? 'Aktif') . ')'"
                    />

                    {{-- Kartu 2: Jabatan Aktif --}}
                    <x-enterprise.stat-card
                        title="Jabatan Terkini"
                        :value="$p->jabatan?->nama_jabatan ?? '-'"
                        icon="briefcase"
                        color="emerald"
                        :subtitle="$p->unitKerja?->nama_unit ?? '-'"
                    />

                    {{-- Kartu 3: Pangkat / Golongan --}}
                    <x-enterprise.stat-card
                        title="Pangkat / Golongan"
                        :value="$p->golongan?->nama_golongan ?? '-'"
                        icon="award"
                        color="amber"
                        :subtitle="'TMT: ' . (isset($p->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($p->tmt_pangkat_terakhir)->format('d-m-Y') : '-')"
                    />

                    {{-- Kartu 4: Sisa Cuti Tahunan --}}
                    <x-enterprise.stat-card
                        title="Sisa Cuti Tahunan"
                        :value="$p->sisa_cuti_tahunan ?? 12"
                        suffix="Hari"
                        icon="calendar-off"
                        color="indigo"
                        :subtitle="'Kuota Tahun ' . date('Y')"
                    />
                </x-enterprise.stat-grid>

                {{-- 🎯 STATUS PEMETAAN TALENTA ASN SAYA (PERMENPAN-RB NO. 3/2020) --}}
                @if(isset($myTalentMapping) && $myTalentMapping)
                    @php
                        $boxNum = $myTalentMapping->kuadran_box;
                        $isHigh = in_array($boxNum, [7, 8, 9]);
                        $isMid  = in_array($boxNum, [4, 5, 6]);
                        $themeBg = $isHigh ? 'bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-100/50 border-emerald-200' : ($isMid ? 'bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-100/50 border-blue-200' : 'bg-gradient-to-r from-amber-50 via-orange-50 to-amber-100/50 border-amber-200');
                        $badgeBg = $isHigh ? 'bg-emerald-600 text-white' : ($isMid ? 'bg-blue-600 text-white' : 'bg-amber-600 text-white');
                    @endphp
                    <div class="rounded-2xl border p-5 sm:p-6 shadow-sm {{ $themeBg }}">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-200/60 pb-4 mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl {{ $isHigh ? 'bg-emerald-600 text-white' : ($isMid ? 'bg-blue-600 text-white' : 'bg-amber-600 text-white') }} flex items-center justify-center shadow-sm shrink-0">
                                    <x-icon name="target" class="w-6 h-6 stroke-[2]" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-bold text-base text-slate-800">Status Pemetaan Talenta ASN Saya</h3>
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $badgeBg }}">
                                            Tahun {{ $myTalentMapping->tahun }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5">Berdasarkan Matriks Kuadran 9-Kotak PermenPAN-RB No. 3/2020 & UU No. 20/2023</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('manajemen-talenta.my-talent') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition shadow-sm">
                                    <x-icon name="clipboard-list" class="w-4 h-4 stroke-[2]" />
                                    <span>Buka Profil & Rekam Asesmen Lengkap →</span>
                                </a>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Kuadran Talenta --}}
                            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/70 shadow-2xs">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Posisi Kuadran 9-Kotak</span>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xl font-black text-slate-900">Kotak {{ $myTalentMapping->kuadran_box }}</span>
                                    @if($myTalentMapping->is_suksesi_eligible)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1">
                                            <x-icon name="crown" class="w-3 h-3 stroke-[2] text-amber-600" />
                                            Talent Pool Suksesi
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs font-semibold text-slate-700 mt-1 line-clamp-1">{{ $myTalentMapping->box_name }}</p>
                            </div>

                            {{-- Sumbu Kinerja & Potensi --}}
                            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/70 shadow-2xs">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Nilai Sumbu Evaluasi</span>
                                <div class="grid grid-cols-2 gap-2 mt-1">
                                    <div>
                                        <div class="text-[10px] text-slate-500 font-semibold">Kinerja (X):</div>
                                        <div class="text-base font-black text-blue-700">{{ number_format($myTalentMapping->sumbu_kinerja_nilai, 1) }} <span class="text-[10px] font-bold text-slate-500">({{ $myTalentMapping->sumbu_kinerja_kategori }})</span></div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] text-slate-500 font-semibold">Potensi (Y):</div>
                                        <div class="text-base font-black text-indigo-700">{{ number_format($myTalentMapping->sumbu_potensi_nilai, 1) }} <span class="text-[10px] font-bold text-slate-500">({{ $myTalentMapping->sumbu_potensi_kategori }})</span></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Rekomendasi Karier --}}
                            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/70 shadow-2xs flex flex-col justify-between">
                                <div>
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Rekomendasi Kebijakan Karier</span>
                                    <p class="text-xs text-slate-700 mt-1 line-clamp-2" title="{{ $myTalentMapping->rekomendasi_kebijakan }}">
                                        {{ $myTalentMapping->rekomendasi_kebijakan ?? 'Pengembangan kompetensi berkala sesuai kebutuhan jabatan.' }}
                                    </p>
                                </div>
                                <div class="text-[10px] text-slate-500 mt-1">
                                    Status: <span class="font-bold text-emerald-700">{{ $myTalentMapping->status_talenta }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 🔔 PUSAT REMINDER KARIR PRIBADI SAYA 🔔 --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                        <span class="text-xl">🔔</span>
                        <h3 class="font-bold text-base text-slate-800">Pusat Reminder & Target Karir Pribadi Saya</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        {{-- KGB Saya --}}
                        <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/60 flex flex-col justify-between hover:shadow-md transition duration-150">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-amber-900">💵 Gaji Berkala (KGB)</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-200 text-amber-900">2 Tahun</span>
                                </div>
                                <div class="text-xs space-y-1 text-slate-700">
                                    <p>TMT Terakhir: <strong class="font-mono text-slate-900">{{ isset($p->tmt_kgb_terakhir) ? \Carbon\Carbon::parse($p->tmt_kgb_terakhir)->format('d-m-Y') : '-' }}</strong></p>
                                    <p>Target KGB: <strong class="font-mono text-amber-800">{{ isset($p->kgb_berikutnya) ? \Carbon\Carbon::parse($p->kgb_berikutnya)->format('d-m-Y') : '-' }}</strong></p>
                                </div>
                            </div>
                            <div>
                                @if(isset($p->kgb_berikutnya))
                                    @php
                                        $diffKgb = (int) round(\Carbon\Carbon::now()->diffInMonths(\Carbon\Carbon::parse($p->kgb_berikutnya), false));
                                    @endphp
                                    @if($diffKgb <= 0)
                                        <a href="{{ auth()->user()->hasRole('admin') ? route('kgb.index') : route('pegawai.show', $p->id) }}" 
                                           class="mt-3 block w-full text-[11px] font-bold text-white bg-amber-600 hover:bg-amber-700 py-2 px-2.5 rounded-lg text-center transition shadow-xs">
                                            ⚠️ Jatuh Tempo KGB! Ajukan &rarr;
                                        </a>
                                    @else
                                        @php
                                            $thnKgb = floor($diffKgb / 12);
                                            $blnKgb = $diffKgb % 12;
                                            $labelKgb = $thnKgb > 0 ? "{$thnKgb} Thn " . ($blnKgb > 0 ? "{$blnKgb} Bln" : "") : "{$blnKgb} Bulan";
                                        @endphp
                                        <a href="{{ auth()->user()->hasRole('admin') ? route('kgb.index') : route('pegawai.show', $p->id) }}" 
                                           class="mt-3 block w-full text-[11px] font-semibold text-amber-800 bg-amber-100 hover:bg-amber-200 py-2 px-2.5 rounded-lg text-center transition">
                                            ⏳ Est. {{ trim($labelKgb) }} (Lihat Detail) &rarr;
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('pegawai.edit', ['pegawai' => $p->id]) }}#section-administrasi" 
                                       class="mt-3 block w-full text-[11px] font-bold text-amber-900 bg-amber-200/90 hover:bg-amber-300 py-2 px-2.5 rounded-lg text-center transition shadow-xs flex items-center justify-center gap-1">
                                        <span>✏️</span> Lengkapi SK KGB &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- KP Saya --}}
                        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 flex flex-col justify-between hover:shadow-md transition duration-150">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">🎖️ Kenaikan Pangkat (KP)</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-200 text-emerald-900">4 Tahun</span>
                                </div>
                                <div class="text-xs space-y-1 text-slate-700">
                                    <p>TMT Terakhir: <strong class="font-mono text-slate-900">{{ isset($p->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($p->tmt_pangkat_terakhir)->format('d-m-Y') : '-' }}</strong></p>
                                    <p>Target KP: <strong class="font-mono text-emerald-800">{{ isset($p->kp_berikutnya) ? \Carbon\Carbon::parse($p->kp_berikutnya)->format('d-m-Y') : '-' }}</strong></p>
                                </div>
                            </div>
                            <div>
                                @if(isset($p->kp_berikutnya))
                                    @php
                                        $diffKp = (int) round(\Carbon\Carbon::now()->diffInMonths(\Carbon\Carbon::parse($p->kp_berikutnya), false));
                                    @endphp
                                    @if($diffKp <= 0)
                                        <a href="{{ auth()->user()->hasRole('admin') ? route('kp.index') : route('pegawai.show', $p->id) }}" 
                                           class="mt-3 block w-full text-[11px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 py-2 px-2.5 rounded-lg text-center transition shadow-xs">
                                            ⚠️ Siap Naik Pangkat! Ajukan &rarr;
                                        </a>
                                    @else
                                        @php
                                            $thnKp = floor($diffKp / 12);
                                            $blnKp = $diffKp % 12;
                                            $labelKp = $thnKp > 0 ? "{$thnKp} Thn " . ($blnKp > 0 ? "{$blnKp} Bln" : "") : "{$blnKp} Bulan";
                                        @endphp
                                        <a href="{{ auth()->user()->hasRole('admin') ? route('kp.index') : route('pegawai.show', $p->id) }}" 
                                           class="mt-3 block w-full text-[11px] font-semibold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 py-2 px-2.5 rounded-lg text-center transition">
                                            ⏳ Est. {{ trim($labelKp) }} (Lihat Detail) &rarr;
                                        </a>
                                    @endif
                                @else
                                    <a href="{{ route('pegawai.edit', ['pegawai' => $p->id]) }}#section-administrasi" 
                                       class="mt-3 block w-full text-[11px] font-bold text-emerald-900 bg-emerald-200/90 hover:bg-emerald-300 py-2 px-2.5 rounded-lg text-center transition shadow-xs flex items-center justify-center gap-1">
                                        <span>✏️</span> Lengkapi SK Pangkat &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Satyalancana Saya --}}
                        <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/60 flex flex-col justify-between hover:shadow-md transition duration-150">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-indigo-900">🏅 Satyalancana</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-indigo-200 text-indigo-900">10/20/30 Thn</span>
                                </div>
                                <div class="text-xs space-y-1 text-slate-700">
                                    <p>Terakhir: <strong class="text-slate-900">{{ $p->satyalancana_terakhir ?? '-' }}</strong></p>
                                    <p>Prediksi: <strong class="font-mono text-indigo-800">{{ isset($p->satyalancana_berikutnya) ? \Carbon\Carbon::parse($p->satyalancana_berikutnya)->format('d-m-Y') : '-' }}</strong></p>
                                </div>
                            </div>
                            <div>
                                @if(empty($p->satyalancana_berikutnya) && empty($p->tanggal_masuk) && empty($p->tmt_sk_pertama))
                                    <a href="{{ route('pegawai.edit', ['pegawai' => $p->id]) }}#section-administrasi" 
                                       class="mt-3 block w-full text-[11px] font-bold text-indigo-900 bg-indigo-200/90 hover:bg-indigo-300 py-2 px-2.5 rounded-lg text-center transition shadow-xs flex items-center justify-center gap-1">
                                        <span>✏️</span> Atur TMT SK Pertama &rarr;
                                    </a>
                                @else
                                    <a href="{{ route('pegawai.show', $p->id) }}" 
                                       class="mt-3 block w-full text-[11px] font-semibold text-indigo-800 bg-indigo-100 hover:bg-indigo-200 py-2 px-2.5 rounded-lg text-center transition flex items-center justify-center gap-1">
                                        <span>🎖️</span> Riwayat Penghargaan &rarr;
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- KARTU KE-4: KONDISIONAL STR/SIP ATAU MASA KONTRAK PPPK ATAU SKP --}}
                        @php
                            $activeStr = (isset($p->riwayatStrSip) && $p->riwayatStrSip->count() > 0) ? $p->riwayatStrSip->first() : null;
                            $isPppkOrContract = ($p->jenis_pegawai === 'PPPK' || $p->jenis_pegawai === 'PHL' || !empty($p->tanggal_kontrak_selesai));
                        @endphp

                        @if($activeStr)
                            {{-- 1. STR & SIP (Khusus Tenaga Medis / Dosen Klinis) --}}
                            <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/60 flex flex-col justify-between hover:shadow-md transition duration-150">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-sky-900">🩺 STR & SIP Profesi</span>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-sky-200 text-sky-900">Legalitas</span>
                                    </div>
                                    <div class="text-xs space-y-1 text-slate-700">
                                        <p>Jenis: <strong class="text-slate-900">{{ $activeStr->jenis_dokumen ?? 'STR/SIP' }}</strong></p>
                                        <p>Berakhir: <strong class="font-mono text-sky-800">
                                            {{ $activeStr->is_seumur_hidup ? 'Seumur Hidup' : (isset($activeStr->tanggal_berakhir) ? \Carbon\Carbon::parse($activeStr->tanggal_berakhir)->format('d-m-Y') : '-') }}
                                        </strong></p>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('pegawai.show', $p->id) }}" 
                                       class="mt-3 block w-full text-[11px] font-semibold text-sky-800 bg-sky-100 hover:bg-sky-200 py-2 px-2.5 rounded-lg text-center transition flex items-center justify-center gap-1">
                                        <span>🩺</span> Cek STR / SIP Profesi &rarr;
                                    </a>
                                </div>
                            </div>
                        @elseif($isPppkOrContract)
                            {{-- 2. Kontrak Kerja PPPK / PHL --}}
                            <div class="p-4 rounded-xl border border-cyan-200 bg-cyan-50/60 flex flex-col justify-between hover:shadow-md transition duration-150">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-cyan-900">📋 Kontrak Kerja {{ $p->jenis_pegawai ?? 'PPPK' }}</span>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-cyan-200 text-cyan-900">Masa Kerja</span>
                                    </div>
                                    <div class="text-xs space-y-1 text-slate-700">
                                        <p>TMT Mulai: <strong class="font-mono text-slate-900">{{ isset($p->tanggal_kontrak_mulai) ? \Carbon\Carbon::parse($p->tanggal_kontrak_mulai)->format('d-m-Y') : (isset($p->tanggal_masuk) ? \Carbon\Carbon::parse($p->tanggal_masuk)->format('d-m-Y') : '-') }}</strong></p>
                                        <p>Selesai: <strong class="font-mono text-cyan-800">{{ isset($p->tanggal_kontrak_selesai) ? \Carbon\Carbon::parse($p->tanggal_kontrak_selesai)->format('d-m-Y') : '-' }}</strong></p>
                                    </div>
                                </div>
                                <div>
                                    @if(isset($p->tanggal_kontrak_selesai))
                                        @php
                                            $diffKontrak = (int) round(\Carbon\Carbon::now()->diffInMonths(\Carbon\Carbon::parse($p->tanggal_kontrak_selesai), false));
                                        @endphp
                                        <a href="{{ route('pegawai.show', $p->id) }}" 
                                           class="mt-3 block w-full text-[11px] font-semibold text-cyan-800 bg-cyan-100 hover:bg-cyan-200 py-2 px-2.5 rounded-lg text-center transition">
                                            {{ $diffKontrak <= 0 ? '⚠️ Kontrak Berakhir (Cek SK)' : "⏳ Sisa {$diffKontrak} Bln (Cek SK)" }} &rarr;
                                        </a>
                                    @else
                                        <a href="{{ route('pegawai.edit', ['pegawai' => $p->id]) }}#section-administrasi" 
                                           class="mt-3 block w-full text-[11px] font-bold text-cyan-900 bg-cyan-200/90 hover:bg-cyan-300 py-2 px-2.5 rounded-lg text-center transition shadow-xs flex items-center justify-center gap-1">
                                            <span>✏️</span> Lengkapi Masa Kontrak &rarr;
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- 3. Sasaran Kinerja Pegawai (SKP) --}}
                            <div class="p-4 rounded-xl border border-purple-200 bg-purple-50/60 flex flex-col justify-between hover:shadow-md transition duration-150">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-bold uppercase tracking-wider text-purple-900">🎯 Sasaran Kinerja (SKP)</span>
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-purple-200 text-purple-900">{{ date('Y') }}</span>
                                    </div>
                                    <div class="text-xs space-y-1 text-slate-700">
                                        <p>Tahun: <strong class="text-slate-900">{{ date('Y') }}</strong></p>
                                        <p>Status: <strong class="text-purple-800">Evaluasi Tahunan</strong></p>
                                    </div>
                                </div>
                                <div>
                                    <a href="{{ route('pegawai.show', $p->id) }}" 
                                       class="mt-3 block w-full text-[11px] font-semibold text-purple-800 bg-purple-100 hover:bg-purple-200 py-2 px-2.5 rounded-lg text-center transition flex items-center justify-center gap-1">
                                        <span>🎯</span> Sasaran Kinerja Pegawai &rarr;
                                    </a>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>

                {{-- TABEL PENGAJUAN CUTI PRIBADI TERBARU --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-slate-100 pb-3 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🏖️</span>
                            <h3 class="font-bold text-base text-slate-800">Riwayat Pengajuan Cuti Saya</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('pengajuan-cuti.create') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white transition shadow-sm flex items-center gap-1.5">
                                <span>+</span> Ajukan Cuti Baru
                            </a>
                            <a href="{{ route('pengajuan-cuti.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                                Lihat Semua Cuti &rarr;
                            </a>
                        </div>
                    </div>

                    @if(isset($myCuti) && count($myCuti) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs text-left text-slate-600">
                                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px]">
                                    <tr>
                                        <th class="px-4 py-2.5 rounded-l-lg">Jenis Cuti</th>
                                        <th class="px-4 py-2.5">Tanggal Permohonan</th>
                                        <th class="px-4 py-2.5">Durasi Hari</th>
                                        <th class="px-4 py-2.5 rounded-r-lg">Status Permohonan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($myCuti as $c)
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="px-4 py-3 font-bold text-slate-900">{{ $c->jenis_cuti }}</td>
                                            <td class="px-4 py-3 font-mono">{{ \Carbon\Carbon::parse($c->tanggal_mulai)->format('d-m-Y') }} s.d {{ \Carbon\Carbon::parse($c->tanggal_selesai)->format('d-m-Y') }}</td>
                                            <td class="px-4 py-3 font-semibold">{{ $c->jumlah_hari }} Hari Kerja</td>
                                            <td class="px-4 py-3">
                                                @if($c->status === 'Disetujui')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">✅ Disetujui</span>
                                                @elseif($c->status === 'Ditolak')
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">❌ Ditolak</span>
                                                @else
                                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">⏳ Menunggu Persetujuan</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-400 text-xs">
                            <span class="text-3xl block mb-2">🏖️</span>
                            Belum ada riwayat pengajuan cuti tercatat.
                        </div>
                    @endif
                </div>

                {{-- 🏛️ TRANSPARANSI STRUKTUR ORGANISASI & PETA JABATAN DIGITAL --}}
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-950 rounded-2xl p-6 text-white shadow-sm border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-3xl border border-white/15 shrink-0 shadow-inner">
                            🏛️
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/30 text-indigo-300 border border-indigo-400/30 mb-1">
                                Transparansi Struktur Organisasi Fakultas
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-white">Peta Jabatan & Alur Komando Organisasi FKp UNRI</h3>
                            <p class="text-xs text-slate-300 mt-0.5 max-w-2xl">
                                Lihat bagan struktur interaktif Fakultas Keperawatan UNRI: Unsur Pimpinan (Dekan & 3 Wadek), Program Studi, Bagian Umum, serta 27 Unit Penunjang, Lab, dan KJFD.
                            </p>
                        </div>
                    </div>
                    <div class="shrink-0 w-full md:w-auto">
                        <a href="{{ route('anjab.peta-jabatan') }}" class="w-full md:w-auto px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-2">
                            <span>Buka Peta Jabatan Digital</span> &rarr;
                        </a>
                    </div>
                </div>

                @else
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-amber-800">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">⚠️</span>
                            <div>
                                <h3 class="font-bold text-base">Profil Pegawai Belum Terhubung</h3>
                                <p class="text-xs mt-1 text-amber-700">Akun pengguna Anda ({{ Auth::user()->email }}) belum terhubung dengan data profil pegawai. Silakan hubungi Administrator untuk menghubungkan NIP/ID Pegawai Anda.</p>
                            </div>
                        </div>
                    </div>
                @endif

            @else
                {{-- ========================================================================= --}}
                {{-- 🏢 DASHBOARD MANAJERIAL FAKULTAS (KHUSUS ADMIN & PIMPINAN)                --}}
                {{-- ========================================================================= --}}

                {{-- Banner Ucapan Selamat Datang Manajerial / Eksekutif --}}
                <div class="rounded-2xl p-6 sm:p-7 shadow-lg text-white relative overflow-hidden mb-6" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #064e3b 100%) !important; color: #ffffff !important;">
                    <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
                        <svg class="w-64 h-64 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    </div>

                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 relative z-10">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold border border-white/20" style="background-color: rgba(255, 255, 255, 0.15) !important; color: #ffffff !important;">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Sistem Informasi Kepegawaian & Kinerja (SIKAP)</span>
                                <span>•</span>
                                <span>FKp UNRI</span>
                            </div>

                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold tracking-tight text-white">
                                Selamat Datang, {{ Auth::user()->name }} 👋
                            </h1>

                            <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-slate-200">
                                @if(Auth::user()->pegawai?->jabatan?->nama_jabatan)
                                    <span class="inline-flex items-center gap-1.5 font-medium text-emerald-300">
                                        💼 {{ Auth::user()->pegawai?->jabatan?->nama_jabatan }}
                                    </span>
                                    @if(Auth::user()->pegawai?->nip)
                                        <span class="text-slate-400 font-mono">• NIP: {{ Auth::user()->pegawai?->nip }}</span>
                                    @endif
                                @elseif(Auth::user()->hasRole('admin'))
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-emerald-300">
                                        🛡️ Administrator Utama Kepegawaian
                                    </span>
                                @endif
                                <span class="text-slate-400">• Fakultas Keperawatan Universitas Riau</span>
                            </div>
                        </div>

                        {{-- Panel Waktu & Status Kalender Akademik / Fiskal --}}
                        <div class="flex sm:flex-row lg:flex-col items-start sm:items-center lg:items-end justify-between sm:justify-start gap-3 bg-white/10 backdrop-blur-md rounded-xl p-3.5 sm:px-5 sm:py-3.5 border border-white/15 shrink-0">
                            <div class="text-left lg:text-right">
                                <div class="text-[10px] uppercase font-bold text-slate-300 tracking-wider">Kalender Kerja</div>
                                <div class="text-sm sm:text-base font-bold text-white font-mono">
                                    {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                                </div>
                            </div>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                <span>T.A. {{ date('Y') }}</span>
                                <span>•</span>
                                <span>Sem. {{ date('n') >= 7 ? 'Ganjil' : 'Genap' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🎯 BANNER PAPARAN PIMPINAN (KHUSUS ADMIN) --}}
                @if(Auth::user()->hasRole('admin'))
                <div class="mb-6 rounded-2xl bg-gradient-to-r from-emerald-900 via-teal-900 to-slate-900 p-5 text-white shadow-md border border-emerald-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-400/30 text-emerald-300 flex items-center justify-center text-2xl shrink-0">
                            🎯
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-400/20 uppercase tracking-wider mb-1">
                                Siap Papar Dekanat
                            </div>
                            <h3 class="text-base font-black text-white">Lembar Paparan Eksekutif &amp; Ringkasan Strategis Pimpinan</h3>
                            <p class="text-xs text-slate-300 mt-0.5">
                                Bahan paparan transformasi digital kepegawaian SIKAP untuk presentasi kepada Ibu Dekan Prof. Wan Nishfa Dewi, S.Kp, MNg, PhD dan jajaran Pimpinan Dekanat.
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 w-full sm:w-auto">
                        <a href="{{ route('pimpinan.executive-brief') }}" class="w-full sm:w-auto text-center px-4 py-2.5 rounded-xl font-bold text-xs bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-sm transition">
                            Buka Lembar Paparan &rarr;
                        </a>
                    </div>
                </div>
                @endif

                {{-- 🏛️ EXECUTIVE ACTION CENTER: MEJA VERIFIKASI & PENGESAHAN TATA NASKAH (URUTAN 4 TAHAP BERJENJANG) --}}
                @php
                    $stage1Items = $workflowStageLists[1] ?? [];
                    $stage2Items = $workflowStageLists[2] ?? [];
                    $stage3Items = $workflowStageLists[3] ?? [];
                    $stage4Items = $workflowStageLists[4] ?? [];

                    $stage1Count = !empty($stage1Items) ? count($stage1Items) : ($workflowStats['admin_screening_total'] ?? 0);
                    $stage2Count = !empty($stage2Items) ? count($stage2Items) : ($workflowStats['pokja_pending_total'] ?? 0);
                    $stage3Count = !empty($stage3Items) ? count($stage3Items) : ($workflowStats['kabag_pending_total'] ?? 0);
                    $stage4Count = !empty($stage4Items) ? count($stage4Items) : (($workflowStats['wd2_pending_total'] ?? 0) + ($workflowStats['dekan_pending_total'] ?? 0) + ($pendingCutiCount ?? 0));
                @endphp
                <div id="section-action-center" 
                     x-data="{ 
                         activeStageModal: null, 
                         filterModul: 'all',
                         openStageModal(stage) {
                             this.activeStageModal = stage;
                             this.filterModul = 'all';
                         },
                         closeModal() {
                             this.activeStageModal = null;
                         }
                     }"
                     @keydown.escape.window="closeModal()"
                     class="bg-white rounded-2xl shadow-sm border border-slate-200/90 p-5 sm:p-6 mb-6 scroll-mt-20 relative">
                    <div class="flex flex-col md:flex-row md:items-center justify-between pb-4 mb-5 border-b border-slate-100 gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-slate-900 via-indigo-900 to-blue-900 text-white flex items-center justify-center text-xl shadow-xs shrink-0">
                                🏛️
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-base sm:text-lg font-bold text-slate-900">Executive Action Center &amp; Meja Pengesahan Berjenjang</h3>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase tracking-wider">
                                        SOP Tata Naskah 4 Tahap
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">
                                    Alur Bertingkat: 1. Admin Screening &rarr; 2. Paraf Ka Pokja &rarr; 3. Paraf Kabag Umum &rarr; 4. TTE Pimpinan (WD II / Dekan / PYBMC).
                                </p>
                            </div>
                        </div>

                        {{-- Tag Status User Login --}}
                        @php
                            $roleBadgeLabel = '👔 Pejabat / Atasan';
                            $roleBadgeClass = 'bg-slate-100 text-slate-800 border border-slate-200';
                            if ($isAdmin ?? false) {
                                $roleBadgeLabel = '🛡️ Admin Kepegawaian';
                                $roleBadgeClass = 'bg-amber-100 text-amber-900 border border-amber-300';
                            } elseif ($isDekan ?? false) {
                                $roleBadgeLabel = '🎓 Dekan Fakultas';
                                $roleBadgeClass = 'bg-emerald-100 text-emerald-900 border border-emerald-300';
                            } elseif ($isWadek1 ?? false) {
                                $roleBadgeLabel = '📖 Wakil Dekan I (Akademik)';
                                $roleBadgeClass = 'bg-sky-100 text-sky-900 border border-sky-300';
                            } elseif ($isWadek2 ?? false) {
                                $roleBadgeLabel = '💼 Wakil Dekan II (Kepeg/Keu)';
                                $roleBadgeClass = 'bg-blue-100 text-blue-900 border border-blue-300';
                            } elseif ($isWadek3 ?? false) {
                                $roleBadgeLabel = '🤝 Wakil Dekan III (Mhs/Kerjasama)';
                                $roleBadgeClass = 'bg-indigo-100 text-indigo-900 border border-indigo-300';
                            } elseif ($isKajur ?? false) {
                                $roleBadgeLabel = '🏢 Ketua Jurusan';
                                $roleBadgeClass = 'bg-teal-100 text-teal-900 border border-teal-300';
                            } elseif ($isSekjur ?? false) {
                                $roleBadgeLabel = '📝 Sekretaris Jurusan';
                                $roleBadgeClass = 'bg-cyan-100 text-cyan-900 border border-cyan-300';
                            } elseif ($isKabag ?? false) {
                                $roleBadgeLabel = '🏛️ Kabag Umum';
                                $roleBadgeClass = 'bg-purple-100 text-purple-900 border border-purple-300';
                            } elseif ($isPokja ?? false) {
                                $roleBadgeLabel = '📋 Ka Pokja Keu-Kepeg';
                                $roleBadgeClass = 'bg-rose-100 text-rose-900 border border-rose-300';
                            }
                        @endphp
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500 font-medium">Peran Anda:</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold shadow-2xs {{ $roleBadgeClass }}">
                                {{ $roleBadgeLabel }}
                            </span>
                        </div>
                    </div>

                    {{-- 4 Kotak Alur Bertingkat (Interactive Pipeline) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        {{-- 1. TAHAP 1: SCREENING ADMIN & NOMOR SURAT --}}
                        <div class="p-4 rounded-xl border {{ $isAdmin ? 'border-amber-400 bg-amber-50/70 shadow-sm ring-1 ring-amber-300' : 'border-slate-200 bg-slate-50/60' }} flex flex-col justify-between transition hover:shadow-md">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-lg bg-amber-500 text-white flex items-center justify-center text-xs font-black">1</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-amber-900">Screening Admin</span>
                                    </div>
                                    @if($isAdmin)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-amber-200 text-amber-900">👉 Tugas Anda</span>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-slate-900">{{ $stage1Count }}</span>
                                    <span class="text-xs font-bold text-amber-800">Berkas Menunggu</span>
                                </div>
                                
                                {{-- Ringkasan Kategori --}}
                                <div class="text-[11px] text-slate-600 mt-2 space-y-1">
                                    <a href="{{ route('layanan-surat.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-amber-100/80 transition group" title="Buka Meja E-Surat Aktif Bekerja">
                                        <span class="group-hover:text-amber-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>📄</span> E-Surat Aktif:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-amber-900">{{ $workflowStats['admin_screening_surat'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('pengajuan-karir.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-amber-100/80 transition group" title="Buka Meja Usulan KP & KGB">
                                        <span class="group-hover:text-amber-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🎖️</span> Usulan KP &amp; KGB:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-amber-900">{{ $workflowStats['admin_screening_karir'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('satyalancana.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-amber-100/80 transition group" title="Buka Meja Usulan Satyalancana">
                                        <span class="group-hover:text-amber-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🏅</span> Satyalancana:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-amber-900">{{ $workflowStats['admin_screening_satya'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('pengajuan-cuti.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-amber-100/80 transition group" title="Buka Meja Screening E-Cuti Pegawai">
                                        <span class="group-hover:text-amber-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🏖️</span> E-Cuti Pegawai:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-amber-900">{{ $workflowStats['admin_screening_cuti'] ?? 0 }}</strong>
                                    </a>
                                    @php
                                        $nomorPendingCount = $workflowStats['admin_nomor_total'] ?? 0;
                                        $targetNomor = ($workflowStats['admin_nomor_surat'] ?? 0) > 0 ? route('layanan-surat.index') : route('pengajuan-karir.index');
                                    @endphp
                                    @if($nomorPendingCount > 0)
                                        <a href="{{ $targetNomor }}" class="flex items-center justify-between pt-1 border-t border-amber-200/50 text-amber-900 font-semibold px-1.5 py-0.5 hover:bg-amber-100/90 rounded transition group" title="Buka berkas dinas yang menunggu registrasi nomor surat">
                                            <span class="group-hover:text-amber-950 flex items-center gap-1 text-[11px]">
                                                <span>⚠️</span> Perlu Penomoran:
                                            </span>
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-mono font-bold bg-amber-500 text-white animate-pulse">
                                                {{ $nomorPendingCount }}
                                            </span>
                                        </a>
                                    @else
                                        <div class="flex items-center justify-between pt-1 border-t border-amber-200/50 text-slate-500 text-[11px] px-1.5 py-0.5" title="Semua naskah dinas dan surat keputusan telah memiliki nomor resmi">
                                            <span class="flex items-center gap-1 text-slate-600">
                                                <span class="text-emerald-600 font-bold">✓</span> Penomoran Surat:
                                            </span>
                                            <span class="font-medium text-emerald-700 text-[10px] bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200/60">Lengkap (0)</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- 📋 Preview Dokumen Menunggu Tahap 1 --}}
                                <div class="mt-3 pt-2.5 border-t border-amber-200/60">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Antrean Dokumen:</span>
                                        @if(count($stage1Items) > 0)
                                            <span class="text-[9px] font-bold text-amber-800 bg-amber-200/80 px-1.5 py-0.2 rounded">{{ count($stage1Items) }} Dokumen</span>
                                        @endif
                                    </div>
                                    @if(empty($stage1Items))
                                        <div class="text-[11px] text-slate-400 italic py-1 flex items-center gap-1">
                                            <span class="text-emerald-600 font-bold">✓</span> Tidak ada antrean berkas
                                        </div>
                                    @else
                                        <div class="space-y-1.5">
                                            @foreach(array_slice($stage1Items, 0, 2) as $doc)
                                                <div class="p-2 rounded-lg bg-white/95 border border-amber-200 shadow-2xs hover:border-amber-400 transition text-[11px]">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="font-bold text-slate-800 line-clamp-1" title="{{ $doc['judul'] }}">
                                                            {{ $doc['modul_icon'] }} {{ $doc['judul'] }}
                                                        </span>
                                                        @if(($doc['metode_ttd'] ?? 'elektronik') === 'manual')
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">✍️ Manual</span>
                                                        @else
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">⚡ Elektronik</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-600">
                                                        <span class="truncate max-w-[125px]" title="{{ $doc['pegawai_nama'] }} (NIP: {{ $doc['pegawai_nip'] }})">
                                                            👤 {{ $doc['pegawai_nama'] }}
                                                        </span>
                                                        <a href="{{ $doc['url'] }}" class="font-bold text-amber-700 hover:text-amber-900 underline ml-1 shrink-0">
                                                            Buka &rarr;
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if(count($stage1Items) > 2)
                                                <p class="text-[10px] text-center text-slate-500 font-medium">
                                                    + {{ count($stage1Items) - 2 }} dokumen lainnya...
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-200/80">
                                <button type="button" @click="openStageModal(1)" class="w-full py-2 px-3 text-center rounded-lg text-xs font-bold transition shadow-2xs {{ $isAdmin ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300' }}">
                                    Buka Meja Screening ({{ $stage1Count }}) &rarr;
                                </button>
                            </div>
                        </div>

                        {{-- 2. TAHAP 2: PARAF KA POKJA KEU-KEPEG --}}
                        <div class="p-4 rounded-xl border {{ $isPokja ? 'border-sky-400 bg-sky-50/70 shadow-sm ring-1 ring-sky-300' : 'border-slate-200 bg-slate-50/60' }} flex flex-col justify-between transition hover:shadow-md">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-lg bg-sky-600 text-white flex items-center justify-center text-xs font-black">2</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-sky-950">Paraf Ka Pokja</span>
                                    </div>
                                    @if($isPokja)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-sky-200 text-sky-900">👉 Tugas Anda</span>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-slate-900">{{ $stage2Count }}</span>
                                    <span class="text-xs font-bold text-sky-800">Menunggu Paraf</span>
                                </div>
                                
                                {{-- Ringkasan Kategori --}}
                                <div class="text-[11px] text-slate-600 mt-2 space-y-1">
                                    <a href="{{ route('layanan-surat.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-sky-100/80 transition group" title="Buka Paraf E-Surat Mandiri">
                                        <span class="group-hover:text-sky-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>📄</span> E-Surat Mandiri:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-sky-900">{{ $workflowStats['pokja_pending_surat'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('pengajuan-karir.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-sky-100/80 transition group" title="Buka Paraf Usulan KP & KGB">
                                        <span class="group-hover:text-sky-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🎖️</span> Usulan KP &amp; KGB:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-sky-900">{{ $workflowStats['pokja_pending_karir'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('satyalancana.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-sky-100/80 transition group" title="Buka Paraf Satyalancana">
                                        <span class="group-hover:text-sky-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🏅</span> Satyalancana:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-sky-900">{{ $workflowStats['pokja_pending_satya'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('pengajuan-cuti.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-sky-100/80 transition group" title="Buka Paraf Koordinasi E-Cuti Pegawai">
                                        <span class="group-hover:text-sky-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🏖️</span> E-Cuti Pegawai:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-sky-900">{{ $workflowStats['pokja_pending_cuti'] ?? 0 }}</strong>
                                    </a>
                                    <p class="text-[10px] text-sky-700 italic pt-1 border-t border-sky-200/50">
                                        Pemeriksaan keaktifan dinas &amp; kelayakan berkas.
                                    </p>
                                </div>

                                {{-- 📋 Preview Dokumen Menunggu Tahap 2 --}}
                                <div class="mt-3 pt-2.5 border-t border-sky-200/60">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Antrean Dokumen:</span>
                                        @if(count($stage2Items) > 0)
                                            <span class="text-[9px] font-bold text-sky-800 bg-sky-200/80 px-1.5 py-0.2 rounded">{{ count($stage2Items) }} Dokumen</span>
                                        @endif
                                    </div>
                                    @if(empty($stage2Items))
                                        <div class="text-[11px] text-slate-400 italic py-1 flex items-center gap-1">
                                            <span class="text-emerald-600 font-bold">✓</span> Tidak ada antrean berkas
                                        </div>
                                    @else
                                        <div class="space-y-1.5">
                                            @foreach(array_slice($stage2Items, 0, 2) as $doc)
                                                <div class="p-2 rounded-lg bg-white/95 border border-sky-200 shadow-2xs hover:border-sky-400 transition text-[11px]">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="font-bold text-slate-800 line-clamp-1" title="{{ $doc['judul'] }}">
                                                            {{ $doc['modul_icon'] }} {{ $doc['judul'] }}
                                                        </span>
                                                        @if(($doc['metode_ttd'] ?? 'elektronik') === 'manual')
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">✍️ Manual</span>
                                                        @else
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">⚡ Elektronik</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-600">
                                                        <span class="truncate max-w-[125px]" title="{{ $doc['pegawai_nama'] }} (NIP: {{ $doc['pegawai_nip'] }})">
                                                            👤 {{ $doc['pegawai_nama'] }}
                                                        </span>
                                                        <a href="{{ $doc['url'] }}" class="font-bold text-sky-700 hover:text-sky-900 underline ml-1 shrink-0">
                                                            Buka &rarr;
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if(count($stage2Items) > 2)
                                                <p class="text-[10px] text-center text-slate-500 font-medium">
                                                    + {{ count($stage2Items) - 2 }} dokumen lainnya...
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-200/80">
                                <button type="button" @click="openStageModal(2)" class="w-full py-2 px-3 text-center rounded-lg text-xs font-bold transition shadow-2xs {{ $isPokja ? 'bg-sky-600 hover:bg-sky-700 text-white' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300' }}">
                                    Paraf Koordinasi Pokja ({{ $stage2Count }}) &rarr;
                                </button>
                            </div>
                        </div>

                        {{-- 3. TAHAP 3: TELAAH, PARAF & TTE KABAG UMUM --}}
                        <div class="p-4 rounded-xl border {{ $isKabag ? 'border-purple-400 bg-purple-50/70 shadow-sm ring-1 ring-purple-300' : 'border-slate-200 bg-slate-50/60' }} flex flex-col justify-between transition hover:shadow-md">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xs font-black">3</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-purple-950">Paraf / TTE Kabag</span>
                                    </div>
                                    @if($isKabag)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-purple-200 text-purple-900">👉 Tugas Anda</span>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-slate-900">{{ $stage3Count }}</span>
                                    <span class="text-xs font-bold text-purple-800">Menunggu Telaah</span>
                                </div>
                                
                                {{-- Ringkasan Kategori --}}
                                <div class="text-[11px] text-slate-600 mt-2 space-y-1">
                                    <a href="{{ route('layanan-surat.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-purple-100/80 transition group" title="Buka Telaah E-Surat Mandiri">
                                        <span class="group-hover:text-purple-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>📄</span> E-Surat Mandiri:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-purple-900">{{ $workflowStats['kabag_pending_surat'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('pengajuan-karir.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-purple-100/80 transition group" title="Buka Telaah Usulan KP & KGB">
                                        <span class="group-hover:text-purple-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🎖️</span> Usulan KP &amp; KGB:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-purple-900">{{ $workflowStats['kabag_pending_karir'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('satyalancana.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-purple-100/80 transition group" title="Buka Telaah Satyalancana">
                                        <span class="group-hover:text-purple-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🏅</span> Satyalancana:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-purple-900">{{ $workflowStats['kabag_pending_satya'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('pengajuan-cuti.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-purple-100/80 transition group" title="Buka Pertimbangan & TTE Cuti Kabag Umum">
                                        <span class="group-hover:text-purple-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🏖️</span> E-Cuti Pegawai:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-purple-900">{{ $workflowStats['kabag_pending_cuti'] ?? 0 }}</strong>
                                    </a>
                                    <p class="text-[10px] text-purple-700 italic pt-1 border-t border-purple-200/50">
                                        Telaah tata naskah, paraf dinas &amp; TTE pertimbangan cuti.
                                    </p>
                                </div>

                                {{-- 📋 Preview Dokumen Menunggu Tahap 3 --}}
                                <div class="mt-3 pt-2.5 border-t border-purple-200/60">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Antrean Dokumen:</span>
                                        @if(count($stage3Items) > 0)
                                            <span class="text-[9px] font-bold text-purple-800 bg-purple-200/80 px-1.5 py-0.2 rounded">{{ count($stage3Items) }} Dokumen</span>
                                        @endif
                                    </div>
                                    @if(empty($stage3Items))
                                        <div class="text-[11px] text-slate-400 italic py-1 flex items-center gap-1">
                                            <span class="text-emerald-600 font-bold">✓</span> Tidak ada antrean berkas
                                        </div>
                                    @else
                                        <div class="space-y-1.5">
                                            @foreach(array_slice($stage3Items, 0, 2) as $doc)
                                                <div class="p-2 rounded-lg bg-white/95 border border-purple-200 shadow-2xs hover:border-purple-400 transition text-[11px]">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="font-bold text-slate-800 line-clamp-1" title="{{ $doc['judul'] }}">
                                                            {{ $doc['modul_icon'] }} {{ $doc['judul'] }}
                                                        </span>
                                                        @if(($doc['metode_ttd'] ?? 'elektronik') === 'manual')
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">✍️ Manual</span>
                                                        @else
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">⚡ Elektronik</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-600">
                                                        <span class="truncate max-w-[125px]" title="{{ $doc['pegawai_nama'] }} (NIP: {{ $doc['pegawai_nip'] }})">
                                                            👤 {{ $doc['pegawai_nama'] }}
                                                        </span>
                                                        <a href="{{ $doc['url'] }}" class="font-bold text-purple-700 hover:text-purple-900 underline ml-1 shrink-0">
                                                            Buka &rarr;
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if(count($stage3Items) > 2)
                                                <p class="text-[10px] text-center text-slate-500 font-medium">
                                                    + {{ count($stage3Items) - 2 }} dokumen lainnya...
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-200/80">
                                <button type="button" @click="openStageModal(3)" class="w-full py-2 px-3 text-center rounded-lg text-xs font-bold transition shadow-2xs {{ $isKabag ? 'bg-purple-600 hover:bg-purple-700 text-white' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300' }}">
                                    Telaah Tata Naskah ({{ $stage3Count }}) &rarr;
                                </button>
                            </div>
                        </div>

                        {{-- 4. TAHAP 4: TTE PIMPINAN (WD II / DEKAN / PYBMC) --}}
                        <div class="p-4 rounded-xl border {{ ($isWadek2 || $isDekan) ? 'border-emerald-400 bg-emerald-50/70 shadow-sm ring-1 ring-emerald-300' : 'border-slate-200 bg-slate-50/60' }} flex flex-col justify-between transition hover:shadow-md">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <span class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-xs font-black">4</span>
                                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-950">TTE Pimpinan</span>
                                    </div>
                                    @if($isWadek2 || $isDekan)
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-200 text-emerald-900">👉 Tugas Anda</span>
                                    @endif
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-slate-900">{{ $stage4Count }}</span>
                                    <span class="text-xs font-bold text-emerald-800">Siap Sah TTE</span>
                                </div>
                                
                                {{-- Ringkasan Kategori --}}
                                <div class="text-[11px] text-slate-600 mt-2 space-y-1">
                                    <a href="{{ route('pengajuan-karir.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-emerald-100/80 transition group" title="Buka Pengesahan TTE Usulan Karir WD II">
                                        <span class="group-hover:text-emerald-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>💼</span> TTE WD II (Usulan):
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-emerald-900">{{ $workflowStats['wd2_pending_total'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('layanan-surat.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-emerald-100/80 transition group" title="Buka Pengesahan TTE Dekan">
                                        <span class="group-hover:text-emerald-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🎓</span> TTE Dekan (Surat):
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-emerald-900">{{ $workflowStats['dekan_pending_total'] ?? 0 }}</strong>
                                    </a>
                                    <a href="{{ route('pengajuan-cuti.index') }}" class="flex items-center justify-between px-1.5 py-1 rounded hover:bg-emerald-100/80 transition group" title="Buka Keputusan Cuti PYBMC">
                                        <span class="group-hover:text-emerald-950 group-hover:font-semibold flex items-center gap-1">
                                            <span>🏖️</span> Keputusan Cuti PYBMC:
                                        </span>
                                        <strong class="font-mono text-slate-800 group-hover:text-emerald-900">{{ $pendingCutiCount ?? 0 }}</strong>
                                    </a>
                                    <p class="text-[10px] text-emerald-700 italic pt-1 border-t border-emerald-200/50">
                                        Penandatanganan elektronik resmi atau manual basah.
                                    </p>
                                </div>

                                {{-- 📋 Preview Dokumen Menunggu Tahap 4 --}}
                                <div class="mt-3 pt-2.5 border-t border-emerald-200/60">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-600">Antrean Dokumen:</span>
                                        @if(count($stage4Items) > 0)
                                            <span class="text-[9px] font-bold text-emerald-800 bg-emerald-200/80 px-1.5 py-0.2 rounded">{{ count($stage4Items) }} Dokumen</span>
                                        @endif
                                    </div>
                                    @if(empty($stage4Items))
                                        <div class="text-[11px] text-slate-400 italic py-1 flex items-center gap-1">
                                            <span class="text-emerald-600 font-bold">✓</span> Tidak ada antrean berkas
                                        </div>
                                    @else
                                        <div class="space-y-1.5">
                                            @foreach(array_slice($stage4Items, 0, 2) as $doc)
                                                <div class="p-2 rounded-lg bg-white/95 border border-emerald-200 shadow-2xs hover:border-emerald-400 transition text-[11px]">
                                                    <div class="flex items-start justify-between gap-1">
                                                        <span class="font-bold text-slate-800 line-clamp-1" title="{{ $doc['judul'] }}">
                                                            {{ $doc['modul_icon'] }} {{ $doc['judul'] }}
                                                        </span>
                                                        @if(($doc['metode_ttd'] ?? 'elektronik') === 'manual')
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-100 text-amber-800 border border-amber-300">✍️ Manual</span>
                                                        @else
                                                            <span class="shrink-0 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">⚡ Elektronik</span>
                                                        @endif
                                                    </div>
                                                    <div class="flex items-center justify-between mt-1 text-[10px] text-slate-600">
                                                        <span class="truncate max-w-[125px]" title="{{ $doc['pegawai_nama'] }} (NIP: {{ $doc['pegawai_nip'] }})">
                                                            👤 {{ $doc['pegawai_nama'] }}
                                                        </span>
                                                        <a href="{{ $doc['url'] }}" class="font-bold text-emerald-700 hover:text-emerald-900 underline ml-1 shrink-0">
                                                            Buka &rarr;
                                                        </a>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if(count($stage4Items) > 2)
                                                <p class="text-[10px] text-center text-slate-500 font-medium">
                                                    + {{ count($stage4Items) - 2 }} dokumen lainnya...
                                                </p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-3 pt-2.5 border-t border-slate-200/80">
                                <button type="button" @click="openStageModal(4)" class="w-full py-2 px-3 text-center rounded-lg text-xs font-bold transition shadow-2xs {{ ($isWadek2 || $isDekan) ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-300' }}">
                                    Meja Pengesahan TTE ({{ $stage4Count }}) &rarr;
                                </button>
                            </div>
                        </div>

                    </div>

                    {{-- 🌟 MODAL INTERAKTIF: MEJA KERJA PENGESAHAN DOKUMEN BERJENJANG 🌟 --}}
                    <div x-cloak x-show="activeStageModal !== null" 
                         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-5"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                         
                         <div @click.away="closeModal()" 
                              class="relative w-full max-w-5xl bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col max-h-[90vh] overflow-hidden"
                              x-transition:enter="transition ease-out duration-200"
                              x-transition:enter-start="opacity-0 scale-95"
                              x-transition:enter-end="opacity-100 scale-100"
                              x-transition:leave="transition ease-in duration-150"
                              x-transition:leave-start="opacity-100 scale-100"
                              x-transition:leave-end="opacity-0 scale-95">
                              
                              <!-- Modal Header -->
                              <div class="px-6 py-4 border-b border-slate-100 flex items-start justify-between bg-slate-50/80">
                                  <div class="flex items-center gap-3">
                                      <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg font-black text-white shrink-0 shadow-xs"
                                           :class="{
                                               'bg-amber-500': activeStageModal === 1,
                                               'bg-sky-600': activeStageModal === 2,
                                               'bg-purple-600': activeStageModal === 3,
                                               'bg-emerald-600': activeStageModal === 4
                                           }">
                                          <span x-text="activeStageModal"></span>
                                      </div>
                                      <div>
                                          <div class="flex items-center gap-2">
                                              <h4 class="text-base sm:text-lg font-bold text-slate-900"
                                                  x-text="activeStageModal === 1 ? 'Meja Screening & Registrasi Dokumen (Tahap 1)' :
                                                         (activeStageModal === 2 ? 'Meja Paraf Koordinasi Pokja Kepegawaian (Tahap 2)' :
                                                         (activeStageModal === 3 ? 'Meja Telaah Tata Naskah & Paraf Kabag Umum (Tahap 3)' :
                                                         'Meja Pengesahan & TTE Pimpinan Fakultas (Tahap 4)'))">
                                              </h4>
                                              <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                    :class="{
                                                        'bg-amber-100 text-amber-800 border border-amber-300': activeStageModal === 1,
                                                        'bg-sky-100 text-sky-800 border border-sky-300': activeStageModal === 2,
                                                        'bg-purple-100 text-purple-800 border border-purple-300': activeStageModal === 3,
                                                        'bg-emerald-100 text-emerald-800 border border-emerald-300': activeStageModal === 4
                                                    }">
                                                  SOP Berjenjang
                                              </span>
                                          </div>
                                          <p class="text-xs text-slate-500 mt-0.5"
                                             x-text="activeStageModal === 1 ? 'Tugas Admin: Screening kelengkapan berkas fisik/digital & registrasi penomoran sebelum diajukan ke Ka Pokja.' :
                                                    (activeStageModal === 2 ? 'Tugas Ka Pokja: Pemeriksaan keaktifan dinas, kelayakan berkas, dan pembubuhan paraf koordinasi.' :
                                                    (activeStageModal === 3 ? 'Tugas Kabag Umum: Telaah tata naskah dinas resmi, paraf persuratan/usulan karir, & TTE pertimbangan cuti.' :
                                                    'Tugas Pimpinan (WD II / Dekan / PYBMC): Pengesahan akhir dan penandatanganan resmi (Elektronik / Manual).'))">
                                          </p>
                                      </div>
                                  </div>
                                  <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-200/60 transition" title="Tutup Meja Kerja">
                                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                  </button>
                              </div>

                              <!-- Filter Bar by Document Type -->
                              <div class="px-6 py-2.5 bg-slate-100/70 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
                                  <div class="flex items-center gap-1.5 text-xs">
                                      <span class="font-bold text-slate-600 mr-1">Filter Modul:</span>
                                      <button type="button" @click="filterModul = 'all'" 
                                              :class="filterModul === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-300'"
                                              class="px-2.5 py-1 rounded-md text-xs font-semibold transition">
                                          Semua
                                      </button>
                                      <button type="button" @click="filterModul = 'surat'" 
                                              :class="filterModul === 'surat' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-300'"
                                              class="px-2.5 py-1 rounded-md text-xs font-semibold transition">
                                          📄 E-Surat
                                      </button>
                                      <button type="button" @click="filterModul = 'karir'" 
                                              :class="filterModul === 'karir' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-300'"
                                              class="px-2.5 py-1 rounded-md text-xs font-semibold transition">
                                          🎖️ KP &amp; KGB
                                      </button>
                                      <button type="button" @click="filterModul = 'satya'" 
                                              :class="filterModul === 'satya' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-300'"
                                              class="px-2.5 py-1 rounded-md text-xs font-semibold transition">
                                          🏅 Satyalancana
                                      </button>
                                      <button type="button" @click="filterModul = 'cuti'" 
                                              :class="filterModul === 'cuti' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-200 border border-slate-300'"
                                              class="px-2.5 py-1 rounded-md text-xs font-semibold transition">
                                          🏖️ E-Cuti
                                      </button>
                                  </div>
                                  <div class="text-[11px] text-slate-500">
                                      Tekan <kbd class="px-1.5 py-0.5 bg-white border border-slate-300 rounded font-mono shadow-2xs">ESC</kbd> untuk menutup
                                  </div>
                              </div>

                              <!-- Document List Table -->
                              <div class="flex-1 overflow-y-auto p-4 sm:p-6">
                                  @foreach([1, 2, 3, 4] as $stageNum)
                                      <div x-show="activeStageModal === {{ $stageNum }}">
                                          @php
                                              $currentItems = $workflowStageLists[$stageNum] ?? [];
                                          @endphp
                                          @if(empty($currentItems))
                                              <div class="py-12 text-center">
                                                  <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 text-2xl flex items-center justify-center mx-auto mb-3">
                                                      ✓
                                                  </div>
                                                  <h5 class="text-sm font-bold text-slate-800">Tidak Ada Antrean Berkas</h5>
                                                  <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                                      Semua naskah dinas dan usulan pada tahapan ini telah selesai diproses.
                                                  </p>
                                              </div>
                                          @else
                                              <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-2xs">
                                                  <table class="w-full text-left text-xs text-slate-700">
                                                      <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider text-[10px] font-bold border-b border-slate-200">
                                                          <tr>
                                                              <th class="px-3.5 py-3">Jenis Dokumen / Naskah</th>
                                                              <th class="px-3.5 py-3">Pemohon &amp; Unit</th>
                                                              <th class="px-3.5 py-3">Metode Tanda Tangan</th>
                                                              <th class="px-3.5 py-3">Tgl Pengajuan &amp; Nomor</th>
                                                              <th class="px-3.5 py-3 text-center">Tindakan</th>
                                                          </tr>
                                                      </thead>
                                                      <tbody class="divide-y divide-slate-100 bg-white">
                                                          @foreach($currentItems as $item)
                                                              <tr x-show="filterModul === 'all' || filterModul === '{{ $item['modul'] }}'" 
                                                                  class="hover:bg-slate-50/90 transition">
                                                                  <td class="px-3.5 py-3">
                                                                      <div class="flex items-center gap-2.5">
                                                                          <span class="text-xl shrink-0">{{ $item['modul_icon'] }}</span>
                                                                          <div>
                                                                              <div class="font-bold text-slate-900">{{ $item['judul'] }}</div>
                                                                              <span class="inline-block px-1.5 py-0.2 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200 mt-0.5">
                                                                                  {{ $item['modul_label'] }}
                                                                              </span>
                                                                          </div>
                                                                      </div>
                                                                  </td>
                                                                  <td class="px-3.5 py-3">
                                                                      <div class="font-bold text-slate-900">{{ $item['pegawai_nama'] }}</div>
                                                                      <div class="text-[11px] text-slate-500 font-mono">NIP: {{ $item['pegawai_nip'] }}</div>
                                                                      <div class="text-[10px] text-slate-400">{{ $item['unit_kerja'] }}</div>
                                                                  </td>
                                                                  <td class="px-3.5 py-3">
                                                                      @if(($item['metode_ttd'] ?? 'elektronik') === 'manual')
                                                                          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-2xs">
                                                                              ✍️ Manual (Basah)
                                                                          </span>
                                                                          <div class="text-[10px] text-slate-500 mt-0.5">Area TTD dikosongkan</div>
                                                                      @else
                                                                          <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs">
                                                                              ⚡ Elektronik (TTE)
                                                                          </span>
                                                                          <div class="text-[10px] text-slate-500 mt-0.5">QR Code / Sertifikat Digital</div>
                                                                      @endif
                                                                  </td>
                                                                  <td class="px-3.5 py-3">
                                                                      <div class="text-slate-800 font-medium">{{ $item['tanggal'] }}</div>
                                                                      <div class="text-[10px] font-mono text-slate-500">{{ $item['nomor'] }}</div>
                                                                  </td>
                                                                  <td class="px-3.5 py-3 text-center whitespace-nowrap">
                                                                      <a href="{{ $item['url'] }}" 
                                                                         class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold text-white transition shadow-xs {{ $stageNum === 1 ? 'bg-amber-600 hover:bg-amber-700' : ($stageNum === 2 ? 'bg-sky-600 hover:bg-sky-700' : ($stageNum === 3 ? 'bg-purple-600 hover:bg-purple-700' : 'bg-emerald-600 hover:bg-emerald-700')) }}">
                                                                          Proses Dokumen &rarr;
                                                                      </a>
                                                                  </td>
                                                              </tr>
                                                          @endforeach
                                                      </tbody>
                                                  </table>
                                              </div>
                                          @endif
                                      </div>
                                  @endforeach
                              </div>

                              <!-- Modal Footer -->
                              <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                                  <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-600">
                                      <span class="font-bold">Akses Cepat Modul:</span>
                                      <a href="{{ route('layanan-surat.index') }}" class="text-indigo-600 hover:underline">📄 E-Surat</a>
                                      <span>&bull;</span>
                                      <a href="{{ route('pengajuan-karir.index') }}" class="text-indigo-600 hover:underline">🎖️ Usulan Karir</a>
                                      <span>&bull;</span>
                                      <a href="{{ route('satyalancana.index') }}" class="text-indigo-600 hover:underline">🏅 Satyalancana</a>
                                      <span>&bull;</span>
                                      <a href="{{ route('pengajuan-cuti.index') }}" class="text-indigo-600 hover:underline">🏖️ E-Cuti</a>
                                  </div>
                                  <button type="button" @click="closeModal()" class="px-4 py-1.5 rounded-lg text-xs font-semibold bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 transition shadow-2xs">
                                      Tutup Meja Kerja
                                  </button>
                              </div>
                         </div>
                    </div>
                </div>

                {{-- 🎓 PANEL PEMANTAUAN AKADEMIK & DOSEN (KHUSUS WAKIL DEKAN I / DEKAN) --}}
                @if(($isWadek1 || $isDekan) && isset($akademikStats))
                <div class="mb-6 bg-gradient-to-br from-sky-900 via-sky-800 to-indigo-950 rounded-2xl p-5 text-white shadow-lg border border-sky-700/50 relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 border-b border-sky-700/60 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 rounded-xl bg-sky-500/20 text-sky-300 border border-sky-400/30 text-lg">🎓</span>
                            <div>
                                <h3 class="font-black text-sm tracking-wide text-sky-100 flex items-center gap-2">
                                    MONITORING PEMBINAAN DOSEN &amp; AKADEMIK
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-sky-400/20 text-sky-200 border border-sky-400/30">Tupoksi WD I</span>
                                </h3>
                                <p class="text-xs text-sky-200/70">Pengawasan kualifikasi dosen, kepemilikan NIDN, tugas belajar aktif, dan keabsahan izin profesi (STR).</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('pegawai.index') }}?jenis_pegawai=Dosen" class="px-3 py-1.5 rounded-lg bg-sky-500/20 hover:bg-sky-500/30 text-sky-200 text-xs font-semibold border border-sky-400/30 transition flex items-center gap-1.5">
                                <span>Master Dosen</span> &rarr;
                            </a>
                            <a href="{{ route('tugas-belajar.index') }}" class="px-3 py-1.5 rounded-lg bg-indigo-500/30 hover:bg-indigo-500/40 text-indigo-200 text-xs font-semibold border border-indigo-400/30 transition flex items-center gap-1.5">
                                <span>Tugas Belajar</span> &rarr;
                            </a>
                        </div>
                    </div>

                    <x-enterprise.stat-grid :cols="4">
                        <x-enterprise.stat-card
                            title="Total Dosen Aktif"
                            :value="$akademikStats['totalDosen'] ?? 0"
                            suffix="Orang"
                            icon="graduation-cap"
                            color="sky"
                            subtitle="Dosen Fakultas Keperawatan"
                        />
                        <x-enterprise.stat-card
                            title="Kepemilikan NIDN"
                            :value="$akademikStats['withNidn'] ?? 0"
                            :suffix="'/ ' . ($akademikStats['totalDosen'] ?? 0)"
                            icon="shield-check"
                            color="emerald"
                            :subtitle="($akademikStats['withoutNidn'] ?? 0) . ' dosen belum NIDN/NUPTK'"
                        />
                        <x-enterprise.stat-card
                            title="Tugas Belajar Aktif"
                            :value="$akademikStats['tubelAktif'] ?? 0"
                            suffix="Dosen"
                            icon="book-open"
                            color="amber"
                            subtitle="Studi Lanjut S2/S3/Spesialis"
                        />
                        <x-enterprise.stat-card
                            title="STR Expiring (≤ 6 Bln)"
                            :value="$akademikStats['strExpiring'] ?? 0"
                            suffix="Berkas"
                            icon="stethoscope"
                            :color="($akademikStats['strExpiring'] ?? 0) > 0 ? 'rose' : 'slate'"
                            subtitle="Perlu perpanjangan izin profesi"
                        />
                    </x-enterprise.stat-grid>
                </div>
                @endif

                {{-- 🏢 MEJA KOORDINASI JURUSAN (KHUSUS KAJUR & SEKJUR) --}}
                @if(($isKajur || $isSekjur) && isset($jurusanStats))
                <div class="mb-6 bg-gradient-to-br from-teal-900 via-teal-800 to-slate-900 rounded-2xl p-5 text-white shadow-lg border border-teal-700/50 relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 border-b border-teal-700/60 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 rounded-xl bg-teal-500/20 text-teal-300 border border-teal-400/30 text-lg">🏢</span>
                            <div>
                                <h3 class="font-black text-sm tracking-wide text-teal-100 flex items-center gap-2">
                                    MEJA KOORDINASI JURUSAN &amp; ATASAN LANGSUNG
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-teal-400/20 text-teal-200 border border-teal-400/30">{{ $isKajur ? 'Ketua Jurusan' : 'Sekretaris Jurusan' }}</span>
                                </h3>
                                <p class="text-xs text-teal-200/80">Unit Kerja: <span class="font-bold text-white">{{ $jurusanStats['namaUnit'] ?? 'Jurusan Keperawatan' }}</span> &bull; Pengawasan operasional dosen binaan.</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.logbook.index') }}" class="px-3 py-1.5 rounded-lg bg-teal-500/20 hover:bg-teal-500/30 text-teal-200 text-xs font-semibold border border-teal-400/30 transition flex items-center gap-1.5">
                                <span>Verifikasi Logbook</span> &rarr;
                            </a>
                            <a href="{{ route('pengajuan-cuti.index') }}" class="px-3 py-1.5 rounded-lg bg-emerald-500/30 hover:bg-emerald-500/40 text-emerald-200 text-xs font-semibold border border-emerald-400/30 transition flex items-center gap-1.5">
                                <span>Pertimbangan Cuti</span> &rarr;
                            </a>
                        </div>
                    </div>

                    <x-enterprise.stat-grid :cols="4" class="{{ (!empty($jurusanStats['recentPendingCuti']) && count($jurusanStats['recentPendingCuti']) > 0) ? 'mb-4' : '' }}">
                        <x-enterprise.stat-card
                            title="Dosen Jurusan"
                            :value="$jurusanStats['totalDosen'] ?? 0"
                            suffix="Orang"
                            icon="users"
                            color="teal"
                            subtitle="Dosen binaan di unit ini"
                        />
                        <x-enterprise.stat-card
                            title="Logbook Bulan Ini"
                            :value="$jurusanStats['sudahLogbookCount'] ?? 0"
                            :suffix="'/ ' . ($jurusanStats['totalDosen'] ?? 0)"
                            icon="clipboard-check"
                            :color="($jurusanStats['belumLogbookCount'] ?? 0) > 0 ? 'amber' : 'emerald'"
                            :subtitle="($jurusanStats['belumLogbookCount'] ?? 0) . ' dosen belum isi logbook'"
                        />
                        <x-enterprise.stat-card
                            title="Cuti Menunggu Pertimbangan"
                            :value="$jurusanStats['pendingCuti'] ?? 0"
                            suffix="Usulan"
                            icon="calendar-off"
                            :color="($jurusanStats['pendingCuti'] ?? 0) > 0 ? 'amber' : 'slate'"
                            subtitle="Memerlukan tanda tangan atasan"
                        />
                        <x-enterprise.stat-card
                            title="STR Dosen Expiring"
                            :value="$jurusanStats['strExpiringCount'] ?? 0"
                            suffix="Berkas"
                            icon="stethoscope"
                            :color="($jurusanStats['strExpiringCount'] ?? 0) > 0 ? 'rose' : 'slate'"
                            subtitle="≤ 6 bulan kedaluwarsa"
                        />
                    </x-enterprise.stat-grid>

                    @if(!empty($jurusanStats['recentPendingCuti']) && count($jurusanStats['recentPendingCuti']) > 0)
                    <div class="bg-black/20 rounded-xl p-3 border border-white/10">
                        <div class="text-xs font-bold text-teal-200 mb-2 flex items-center justify-between">
                            <span>Daftar Permohonan Cuti Dosen Menunggu Pertimbangan Atasan:</span>
                            <a href="{{ route('pengajuan-cuti.index') }}" class="text-[11px] text-teal-300 hover:underline">Lihat Semua &rarr;</a>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($jurusanStats['recentPendingCuti'] as $cutiItem)
                            <div class="flex items-center justify-between p-2 rounded-lg bg-white/5 hover:bg-white/10 transition text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                    <span class="font-bold text-white">{{ $cutiItem->pegawai?->nama_lengkap ?? 'Dosen' }}</span>
                                    <span class="text-teal-300/70 text-[11px]">({{ $cutiItem->jenis_cuti ?? 'Cuti Tahunan' }})</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-300 text-[11px]">{{ \Carbon\Carbon::parse($cutiItem->tanggal_mulai)->format('d M') }} - {{ \Carbon\Carbon::parse($cutiItem->tanggal_selesai)->format('d M Y') }}</span>
                                    <a href="{{ route('pengajuan-cuti.show', $cutiItem->id) }}" class="px-2 py-0.5 rounded bg-amber-500 hover:bg-amber-600 text-white font-semibold text-[11px] transition">Proses</a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @endif

                {{-- 🚨 PUSAT TINDAKAN & OPERASIONAL HARIAN (ACTION ITEMS) --}}
                <x-enterprise.stat-grid :cols="3" class="mb-6">
                    {{-- 1. Pending Logbook --}}
                    <x-enterprise.stat-card
                        title="Logbook Menunggu"
                        :value="$pendingLogbookCount ?? 0"
                        icon="clipboard-check"
                        color="indigo"
                        :trend="($pendingLogbookCount ?? 0) > 0 ? 'Review' : null"
                        trendDirection="neutral"
                        :subtitle="(($pendingLogbookCount ?? 0) > 0 ? 'Verifikasi Kinerja' : 'Semua Logbook Terverifikasi') . ' →'"
                        :href="route('admin.logbook.index', ['status' => 'diajukan'])"
                    />

                    {{-- 2. Presensi Hari Ini --}}
                    <x-enterprise.stat-card
                        title="Presensi Hari Ini"
                        :value="$todayPresentCount ?? 0"
                        icon="clock"
                        color="emerald"
                        trend="Live"
                        trendDirection="up"
                        subtitle="Pegawai Hadir Hari Ini →"
                        :href="route('admin.presensi.index')"
                    />

                    {{-- 3. Total Jam Logbook Bulan Ini --}}
                    <x-enterprise.stat-card
                        title="Kinerja Bulan Ini"
                        :value="$adminLogbookStats['total_jam'] ?? 0"
                        suffix="Jam"
                        icon="timer"
                        color="blue"
                        :subtitle="($adminLogbookStats['total'] ?? 0) . ' Aktivitas Terdata →'"
                        :href="route('admin.logbook.index')"
                    />
                </x-enterprise.stat-grid>

                {{-- ⚡ PINTASAN CEPAT MODUL POPULER (QUICK ACTIONS) --}}
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3.5 mb-4 border-b border-slate-100 gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-base border border-blue-100 shadow-2xs">
                                <x-icon name="sparkles" class="w-4 h-4 text-blue-600" />
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Pintasan Cepat Modul Populer</h4>
                                <p class="text-[11px] text-slate-500">Akses langsung ke seluruh modul operasional, data kepegawaian, surat dinas, dan monitoring harian.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                            {{-- 1. Master Pegawai --}}
                            <a href="{{ route('pegawai.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-blue-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="users" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-700">Master Pegawai</span>
                                <span class="text-[10px] text-slate-400">Data Profil</span>
                            </a>

                            {{-- 2. Layanan E-Surat Mandiri --}}
                            <a href="{{ route('layanan-surat.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="file-text" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-700">Layanan E-Surat</span>
                                <span class="text-[10px] text-slate-400">Surat Aktif Bekerja</span>
                            </a>
                        @endif

                        {{-- 3. Rekap Presensi --}}
                        <a href="{{ route('admin.presensi.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-emerald-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                            <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-emerald-600 group-hover:text-white transition duration-200 shadow-2xs">
                                <x-icon name="calendar-check" class="w-5 h-5" />
                            </span>
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-emerald-700">Rekap Presensi</span>
                            <span class="text-[10px] text-slate-400">Log &amp; GPS</span>
                        </a>

                        {{-- 4. Verifikasi Logbook --}}
                        <a href="{{ route('admin.logbook.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-indigo-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                            <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white transition duration-200 shadow-2xs">
                                <x-icon name="clipboard-list" class="w-5 h-5" />
                            </span>
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-700">Logbook Kinerja</span>
                            <span class="text-[10px] text-slate-400">Verifikasi</span>
                        </a>

                        {{-- 5. Pengajuan Cuti --}}
                        <a href="{{ route('pengajuan-cuti.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-amber-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                            <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-amber-600 group-hover:text-white transition duration-200 shadow-2xs">
                                <x-icon name="calendar-off" class="w-5 h-5" />
                            </span>
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-amber-700">Layanan Cuti</span>
                            <span class="text-[10px] text-slate-400">Persetujuan BKN</span>
                        </a>

                        @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                            {{-- 6. Kenaikan Pangkat --}}
                            <a href="{{ route('kp.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-purple-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-purple-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="award" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-purple-700">Kenaikan Pangkat</span>
                                <span class="text-[10px] text-slate-400">Usulan KP</span>
                            </a>

                            {{-- 7. Kenaikan Gaji Berkala --}}
                            <a href="{{ route('kgb.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-teal-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 border border-teal-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-teal-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="banknote" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-teal-700">Gaji Berkala</span>
                                <span class="text-[10px] text-slate-400">Jadwal KGB</span>
                            </a>

                            {{-- 8. Usulan Satyalancana --}}
                            <a href="{{ route('satyalancana.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-indigo-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-indigo-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="award" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-700">Satyalancana</span>
                                <span class="text-[10px] text-slate-400">Penghargaan</span>
                            </a>

                            {{-- 9. Beban Kerja (ABK) --}}
                            <a href="{{ route('abk.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-blue-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="chart" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-700">Formasi ABK</span>
                                <span class="text-[10px] text-slate-400">Analisis Beban</span>
                            </a>

                            {{-- 10. Manajemen Talenta ASN --}}
                            <a href="{{ route('manajemen-talenta.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-purple-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 border border-purple-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-purple-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="target" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-purple-700">Manajemen Talenta</span>
                                <span class="text-[10px] text-slate-400">9-Box &amp; Suksesi</span>
                            </a>

                            {{-- 11. DUK --}}
                            <a href="{{ route('duk.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-cyan-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-cyan-50 text-cyan-600 border border-cyan-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-cyan-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="list-ordered" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-cyan-700">Matriks DUK</span>
                                <span class="text-[10px] text-slate-400">Urut Pangkat</span>
                            </a>

                            {{-- 12. Tugas Belajar --}}
                            <a href="{{ route('tugas-belajar.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-rose-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 border border-rose-200/70 flex items-center justify-center mb-1.5 group-hover:scale-105 group-hover:bg-rose-600 group-hover:text-white transition duration-200 shadow-2xs">
                                    <x-icon name="graduation-cap" class="w-5 h-5" />
                                </span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-rose-700">Tugas Belajar</span>
                                <span class="text-[10px] text-slate-400">Studi Pegawai</span>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- BARIS 1: STATISTIK UTAMA PEGAWAI --}}
                <x-enterprise.stat-grid :cols="4" class="mb-6">
                    <x-enterprise.stat-card
                        title="Total Pegawai"
                        :value="$statistik['total'] ?? 0"
                        icon="users"
                        color="blue"
                        :href="route('pegawai.index')"
                    />

                    <x-enterprise.stat-card
                        title="Pegawai Aktif"
                        :value="$statistik['aktif'] ?? 0"
                        icon="user-check"
                        color="emerald"
                        :href="route('pegawai.index', ['filter' => 'aktif'])"
                    />

                    <x-enterprise.stat-card
                        title="Status ASN"
                        :value="$statistik['asn'] ?? 0"
                        icon="shield-check"
                        color="sky"
                        :href="route('pegawai.index', ['filter' => 'pns'])"
                    />

                    <x-enterprise.stat-card
                        title="Pegawai Pensiun"
                        :value="$statistik['pensiun'] ?? 0"
                        icon="building"
                        color="amber"
                        :href="route('pegawai.index', ['filter' => 'pensiun'])"
                    />
                </x-enterprise.stat-grid>

                {{-- BARIS 2: SUB-STATISTIK KLASIFIKASI KEPEGAWAIAN (INTERAKTIF) --}}
                <x-enterprise.stat-grid :cols="5" class="mb-6">
                    <x-enterprise.stat-card
                        title="Dosen PNS"
                        :value="$statistik['dosen_pns'] ?? $statistik['dosen'] ?? 0"
                        icon="graduation-cap"
                        color="indigo"
                        :href="route('kepegawaian.dosen.index', ['filter' => 'pns'])"
                    />

                    <x-enterprise.stat-card
                        title="Dosen PPPK"
                        :value="$statistik['dosen_pppk'] ?? 0"
                        icon="graduation-cap"
                        color="purple"
                        :href="route('kepegawaian.dosen.index', ['filter' => 'pppk'])"
                    />

                    <x-enterprise.stat-card
                        title="Tendik PNS"
                        :value="$statistik['tendik_pns'] ?? $statistik['tendik'] ?? 0"
                        icon="briefcase"
                        color="blue"
                        :href="route('kepegawaian.tendik.index', ['filter' => 'pns'])"
                    />

                    <x-enterprise.stat-card
                        title="Tendik PPPK"
                        :value="$statistik['tendik_pppk'] ?? 0"
                        icon="briefcase"
                        color="emerald"
                        :href="route('kepegawaian.tendik.index', ['filter' => 'pppk'])"
                    />

                    <x-enterprise.stat-card
                        title="Pegawai PHL"
                        :value="$statistik['phl'] ?? $statistik['honorer'] ?? 0"
                        icon="user"
                        color="amber"
                        :href="route('kepegawaian.phl.index')"
                    />
                </x-enterprise.stat-grid>

                {{-- BARIS 3: PUSAT REMINDER TRANSAKSI KEPEGAWAIAN --}}
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 sm:p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-slate-100 pb-3.5 mb-4 gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 border border-amber-200/60 flex items-center justify-center shadow-2xs">
                                <x-icon name="bell" class="w-4 h-4 text-amber-600" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800">Pusat Reminder Transaksi Kepegawaian</h3>
                                <p class="text-xs text-slate-500">Monitoring otomatis jatuh tempo KGB, Kenaikan Pangkat, Satyalancana, BUP Pensiun & STR/SIP.</p>
                            </div>
                        </div>
                        @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                            <div class="flex items-center gap-2">
                                <a href="{{ route('reports.reminder.pdf') }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                                    <x-icon name="file-text" class="w-3.5 h-3.5 text-white" />
                                    <span>Cetak PDF Pengingat</span>
                                </a>
                                <a href="{{ route('reports.reminder.excel') }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-semibold shadow-xs transition">
                                    <x-icon name="file-text" class="w-3.5 h-3.5 text-white" />
                                    <span>Export Excel</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        {{-- Kartu 1: KGB --}}
                        <div class="bg-amber-50/90 border border-amber-200/80 rounded-2xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-amber-900 flex justify-between items-center mb-2.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">Gaji Berkala (KGB)</span>
                                        <span class="block text-[10px] text-amber-700 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-amber-200/90 text-amber-950 text-xs px-2 py-0.5 rounded-full font-bold border border-amber-300/60">{{ is_countable($reminder['kgb'] ?? null) ? count($reminder['kgb']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['kgb']) && is_countable($reminder['kgb']) && count($reminder['kgb']) > 0)
                                    <ul class="text-xs text-amber-950 divide-y divide-amber-200/70 max-h-48 overflow-y-auto">
                                        @foreach($reminder['kgb'] as $r)
                                            <li class="py-2 flex justify-between items-center gap-2">
                                                <span class="truncate font-medium text-slate-800" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-amber-200/70 text-amber-900 border border-amber-300/60 shrink-0 font-semibold">{{ $r->tanggal_kegiatan }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs text-amber-700 mt-2 py-2 italic">
                                        <span class="text-emerald-600 font-bold">✓</span> Aman. Tidak ada jatuh tempo.
                                    </div>
                                @endif
                            </div>
                            @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                                <div class="mt-3 pt-2 border-t border-amber-200/60 text-right">
                                    <a href="{{ route('kgb.index', ['filter' => 'reminder']) }}" class="text-[11px] font-semibold text-amber-800 hover:text-amber-950 hover:underline inline-flex items-center gap-1">
                                        Buka Monitoring KGB &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Kartu 2: KP --}}
                        @php
                            $bknWindow = app(\App\Services\KelayakanKarirService::class)->getPeriodeBknStatus();
                        @endphp
                        <div class="bg-emerald-50/90 border border-emerald-200/80 rounded-2xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-emerald-900 flex justify-between items-center mb-1.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">Kenaikan Pangkat (KP)</span>
                                        <span class="block text-[10px] text-emerald-700 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-emerald-200/90 text-emerald-950 text-xs px-2 py-0.5 rounded-full font-bold border border-emerald-300/60">{{ is_countable($reminder['kp'] ?? null) ? count($reminder['kp']) : 0 }}</span>
                                </h4>

                                {{-- Lencana Periode Cutoff BKN (6 Periode) --}}
                                <div class="mb-2 px-2 py-1 rounded-lg {{ $bknWindow['is_open'] ? 'bg-emerald-100 border border-emerald-300/80 text-emerald-900' : 'bg-slate-100 border border-slate-200 text-slate-700' }} text-[10px]">
                                    <div class="flex items-center justify-between font-bold">
                                        <span class="flex items-center gap-1">
                                            <span>📅</span> Periode {{ $bknWindow['active_periode'] }}
                                        </span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold {{ $bknWindow['is_open'] ? 'bg-emerald-600 text-white animate-pulse' : 'bg-slate-400 text-white' }}">
                                            {{ $bknWindow['is_open'] ? 'Buka' : 'Tutup' }}
                                        </span>
                                    </div>
                                    <div class="text-[9px] text-slate-600 mt-0.5 truncate" title="{{ $bknWindow['label'] }}">
                                        {{ $bknWindow['is_open'] ? 'Batas Usulan: ' . ($bknWindow['current_window']['label'] ?? '-') : 'Buka: ' . ($bknWindow['next_window']['label'] ?? '-') }}
                                    </div>
                                </div>

                                @if(!empty($reminder['kp']) && is_countable($reminder['kp']) && count($reminder['kp']) > 0)
                                    <ul class="text-xs text-emerald-950 divide-y divide-emerald-200/70 max-h-40 overflow-y-auto">
                                        @foreach($reminder['kp'] as $r)
                                            <li class="py-2 flex justify-between items-center gap-2">
                                                <span class="truncate font-medium text-slate-800" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-emerald-200/70 text-emerald-900 border border-emerald-300/60 shrink-0 font-semibold">{{ $r->tanggal_kegiatan }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs text-emerald-700 mt-2 py-2 italic">
                                        <span class="text-emerald-600 font-bold">✓</span> Aman. Tidak ada jatuh tempo.
                                    </div>
                                @endif
                            </div>
                            @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                                <div class="mt-3 pt-2 border-t border-emerald-200/60 text-right">
                                    <a href="{{ route('pengajuan-karir.index', ['tab' => 'kp']) }}" class="text-[11px] font-semibold text-emerald-800 hover:text-emerald-950 hover:underline inline-flex items-center gap-1">
                                        Monitoring & Usulan KP &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Kartu 3: Satyalancana --}}
                        @php
                            $totalNominasiSatya = Cache::remember('dashboard_nominasi_satya_count', 600, function() {
                                $satyaService = app(\App\Services\SatyalancanaService::class);
                                $count = 0;
                                foreach (\App\Models\Pegawai::all() as $p) {
                                    if ($satyaService->cekKelayakanSatyalancana($p)) {
                                        $count++;
                                    }
                                }
                                return $count;
                            });
                        @endphp
                        <div class="bg-indigo-50/90 border border-indigo-200/80 rounded-2xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-indigo-900 flex justify-between items-center mb-1.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">Satyalancana</span>
                                        <span class="block text-[10px] text-indigo-700 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-indigo-200/90 text-indigo-950 text-xs px-2 py-0.5 rounded-full font-bold border border-indigo-300/60">{{ is_countable($reminder['satyalancana'] ?? null) ? count($reminder['satyalancana']) : 0 }}</span>
                                </h4>

                                {{-- Lencana Nominasi Satyalancana Tahun Berjalan --}}
                                <div class="mb-2 px-2 py-1 rounded-lg bg-indigo-100 border border-indigo-300/80 text-indigo-950 text-[10px]">
                                    <div class="flex items-center justify-between font-bold">
                                        <span class="flex items-center gap-1">
                                            <span>🏅</span> Nominasi {{ date('Y') }}
                                        </span>
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-extrabold bg-indigo-600 text-white">
                                            {{ $totalNominasiSatya }} Pegawai
                                        </span>
                                    </div>
                                    <div class="text-[9px] text-indigo-800 mt-0.5">
                                        Pengabdian 10, 20 &amp; 30 Thn
                                    </div>
                                </div>

                                @if(!empty($reminder['satyalancana']) && is_countable($reminder['satyalancana']) && count($reminder['satyalancana']) > 0)
                                    <ul class="text-xs text-indigo-950 divide-y divide-indigo-200/70 max-h-40 overflow-y-auto">
                                        @foreach($reminder['satyalancana'] as $r)
                                            <li class="py-2 flex justify-between items-center gap-2">
                                                <span class="truncate font-medium text-slate-800" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-indigo-200/70 text-indigo-900 border border-indigo-300/60 shrink-0 font-semibold">{{ $r->tanggal_kegiatan }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs text-indigo-700 mt-2 py-2 italic">
                                        <span class="text-emerald-600 font-bold">✓</span> Aman. Tidak ada jatuh tempo.
                                    </div>
                                @endif
                            </div>
                            @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                                <div class="mt-3 pt-2 border-t border-indigo-200/60 text-right">
                                    <a href="{{ route('satyalancana.index') }}" class="text-[11px] font-semibold text-indigo-800 hover:text-indigo-950 hover:underline inline-flex items-center gap-1" title="Buka Daftar Nominasi Satyalancana ({{ $totalNominasiSatya }} Pegawai)">
                                        Buka Nominasi ({{ $totalNominasiSatya }}) &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Kartu 4: Pensiun --}}
                        <div class="bg-rose-50/90 border border-rose-200/80 rounded-2xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-rose-900 flex justify-between items-center mb-2.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">Masa Pensiun (BUP)</span>
                                        <span class="block text-[10px] text-rose-700 font-semibold">1 Tahun ke Depan</span>
                                    </div>
                                    <span class="bg-rose-200/90 text-rose-950 text-xs px-2 py-0.5 rounded-full font-bold border border-rose-300/60">{{ is_countable($reminder['pensiun'] ?? null) ? count($reminder['pensiun']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['pensiun']) && is_countable($reminder['pensiun']) && count($reminder['pensiun']) > 0)
                                    <ul class="text-xs text-rose-950 divide-y divide-rose-200/70 max-h-48 overflow-y-auto">
                                        @foreach($reminder['pensiun'] as $r)
                                            <li class="py-2 flex justify-between items-center gap-2">
                                                <span class="truncate font-medium text-slate-800" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-rose-200/70 text-rose-900 border border-rose-300/60 shrink-0 font-semibold">{{ $r->tanggal_kegiatan }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs text-rose-700 mt-2 py-2 italic">
                                        <span class="text-emerald-600 font-bold">✓</span> Aman. Tidak ada masa pensiun terdekat.
                                    </div>
                                @endif
                            </div>
                            @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                                <div class="mt-3 pt-2 border-t border-rose-200/60 text-right">
                                    <a href="{{ route('pegawai.index', ['filter' => 'pensiun']) }}" class="text-[11px] font-semibold text-rose-800 hover:text-rose-950 hover:underline inline-flex items-center gap-1">
                                        Lihat Data Pensiun &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Kartu 5: STR / SIP --}}
                        <div class="bg-sky-50/90 border border-sky-200/80 rounded-2xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-sky-900 flex justify-between items-center mb-2.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">STR & SIP (Ners/Klinis)</span>
                                        <span class="block text-[10px] text-sky-700 font-semibold">6 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-sky-200/90 text-sky-950 text-xs px-2 py-0.5 rounded-full font-bold border border-sky-300/60">{{ is_countable($reminder['str_sip'] ?? null) ? count($reminder['str_sip']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['str_sip']) && is_countable($reminder['str_sip']) && count($reminder['str_sip']) > 0)
                                    <ul class="text-xs text-sky-950 divide-y divide-sky-200/70 max-h-48 overflow-y-auto">
                                        @foreach($reminder['str_sip'] as $r)
                                            <li class="py-2 flex justify-between items-center gap-2">
                                                <span class="truncate font-medium text-slate-800" title="{{ $r->nama_lengkap ?? $r->nama }} ({{ $r->jenis_dokumen }})">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <span class="font-mono text-[10px] px-1.5 py-0.5 rounded bg-sky-200/70 text-sky-900 border border-sky-300/60 shrink-0 font-semibold">{{ $r->tanggal_kegiatan }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="flex items-center gap-1.5 text-xs text-sky-700 mt-2 py-2 italic">
                                        <span class="text-emerald-600 font-bold">✓</span> Aman. STR/SIP aktif terpantau.
                                    </div>
                                @endif
                            </div>
                            @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                                <div class="mt-3 pt-2 border-t border-sky-200/60 text-right">
                                    <a href="{{ route('riwayat-str-sip.index') }}" class="text-[11px] font-semibold text-sky-800 hover:text-sky-950 hover:underline inline-flex items-center gap-1">
                                        Monitoring STR & SIP &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 🎯 MANAJEMEN TALENTA ASN & RENCANA SUKSESI JABATAN (PERMENPAN-RB NO. 3/2020) 🎯 --}}
                @if(isset($talentSummary))
                    @php
                        $tBoxes = $talentSummary['boxes'] ?? [];
                        $totalMapped = (int) ($talentSummary['total'] ?? 0);
                        $countPromosi = (int) (($tBoxes[7]['count'] ?? 0) + ($tBoxes[8]['count'] ?? 0) + ($tBoxes[9]['count'] ?? 0));
                        $countMid     = (int) (($tBoxes[4]['count'] ?? 0) + ($tBoxes[5]['count'] ?? 0) + ($tBoxes[6]['count'] ?? 0));
                        $countLow     = (int) (($tBoxes[1]['count'] ?? 0) + ($tBoxes[2]['count'] ?? 0) + ($tBoxes[3]['count'] ?? 0));
                        $pctPromosi   = $totalMapped > 0 ? round(($countPromosi / $totalMapped) * 100, 1) : 0;
                        $pctMid       = $totalMapped > 0 ? round(($countMid / $totalMapped) * 100, 1) : 0;
                        $pctLow       = $totalMapped > 0 ? round(($countLow / $totalMapped) * 100, 1) : 0;

                        // Ambil representasi kandidat unggulan Kotak 9 & 8 untuk preview
                        $topTalents = collect();
                        foreach ([9, 8] as $boxNum) {
                            $boxItems = $tBoxes[$boxNum]['items'] ?? null;
                            if (is_iterable($boxItems) && !(is_object($boxItems) && get_class($boxItems) === '__PHP_Incomplete_Class')) {
                                foreach ($boxItems as $item) {
                                    if (is_object($item) && isset($item->pegawai_id)) {
                                        $topTalents->push($item);
                                    }
                                }
                            }
                        }
                        $topTalentsPreview = $topTalents->take(4);
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
                        {{-- Header Eksekutif Talenta --}}
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5 mb-6">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-purple-700 to-indigo-600 text-white flex items-center justify-center text-2xl shadow-md shrink-0">
                                    🎯
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Manajemen Talenta ASN & Rencana Suksesi</h2>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                            PermenPAN-RB No. 3/2020
                                        </span>
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            Tahun {{ $talentSummary['tahun'] ?? date('Y') }}
                                        </span>
                                    </div>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                                        Pemetaan Sistem Merit berbasis Kuadran 9-Kotak (Sumbu Kinerja SKP × Sumbu Potensi/Kompetensi BKN).
                                    </p>
                                </div>
                            </div>

                            {{-- Tombol Navigasi Cepat --}}
                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                <a href="{{ route('manajemen-talenta.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow-sm" title="Buka Tampilan Lengkap Matriks 9-Kotak">
                                    <span>🎯</span> Matriks 9-Kotak
                                </a>
                                <a href="{{ route('manajemen-talenta.suksesi.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-800 bg-amber-100 hover:bg-amber-200 border border-amber-300 transition" title="Kelola Pemetaan Calon Pemimpin & Job Matching">
                                    <span>👑</span> Rencana Suksesi
                                </a>
                                <a href="{{ route('manajemen-talenta.rekap') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition" title="Tabel Rekap Data Talenta">
                                    <span>📋</span> Rekap Data
                                </a>
                            </div>
                        </div>

                        {{-- 4 Kartu KPI Indikator Talenta ASN --}}
                        <x-enterprise.stat-grid :cols="4" class="mb-6">
                            {{-- KPI 1: Talent Pool Siap Promosi (Kotak 7, 8, 9) --}}
                            <x-enterprise.stat-card
                                title="Talent Pool Siap Promosi"
                                :value="$countPromosi"
                                :suffix="'ASN (' . $pctPromosi . '%)'"
                                icon="crown"
                                color="emerald"
                                subtitle="Kotak VII, VIII & IX (Prioritas Suksesi) →"
                                :href="route('manajemen-talenta.rekap', ['only_suksesi' => 1])"
                            >
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $pctPromosi }}%"></div>
                                </div>
                            </x-enterprise.stat-card>

                            {{-- KPI 2: Kelompok Dipertahankan / Pengembangan (Kotak 4, 5, 6) --}}
                            <x-enterprise.stat-card
                                title="Kelompok Pengembangan"
                                :value="$countMid"
                                :suffix="'ASN (' . $pctMid . '%)'"
                                icon="trending-up"
                                color="blue"
                                subtitle="Kotak IV, V & VI (Fokus Diklat 20 JP) →"
                                :href="route('manajemen-talenta.rekap')"
                            >
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-blue-500 h-1.5 rounded-full" style="width: {{ $pctMid }}%"></div>
                                </div>
                            </x-enterprise.stat-card>

                            {{-- KPI 3: Bimbingan & Konseling (Kotak 1, 2, 3) --}}
                            <x-enterprise.stat-card
                                title="Bimbingan & Konseling"
                                :value="$countLow"
                                :suffix="'ASN (' . $pctLow . '%)'"
                                icon="alert-triangle"
                                color="amber"
                                subtitle="Kotak I, II & III (Penataan & Pembinaan) →"
                                :href="route('manajemen-talenta.rekap')"
                            >
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $pctLow }}%"></div>
                                </div>
                            </x-enterprise.stat-card>

                            {{-- KPI 4: Target Suksesi Jabatan Aktif --}}
                            <x-enterprise.stat-card
                                title="Rencana Suksesi Jabatan"
                                :value="$totalSuccessionPlans ?? 0"
                                suffix="Jabatan Target"
                                icon="building"
                                color="purple"
                                subtitle="Buka Pemetaan Suksesi Struktural →"
                                :href="route('manajemen-talenta.suksesi.index')"
                            >
                                <div class="text-[11px] text-slate-500">
                                    Total ASN Terpetakan: <strong class="text-slate-800">{{ $totalMapped }} ASN</strong>
                                </div>
                            </x-enterprise.stat-card>
                        </x-enterprise.stat-grid>

                        {{-- Layout Dua Kolom: Mini Matriks 9-Kotak & Highlight Top Talent Pool --}}
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                            
                            {{-- Kolom Kiri (7 Kolom): Mini Matriks Kuadran 9-Kotak Interaktif --}}
                            <div class="lg:col-span-7 bg-slate-50/80 rounded-2xl border border-slate-200 p-4">
                                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200">
                                    <div>
                                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Mini Matriks Kuadran 9-Kotak ASN</h3>
                                        <p class="text-[11px] text-slate-500">Klik pada kotak kuadran untuk melihat daftar nama pegawai</p>
                                    </div>
                                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-200">
                                        {{ $totalMapped }} Terpetakan
                                    </span>
                                </div>

                                {{-- Grid 3x3 Mini Matriks --}}
                                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                    
                                    {{-- Baris 1: Potensi Tinggi (Y: Tinggi) --}}
                                    {{-- Kotak 7 --}}
                                    @php $cnt7 = $tBoxes[7]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 7]) }}" class="p-2.5 rounded-xl border {{ $cnt7 > 0 ? 'border-emerald-300 bg-emerald-100/70 hover:bg-emerald-200' : 'border-slate-200 bg-white hover:bg-slate-100' }} transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-slate-500 uppercase">Kotak VII</div>
                                        <div class="text-xl font-black text-emerald-800 my-0.5">{{ $cnt7 }}</div>
                                        <div class="text-[10px] font-semibold text-slate-700 line-clamp-1">K. Di Bawah | P. Tinggi</div>
                                    </a>

                                    {{-- Kotak 8 --}}
                                    @php $cnt8 = $tBoxes[8]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 8]) }}" class="p-2.5 rounded-xl border {{ $cnt8 > 0 ? 'border-emerald-400 bg-emerald-200/80 hover:bg-emerald-300' : 'border-slate-200 bg-white hover:bg-slate-100' }} transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-emerald-800 uppercase">Kotak VIII</div>
                                        <div class="text-xl font-black text-emerald-900 my-0.5">{{ $cnt8 }}</div>
                                        <div class="text-[10px] font-semibold text-emerald-900 line-clamp-1">K. Sesuai | P. Tinggi</div>
                                    </a>

                                    {{-- Kotak 9 (Bintang Suksesi) --}}
                                    @php $cnt9 = $tBoxes[9]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 9]) }}" class="p-2.5 rounded-xl border border-emerald-500 bg-emerald-600 hover:bg-emerald-700 text-white transition block group shadow-sm">
                                        <div class="text-[10px] font-bold text-emerald-100 uppercase flex items-center justify-center gap-1"><span>👑</span> Kotak IX</div>
                                        <div class="text-xl font-black text-white my-0.5">{{ $cnt9 }}</div>
                                        <div class="text-[10px] font-bold text-emerald-50 line-clamp-1">K. Di Atas | P. Tinggi</div>
                                    </a>

                                    {{-- Baris 2: Potensi Sedang (Y: Sedang) --}}
                                    {{-- Kotak 4 --}}
                                    @php $cnt4 = $tBoxes[4]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 4]) }}" class="p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-slate-500 uppercase">Kotak IV</div>
                                        <div class="text-xl font-black text-slate-800 my-0.5">{{ $cnt4 }}</div>
                                        <div class="text-[10px] font-semibold text-slate-600 line-clamp-1">K. Di Bawah | P. Sedang</div>
                                    </a>

                                    {{-- Kotak 5 --}}
                                    @php $cnt5 = $tBoxes[5]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 5]) }}" class="p-2.5 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100 transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-blue-700 uppercase">Kotak V</div>
                                        <div class="text-xl font-black text-blue-900 my-0.5">{{ $cnt5 }}</div>
                                        <div class="text-[10px] font-semibold text-blue-800 line-clamp-1">K. Sesuai | P. Sedang</div>
                                    </a>

                                    {{-- Kotak 6 --}}
                                    @php $cnt6 = $tBoxes[6]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 6]) }}" class="p-2.5 rounded-xl border border-teal-200 bg-teal-50/70 hover:bg-teal-100 transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-teal-700 uppercase">Kotak VI</div>
                                        <div class="text-xl font-black text-teal-900 my-0.5">{{ $cnt6 }}</div>
                                        <div class="text-[10px] font-semibold text-teal-800 line-clamp-1">K. Di Atas | P. Sedang</div>
                                    </a>

                                    {{-- Baris 3: Potensi Rendah (Y: Rendah) --}}
                                    {{-- Kotak 1 --}}
                                    @php $cnt1 = $tBoxes[1]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 1]) }}" class="p-2.5 rounded-xl border border-rose-200 bg-rose-50/70 hover:bg-rose-100 transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-rose-700 uppercase">Kotak I</div>
                                        <div class="text-xl font-black text-rose-900 my-0.5">{{ $cnt1 }}</div>
                                        <div class="text-[10px] font-semibold text-rose-800 line-clamp-1">K. Di Bawah | P. Rendah</div>
                                    </a>

                                    {{-- Kotak 2 --}}
                                    @php $cnt2 = $tBoxes[2]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 2]) }}" class="p-2.5 rounded-xl border border-amber-200 bg-amber-50/70 hover:bg-amber-100 transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-amber-700 uppercase">Kotak II</div>
                                        <div class="text-xl font-black text-amber-900 my-0.5">{{ $cnt2 }}</div>
                                        <div class="text-[10px] font-semibold text-amber-800 line-clamp-1">K. Sesuai | P. Rendah</div>
                                    </a>

                                    {{-- Kotak 3 --}}
                                    @php $cnt3 = $tBoxes[3]['count'] ?? 0; @endphp
                                    <a href="{{ route('manajemen-talenta.rekap', ['kuadran' => 3]) }}" class="p-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 transition block group shadow-2xs">
                                        <div class="text-[10px] font-bold text-slate-500 uppercase">Kotak III</div>
                                        <div class="text-xl font-black text-slate-800 my-0.5">{{ $cnt3 }}</div>
                                        <div class="text-[10px] font-semibold text-slate-600 line-clamp-1">K. Di Atas | P. Rendah</div>
                                    </a>

                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-3 pt-2 border-t border-slate-200">
                                    <span>◄ Sumbu X: Kinerja (Rendah → Tinggi) ►</span>
                                    <span>▲ Sumbu Y: Potensi (Tinggi di Atas)</span>
                                </div>
                            </div>

                            {{-- Kolom Kanan (5 Kolom): Highlight Talent Pool Suksesi (Kotak 9 & 8) --}}
                            <div class="lg:col-span-5 bg-slate-50/80 rounded-2xl border border-slate-200 p-4 sm:p-5 shadow-xs flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-base shrink-0 shadow-2xs">
                                                🌟
                                            </div>
                                            <div>
                                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Top Talent Pool Suksesi</h3>
                                                <p class="text-[11px] text-slate-500">Kandidat Kotak IX & VIII Siap Promosi</p>
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            {{ $countPromosi }} Calon
                                        </span>
                                    </div>

                                    @if($topTalentsPreview->count() > 0)
                                        <div class="space-y-2.5">
                                            @foreach($topTalentsPreview as $talent)
                                                @if(is_object($talent) && isset($talent->pegawai_id))
                                                <a href="{{ route('manajemen-talenta.show', $talent->pegawai_id) }}" class="p-2.5 rounded-xl bg-white hover:bg-indigo-50/50 border border-slate-200 hover:border-indigo-300 transition flex items-center justify-between group shadow-2xs">
                                                    <div class="flex items-center gap-2.5 min-w-0">
                                                        <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-xs">
                                                            {{ substr($talent->pegawai?->nama ?? 'A', 0, 1) }}
                                                        </div>
                                                        <div class="min-w-0">
                                                            <h4 class="text-xs font-bold text-slate-800 truncate group-hover:text-indigo-600">
                                                                {{ $talent->pegawai?->nama_lengkap ?? $talent->pegawai?->nama ?? '-' }}
                                                            </h4>
                                                            <p class="text-[10px] text-slate-500 truncate">
                                                                {{ $talent->pegawai?->jabatan?->nama_jabatan ?? 'Pegawai' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="text-right shrink-0 ml-2">
                                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                            K{{ $talent->kuadran_box ?? '-' }}
                                                        </span>
                                                        <div class="text-[9px] text-slate-500 font-mono mt-0.5">
                                                            K:{{ number_format($talent->sumbu_kinerja_nilai ?? 0, 0) }} P:{{ number_format($talent->sumbu_potensi_nilai ?? 0, 0) }}
                                                        </div>
                                                    </div>
                                                </a>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="py-5 px-4 rounded-xl bg-white border border-dashed border-slate-300 text-center shadow-2xs">
                                            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 mx-auto flex items-center justify-center text-lg mb-2 border border-amber-200">
                                                👑
                                            </div>
                                            <h4 class="text-xs font-bold text-slate-800">Belum Ada Kandidat di Kotak IX atau VIII</h4>
                                            <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto leading-relaxed">
                                                Pada periode tahun {{ $talentSummary['tahun'] ?? date('Y') }}, belum ada pegawai di kuadran siap promosi langsung.
                                            </p>
                                            <a href="{{ route('manajemen-talenta.asesmen.index') }}" class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 transition">
                                                <span>✍️</span> Catat Asesmen Kompetensi
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="pt-3 mt-3 border-t border-slate-200 flex items-center justify-between">
                                    <a href="{{ route('manajemen-talenta.suksesi.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-1 group">
                                        <span>Buka Job Matching Suksesi</span>
                                        <span class="group-hover:translate-x-1 transition-transform">→</span>
                                    </a>
                                    <a href="{{ route('manajemen-talenta.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                                        Detail Matriks 9-Kotak
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                @endif

                {{-- ⚖️ ANALISIS JABATAN & KEBUTUHAN FORMASI PEGAWAI (ANJAB & ABK) ⚖️ --}}
                @if(isset($abkSummary))
                    @php
                        $abkItemsList = $abkSummary['items'] ?? [];
                        if ($abkItemsList instanceof \Illuminate\Support\Collection) {
                            $abkItemsList = $abkItemsList->values()->all();
                        } elseif (is_array($abkItemsList)) {
                            $abkItemsList = array_values($abkItemsList);
                        } else {
                            $abkItemsList = [];
                        }
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6"
                         x-data="{
                             filter: 'all',
                             search: '',
                             rawItems: {{ Js::from($abkItemsList) }},
                             get items() {
                                 return Array.isArray(this.rawItems) ? this.rawItems : Object.values(this.rawItems || {});
                             },
                             get filteredItems() {
                                 let f = this.filter;
                                 let s = this.search.toLowerCase().trim();
                                 let list = this.items;
                                 return list.filter(item => {
                                     let matchFilter = true;
                                     if (f === 'defisit') matchFilter = item.is_defisit;
                                     if (f === 'ideal') matchFilter = item.is_ideal;
                                     if (f === 'lebih') matchFilter = item.is_lebih;
                                     if (f === 'dokumen') matchFilter = true;

                                     let matchSearch = !s || (
                                         (item.jabatan && item.jabatan.toLowerCase().includes(s)) ||
                                         (item.unit && item.unit.toLowerCase().includes(s)) ||
                                         (item.kode_anjab && item.kode_anjab.toLowerCase().includes(s))
                                     );
                                     return matchFilter && matchSearch;
                                 });
                             },
                             get filterTitle() {
                                 if (this.filter === 'defisit') return 'Daftar Formasi Jabatan Defisit Pegawai (Perlu Rekrutmen / Mutasi)';
                                 if (this.filter === 'ideal') return 'Daftar Formasi Jabatan Ideal & Terpenuhi (Beban Kerja Seimbang)';
                                 if (this.filter === 'dokumen') return 'Daftar Dokumen Analisis Jabatan Baku (17 Butir PermenPAN-RB No. 1/2020)';
                                 return 'Daftar Seluruh Formasi Jabatan Teranalisis (ABK & Bezetting Riil)';
                             },
                             get filterBadge() {
                                 let list = this.items;
                                 if (this.filter === 'defisit') return '🔴 Formasi Kurang (' + list.filter(i => i.is_defisit).length + ' Jabatan)';
                                 if (this.filter === 'ideal') return '🟢 Formasi Ideal (' + list.filter(i => i.is_ideal).length + ' Jabatan)';
                                 if (this.filter === 'dokumen') return '📑 Dokumen Baku (' + list.length + ' Dokumen)';
                                 return '📋 Semua Formasi (' + list.length + ' Jabatan)';
                             },
                             setFilter(f) {
                                 this.filter = (this.filter === f && f !== 'all') ? 'all' : f;
                             }
                         }">
                        {{-- Header Eksekutif --}}
                        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-4 mb-5 gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-xl border border-indigo-100 shadow-2xs shrink-0">
                                    ⚖️
                                </div>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-lg font-bold text-slate-800">Analisis Jabatan & Formasi Kebutuhan Pegawai (Anjab & ABK)</h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            PermenPAN-RB No. 1/2020
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">Pemetaan standar beban kerja, kebutuhan riil pegawai (bezetting), dan usulan formasi BKN (Klik kartu untuk melihat rincian)</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 transition shadow-2xs">
                                    <span>🏛️</span> Peta Jabatan Digital
                                </a>
                                <a href="{{ route('abk.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition shadow-2xs">
                                    <span>🧮</span> Rekapitulasi ABK
                                </a>
                                <a href="{{ route('anjab.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition shadow-2xs">
                                    <span>📑</span> Katalog Anjab
                                </a>
                            </div>
                        </div>

                        {{-- 4 Kartu Metrik Formasi (INTERAKTIF & BISA DIKLIK) --}}
                        <x-enterprise.stat-grid :cols="4" class="mb-6">
                            {{-- Kartu 1: Rasio Keterisian Formasi (Semua) --}}
                            <x-enterprise.stat-card
                                title="Keterisian Formasi"
                                :value="$abkSummary['totalBezetting']"
                                :suffix="'/ ' . $abkSummary['totalKebutuhan'] . ' Formasi ABK'"
                                icon="scale"
                                color="indigo"
                                :trend="$abkSummary['rasioKeterisian'] . '%'"
                                trendDirection="neutral"
                                :subtitle="'Pegawai Aktif: ' . ($statistik['aktif'] ?? 83)"
                                @click="setFilter('all')"
                                activeWhen="filter === 'all'"
                            >
                                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500" style="width: {{ min(100, $abkSummary['rasioKeterisian']) }}%"></div>
                                </div>
                            </x-enterprise.stat-card>

                            {{-- Kartu 2: Defisit Pegawai (Perlu Rekrutmen / Formasi Kurang) --}}
                            <x-enterprise.stat-card
                                title="Defisit Formasi"
                                :value="$abkSummary['totalDefisit']"
                                suffix="Orang Kurang"
                                icon="alert-triangle"
                                color="rose"
                                trend="Butuh SDM"
                                trendDirection="down"
                                subtitle="Prioritas Usulan CASN (WKE 1.250 jam/thn)"
                                @click="setFilter('defisit')"
                                activeWhen="filter === 'defisit'"
                            />

                            {{-- Kartu 3: Formasi Ideal / Cukup --}}
                            <x-enterprise.stat-card
                                title="Formasi Ideal"
                                :value="$abkSummary['totalIdeal']"
                                suffix="Jabatan Terpenuhi"
                                icon="check-circle"
                                color="emerald"
                                trend="Cukup"
                                trendDirection="up"
                                subtitle="Beban Kerja Seimbang"
                                @click="setFilter('ideal')"
                                activeWhen="filter === 'ideal'"
                            />

                            {{-- Kartu 4: Dokumen & Standar Anjab Terisi --}}
                            <x-enterprise.stat-card
                                title="Dokumen Anjab Baku"
                                :value="$abkSummary['totalDokumen']"
                                suffix="Jabatan Teranalisis"
                                icon="file-text"
                                color="blue"
                                trend="17 Butir"
                                trendDirection="neutral"
                                subtitle="Standar KemenPAN-RB"
                                @click="setFilter('dokumen')"
                                activeWhen="filter === 'dokumen'"
                            />
                        </x-enterprise.stat-grid>

                        {{-- Sub-Grid: Tabel Dinamis Interaktif & Panel Alur Struktur Organisasi --}}
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                            {{-- Tabel Dinamis (2 Kolom Lebar) --}}
                            <div class="lg:col-span-2 border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 pb-3 border-b border-slate-200/80 gap-3">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-base" x-text="filter === 'defisit' ? '🚨' : (filter === 'ideal' ? '🟢' : (filter === 'dokumen' ? '📑' : '📋'))"></span>
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800" x-text="filterTitle"></h4>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-500 mt-0.5 inline-block" x-text="filterBadge"></span>
                                    </div>

                                    {{-- Kolom Pencarian Cepat & Tombol Reset --}}
                                    <div class="flex items-center gap-2">
                                        <div class="relative w-48 sm:w-56">
                                            <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-slate-400 text-xs">
                                                🔍
                                            </span>
                                            <input type="text"
                                                   x-model="search"
                                                   placeholder="Cari jabatan/unit..."
                                                   class="w-full pl-7 pr-6 py-1 text-xs bg-white rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                                            <button type="button"
                                                    x-show="search.length > 0"
                                                    @click="search = ''"
                                                    class="absolute inset-y-0 right-0 pr-2 flex items-center text-slate-400 hover:text-slate-600 text-xs">
                                                ✕
                                            </button>
                                        </div>
                                        <button type="button"
                                                x-show="filter !== 'all' || search.length > 0"
                                                @click="filter = 'all'; search = ''"
                                                class="px-2.5 py-1 text-[11px] font-semibold text-slate-600 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 transition">
                                            Reset Filter
                                        </button>
                                    </div>
                                </div>

                                {{-- Isi Tabel Dinamis --}}
                                <div class="overflow-x-auto max-h-[360px] overflow-y-auto border border-slate-200 rounded-lg bg-white shadow-2xs">
                                    <table class="w-full text-xs text-left text-slate-600">
                                        <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] border-b border-slate-200 sticky top-0 z-10 shadow-2xs">
                                            <tr>
                                                <th class="px-3 py-2.5 text-center w-10">No</th>
                                                <th class="px-3 py-2.5">Nama Jabatan & Unit Kerja</th>
                                                <th class="px-3 py-2.5 text-center">Kelas</th>
                                                <th class="px-3 py-2.5 text-center">Kebutuhan</th>
                                                <th class="px-3 py-2.5 text-center">Pegawai Riil</th>
                                                <th class="px-3 py-2.5 text-center">Status Formasi</th>
                                                <th class="px-3 py-2.5 text-right">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <template x-for="(item, index) in filteredItems" :key="item.id">
                                                <tr class="hover:bg-slate-50 transition">
                                                    <td class="px-3 py-2.5 text-center font-medium text-slate-500" x-text="index + 1"></td>
                                                    <td class="px-3 py-2.5">
                                                        <div class="font-bold text-slate-900" x-text="item.jabatan"></div>
                                                        <div class="text-[10px] text-slate-500">
                                                            <span x-text="item.unit"></span>
                                                            <span class="text-slate-300">•</span>
                                                            <span class="font-mono text-slate-400" x-text="item.kode_anjab"></span>
                                                        </div>
                                                    </td>
                                                    <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200" x-text="'Grade ' + item.kelas">
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2.5 text-center font-bold text-slate-800" x-text="item.kebutuhan"></td>
                                                    <td class="px-3 py-2.5 text-center font-semibold text-slate-700" x-text="item.bezetting"></td>
                                                    <td class="px-3 py-2.5 text-center whitespace-nowrap">
                                                        <template x-if="item.is_defisit">
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-200" x-text="'🔴 Kurang (' + Math.abs(item.selisih) + ')'"></span>
                                                        </template>
                                                        <template x-if="item.is_ideal">
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">🟢 Ideal (Cukup)</span>
                                                        </template>
                                                        <template x-if="item.is_lebih">
                                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200" x-text="'🟡 Lebih (+' + item.selisih + ')'"></span>
                                                        </template>
                                                    </td>
                                                    <td class="px-3 py-2.5 text-right whitespace-nowrap space-x-1">
                                                        @if(Auth::user()->canManageAnjabAbk())
                                                            <a :href="item.url_abk" class="inline-flex items-center px-2 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold text-[10px] border border-blue-200 transition">
                                                                Kelola ABK &rarr;
                                                            </a>
                                                        @else
                                                            <a :href="item.url_abk" class="inline-flex items-center px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold text-[10px] border border-slate-200 transition">
                                                                Rincian ABK &rarr;
                                                            </a>
                                                        @endif
                                                        <a :href="item.url_anjab" class="inline-flex items-center px-2 py-1 rounded bg-white text-slate-600 hover:bg-slate-50 font-semibold text-[10px] border border-slate-300 transition" title="Lihat 17 Butir Anjab">
                                                            Dokumen 👁️
                                                        </a>
                                                    </td>
                                                </tr>
                                            </template>

                                            {{-- Pesan saat hasil kosong --}}
                                            <tr x-show="filteredItems.length === 0">
                                                <td colspan="7" class="px-4 py-8 text-center text-slate-400 text-xs">
                                                    <span class="text-2xl block mb-1">🔍</span>
                                                    <p class="font-semibold text-slate-600">Tidak ada jabatan yang sesuai dengan filter atau kata kunci.</p>
                                                    <button type="button" @click="filter = 'all'; search = ''" class="mt-2 text-indigo-600 font-bold hover:underline">
                                                        &larr; Tampilkan Semua Formasi Jabatan
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Footer Tabel dengan Tautan Matriks Lengkap --}}
                                <div class="mt-3 pt-2 border-t border-slate-200/80 flex items-center justify-between text-xs">
                                    <span class="text-slate-500">
                                        Menampilkan <strong class="text-slate-800" x-text="filteredItems.length"></strong> dari <strong class="text-slate-900" x-text="items.length"></strong> jabatan teranalisis
                                    </span>
                                    <a href="{{ route('abk.index') }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1">
                                        Buka Matriks Lengkap Beban Kerja (ABK) &rarr;
                                    </a>
                                </div>
                            </div>

                            {{-- Peta Struktur & Rekomendasi Cepat (1 Kolom) --}}
                            <div class="rounded-xl p-5 flex flex-col justify-between shadow-md"
                                 style="background-color: #0f172a !important; background-image: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #172554 100%) !important; border: 1px solid #334155 !important; color: #ffffff !important;">
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-xl">🏛️</span>
                                        <h4 class="text-xs font-bold uppercase tracking-wider" style="color: #93c5fd !important;">Peta Struktur & Alur Jabatan</h4>
                                    </div>
                                    <h5 class="text-sm font-bold mb-2 leading-snug" style="color: #ffffff !important;">
                                        Struktur Hirarki Organisasi Fakultas Keperawatan UNRI
                                    </h5>
                                    <p class="text-[11px] leading-relaxed mb-4" style="color: #cbd5e1 !important;">
                                        Bagan interaktif 3 Sayap Wakil Dekan, Jurusan Klinik & Komunitas, Program Studi (S1, S2, S3, Ners), SPMF/GPM, serta 27 Unit/Lab/KJFD.
                                    </p>
                                    <div class="space-y-2 text-xs">
                                        <div class="flex items-center justify-between p-2.5 rounded-lg" style="background-color: rgba(255, 255, 255, 0.08) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                                            <span style="color: #cbd5e1 !important;">Total Unsur Pimpinan:</span>
                                            <span class="font-bold" style="color: #6ee7b7 !important;">Dekan & 3 Wadek</span>
                                        </div>
                                        <div class="flex items-center justify-between p-2.5 rounded-lg" style="background-color: rgba(255, 255, 255, 0.08) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                                            <span style="color: #cbd5e1 !important;">Pilar Penunjang & Unit:</span>
                                            <span class="font-bold" style="color: #93c5fd !important;">27 Lab / Unit / KJFD</span>
                                        </div>
                                        <div class="flex items-center justify-between p-2.5 rounded-lg" style="background-color: rgba(255, 255, 255, 0.08) !important; border: 1px solid rgba(255, 255, 255, 0.12) !important;">
                                            <span style="color: #cbd5e1 !important;">Total Formasi Disusun:</span>
                                            <span class="font-bold" style="color: #fde047 !important;">{{ $abkSummary['totalDokumen'] }} Jabatan</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 pt-3 space-y-2" style="border-top: 1px solid rgba(255, 255, 255, 0.15) !important;">
                                    <a href="{{ route('anjab.peta-jabatan') }}" class="w-full py-2.5 px-3 rounded-xl font-bold text-xs flex items-center justify-center gap-2 shadow transition hover:opacity-95" style="background-color: #2563eb !important; color: #ffffff !important; text-decoration: none !important;">
                                        <span>Buka Peta Jabatan Digital</span> &rarr;
                                    </a>
                                    @if(Auth::user()->canManageAnjabAbk())
                                        <a href="{{ route('anjab.create') }}" class="w-full py-2 px-3 rounded-xl font-semibold text-xs flex items-center justify-center gap-1.5 transition hover:bg-white/20" style="background-color: rgba(255, 255, 255, 0.12) !important; color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.25) !important; text-decoration: none !important;">
                                            <span class="font-bold text-sm">+</span> Tambah Dokumen Anjab Baru
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                @endif

                {{-- 📊 MONITORING KELENGKAPAN DATA PEGAWAI FAKULTAS (KHUSUS ADMIN & PIMPINAN) 📊 --}}
                @if(isset($facultyCompleteness))
                    @php
                        $itemsJson = collect($facultyCompleteness['pegawai_scores'] ?? [])->map(function($item) {
                            $peg = data_get($item, 'pegawai');
                            $pegId = data_get($peg, 'id');
                            $score = (int)data_get($item, 'score', 0);
                            return [
                                'id' => $pegId,
                                'nama' => data_get($peg, 'nama_lengkap') ?? data_get($peg, 'nama') ?? '-',
                                'nip' => data_get($peg, 'nip') ?? '-',
                                'jabatan' => data_get($peg, 'jabatan_nama') ?? data_get($peg, 'jabatan.nama_jabatan') ?? '-',
                                'unit' => data_get($peg, 'unit_nama') ?? data_get($peg, 'unitKerja.nama_unit') ?? '-',
                                'score' => $score,
                                'progress_color' => data_get($item, 'progress_color', 'bg-blue-500'),
                                'badge_color' => data_get($item, 'badge_color', 'bg-slate-100 text-slate-700 border-slate-200'),
                                'status_label' => data_get($item, 'status_label', '-'),
                                'missing_count' => (int)data_get($item, 'missing_count', 0),
                                'category' => ($score >= 100) ? 'complete' : (($score >= 50) ? 'moderate' : 'low'),
                                'url' => $pegId ? route('pegawai.show', $pegId) : '#',
                            ];
                        })->values();
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6"
                         x-data="{ 
                             filter: 'all', 
                             search: '',
                             showTable: true,
                             page: 1,
                             perPage: 10,
                             rawItems: {{ Js::from($itemsJson) }},
                             get items() {
                                 return Array.isArray(this.rawItems) ? this.rawItems : Object.values(this.rawItems || {});
                             },
                             counts: {
                                 all: {{ count($facultyCompleteness['pegawai_scores'] ?? []) }},
                                 complete: {{ $facultyCompleteness['total_complete'] ?? 0 }},
                                 moderate: {{ $facultyCompleteness['total_moderate'] ?? 0 }},
                                 low: {{ $facultyCompleteness['total_low'] ?? 0 }}
                             },
                             get filteredItems() {
                                 let f = this.filter;
                                 let s = this.search.toLowerCase().trim();
                                 let list = this.items;
                                 return list.filter(item => {
                                     let matchFilter = (f === 'all' || item.category === f);
                                     let matchSearch = !s || (
                                         (item.nama && item.nama.toLowerCase().includes(s)) ||
                                         (item.nip && item.nip.toLowerCase().includes(s)) ||
                                         (item.jabatan && item.jabatan.toLowerCase().includes(s)) ||
                                         (item.unit && item.unit.toLowerCase().includes(s))
                                     );
                                     return matchFilter && matchSearch;
                                 });
                             },
                             get totalPages() {
                                 if (this.perPage === 'all') return 1;
                                 let pp = parseInt(this.perPage);
                                 return Math.ceil(this.filteredItems.length / pp) || 1;
                             },
                             get paginatedItems() {
                                 if (this.perPage === 'all') return this.filteredItems;
                                 let pp = parseInt(this.perPage);
                                 let start = (this.page - 1) * pp;
                                 return this.filteredItems.slice(start, start + pp);
                             },
                             setFilter(cat) {
                                 this.filter = cat;
                                 this.page = 1;
                             },
                             nextPage() {
                                 if (this.page < this.totalPages) this.page++;
                             },
                             prevPage() {
                                 if (this.page > 1) this.page--;
                             }
                         }">
                        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-4 mb-4 gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">📊</span>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800">Monitoring Kelengkapan Data Pegawai Fakultas</h3>
                                    <p class="text-xs text-slate-500">Evaluasi pemenuhan dokumen SK & data profil seluruh pegawai (Klik kartu untuk filter cepat)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200 shrink-0">
                                    <span class="text-xs font-bold text-slate-600">Rata-Rata Fakultas:</span>
                                    <span class="text-base font-black text-blue-600">{{ $facultyCompleteness['average_score'] }}%</span>
                                </div>
                                <button type="button" 
                                        @click="showTable = !showTable"
                                        class="px-3 py-1.5 text-xs font-bold rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 transition flex items-center gap-1.5 text-slate-700 select-none">
                                    <span x-text="showTable ? '▲ Sembunyikan Tabel' : '▼ Tampilkan Tabel'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Ringkasan 3 Kategori yang Dapat Diklik (Interactive Filter Cards) --}}
                        <x-enterprise.stat-grid :cols="3" class="mb-5">
                            {{-- Kartu 1: 100% Lengkap --}}
                            <x-enterprise.stat-card
                                title="100% Lengkap (Sempurna)"
                                :value="$facultyCompleteness['total_complete']"
                                suffix="Pegawai"
                                icon="check-circle"
                                color="emerald"
                                subtitle="Klik untuk filter tabel"
                                @click="setFilter(filter === 'complete' ? 'all' : 'complete')"
                                activeWhen="filter === 'complete'"
                            />

                            {{-- Kartu 2: Cukup Lengkap (50% - 99%) --}}
                            <x-enterprise.stat-card
                                title="Cukup Lengkap (50% - 99%)"
                                :value="$facultyCompleteness['total_moderate']"
                                suffix="Pegawai"
                                icon="clock"
                                color="amber"
                                subtitle="Klik untuk filter tabel"
                                @click="setFilter(filter === 'moderate' ? 'all' : 'moderate')"
                                activeWhen="filter === 'moderate'"
                            />

                            {{-- Kartu 3: Perlu Dilengkapi (< 50%) --}}
                            <x-enterprise.stat-card
                                title="Perlu Dilengkapi (< 50%)"
                                :value="$facultyCompleteness['total_low']"
                                suffix="Pegawai"
                                icon="alert-triangle"
                                color="rose"
                                subtitle="Klik untuk filter tabel"
                                @click="setFilter(filter === 'low' ? 'all' : 'low')"
                                activeWhen="filter === 'low'"
                            />
                        </x-enterprise.stat-grid>

                        {{-- Konten Tabel (Collapsible) --}}
                        <div x-show="showTable" x-transition.duration.200ms>
                            
                            {{-- Bilah Status Filter, Paginasi Baris, & Pencarian Cepat --}}
                            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-3 pt-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-semibold text-slate-500">Tampilan Data:</span>
                                    
                                    <button type="button" 
                                            @click="setFilter('all')"
                                            :class="filter === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                            class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                        <span>Semua Pegawai</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'all' ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-600'" x-text="counts.all"></span>
                                    </button>

                                    <button type="button" 
                                            @click="setFilter('complete')"
                                            :class="filter === 'complete' ? 'bg-emerald-700 text-white shadow-xs ring-1 ring-emerald-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                            class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                        <span>💎 100% Lengkap</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'complete' ? 'bg-emerald-800 text-white' : 'bg-emerald-200 text-emerald-800'" x-text="counts.complete"></span>
                                    </button>

                                    <button type="button" 
                                            @click="setFilter('moderate')"
                                            :class="filter === 'moderate' ? 'bg-amber-600 text-white shadow-xs ring-1 ring-amber-500' : 'bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200'"
                                            class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                        <span>🟡 Cukup Lengkap</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'moderate' ? 'bg-amber-700 text-white' : 'bg-amber-200 text-amber-800'" x-text="counts.moderate"></span>
                                    </button>

                                    <button type="button" 
                                            @click="setFilter('low')"
                                            :class="filter === 'low' ? 'bg-rose-700 text-white shadow-xs ring-1 ring-rose-600' : 'bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200'"
                                            class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                        <span>🔴 Perlu Dilengkapi</span>
                                        <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'low' ? 'bg-rose-800 text-white' : 'bg-rose-200 text-rose-800'" x-text="counts.low"></span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-2 w-full lg:w-auto">
                                    {{-- Selector Jumlah Baris per Halaman --}}
                                    <div class="flex items-center gap-1.5 text-xs text-slate-500 shrink-0">
                                        <span>Baris:</span>
                                        <select x-model="perPage" @change="page = 1" class="py-1 px-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:ring-1 focus:ring-blue-500 font-semibold text-slate-700">
                                            <option value="5">5</option>
                                            <option value="10">10</option>
                                            <option value="20">20</option>
                                            <option value="all">Semua</option>
                                        </select>
                                    </div>

                                    {{-- Input Pencarian Nama / NIP --}}
                                    <div class="relative flex-1 sm:w-60">
                                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs">
                                            🔍
                                        </span>
                                        <input type="text" 
                                               x-model="search" 
                                               @input="page = 1"
                                               placeholder="Cari Nama / NIP..." 
                                               class="w-full pl-8 pr-7 py-1.5 text-xs bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                                        <button type="button" 
                                                x-show="search.length > 0" 
                                                @click="search = ''; page = 1" 
                                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-xs font-bold">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Tabel Monitoring Kelengkapan Pegawai (Ketinggian Dibatasi Rapi max-h-[380px] dengan Sticky Header) --}}
                            <div class="overflow-x-auto max-h-[380px] overflow-y-auto border border-slate-200 rounded-xl relative shadow-2xs">
                                <table class="w-full text-xs text-left text-slate-600">
                                    <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] sticky top-0 z-10 border-b border-slate-200 shadow-2xs">
                                        <tr>
                                            <th class="px-4 py-2.5">Nama Pegawai & NIP</th>
                                            <th class="px-4 py-2.5">Unit Kerja / Jabatan</th>
                                            <th class="px-4 py-2.5">Persentase</th>
                                            <th class="px-4 py-2.5">Status Kelengkapan</th>
                                            <th class="px-4 py-2.5 text-center">Item Belum Lengkap</th>
                                            <th class="px-4 py-2.5 text-right">Aksi Detail</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <template x-for="item in paginatedItems" :key="item.id">
                                            <tr class="hover:bg-slate-50 transition">
                                                <td class="px-4 py-2.5">
                                                    <div class="font-bold text-slate-900" x-text="item.nama"></div>
                                                    <div class="text-[10px] text-slate-500 font-mono" x-text="'NIP: ' + item.nip"></div>
                                                </td>
                                                <td class="px-4 py-2.5">
                                                    <div class="font-semibold text-slate-800" x-text="item.jabatan"></div>
                                                    <div class="text-[10px] text-slate-500" x-text="item.unit"></div>
                                                </td>
                                                <td class="px-4 py-2.5 whitespace-nowrap">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                                            <div :class="item.progress_color" class="h-2 rounded-full transition-all duration-300" :style="'width: ' + item.score + '%'"></div>
                                                        </div>
                                                        <span class="font-black text-slate-900 text-xs" x-text="item.score + '%'"></span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2.5 whitespace-nowrap">
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border" :class="item.badge_color" x-text="item.status_label"></span>
                                                </td>
                                                <td class="px-4 py-2.5 text-center">
                                                    <template x-if="item.missing_count > 0">
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-900" x-text="'⚠️ ' + item.missing_count + ' Item Belum'"></span>
                                                    </template>
                                                    <template x-if="item.missing_count === 0">
                                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-900">✅ Complete</span>
                                                    </template>
                                                </td>
                                                <td class="px-4 py-2.5 text-right">
                                                    <a :href="item.url" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition border border-slate-300">
                                                        Lihat Profil &rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        </template>

                                        {{-- Baris Pesan Ketika Hasil Pencarian / Filter Kosong --}}
                                        <tr x-show="filteredItems.length === 0">
                                            <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                                                <span class="text-2xl block mb-1">🔍</span>
                                                <p class="font-semibold text-slate-600">Tidak ada pegawai yang sesuai dengan filter atau kata kunci pencarian.</p>
                                                <button type="button" @click="setFilter('all'); search = ''" class="mt-2 text-blue-600 font-bold hover:underline">
                                                    &larr; Tampilkan Semua Pegawai
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Bilah Navigasi Paginasi & Tautan Cepat --}}
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-3 pt-2 text-xs text-slate-600">
                                <div class="flex items-center gap-2">
                                    <span>
                                        Menampilkan 
                                        <strong class="text-slate-800" x-text="filteredItems.length === 0 ? 0 : (perPage === 'all' ? 1 : (page - 1) * parseInt(perPage) + 1)"></strong> 
                                        sampai 
                                        <strong class="text-slate-800" x-text="perPage === 'all' ? filteredItems.length : Math.min(page * parseInt(perPage), filteredItems.length)"></strong> 
                                        dari 
                                        <strong class="text-slate-900" x-text="filteredItems.length"></strong> 
                                        pegawai
                                    </span>
                                </div>

                                <div class="flex items-center gap-2" x-show="totalPages > 1 && perPage !== 'all'">
                                    <button type="button" 
                                            @click="prevPage" 
                                            :disabled="page <= 1" 
                                            class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition text-xs">
                                        &larr; Sebelumnya
                                    </button>
                                    <span class="px-2 font-semibold text-slate-700">
                                        Halaman <span x-text="page"></span> / <span x-text="totalPages"></span>
                                    </span>
                                    <button type="button" 
                                            @click="nextPage" 
                                            :disabled="page >= totalPages" 
                                            class="px-3 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition text-xs">
                                        Selanjutnya &rarr;
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                                <span class="text-slate-400">Data kelengkapan diperbarui otomatis secara real-time.</span>
                                @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                                    <a href="{{ route('pegawai.index') }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1">
                                        Buka Seluruh Data di Master Pegawai &rarr;
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
