<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(Auth::user()->hasRole('pegawai'))
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
                                <h1 class="text-xl md:text-2xl font-bold tracking-tight" style="color: #ffffff !important;">Selamat Datang, {{ $p->nama_lengkap ?? $p->nama ?? Auth::user()->name }}!</h1>
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
                            @endif
                        </div>
                    </div>
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
                                    <div class="w-11 h-11 rounded-xl {{ $todayAttendance ? ($todayAttendance->check_out_time ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-blue-50 text-blue-600 border border-blue-200') : 'bg-amber-50 text-amber-600 border border-amber-200' }} flex items-center justify-center text-xl shrink-0 shadow-2xs">
                                        📍
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
                                    <span>📸 Presensi Masuk Sekarang</span>
                                @elseif(!$todayAttendance->check_out_time)
                                    <span>🚪 Presensi Pulang (Check-Out)</span>
                                @else
                                    <span>✅ Buka Menu Presensi</span>
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
                                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200 flex items-center justify-center text-xl shrink-0 shadow-2xs">
                                            📝
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
                                    <span>+ Catat Aktivitas</span>
                                </a>
                                <a href="{{ route('logbook.index') }}" class="px-3.5 py-2.5 rounded-xl font-semibold text-xs text-slate-700 bg-slate-100 hover:bg-slate-200 transition shrink-0">
                                    Buka Logbook
                                </a>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- 👥 PUSAT TINDAKAN VERIFIKASI ATASAN LANGSUNG (JIKA PEGAWAI ADALAH ATASAN) --}}
                @if(isset($isAtasan) && $isAtasan)
                    <div class="bg-gradient-to-r from-blue-900 to-indigo-900 rounded-2xl p-5 text-white shadow-md">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 border-b border-blue-800 pb-3 mb-4">
                            <div class="flex items-center gap-2.5">
                                <span class="text-2xl">👥</span>
                                <div>
                                    <h3 class="font-bold text-base text-white">Meja Verifikasi Atasan Langsung</h3>
                                    <p class="text-xs text-blue-200">Pengawasan dan pengesahan aktivitas logbook harian serta cuti pegawai di bawah tanggung jawab Anda.</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <a href="{{ route('admin.logbook.index', ['status' => 'diajukan']) }}" class="p-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 transition flex items-center justify-between group">
                                <div>
                                    <div class="text-xs font-semibold text-blue-200 uppercase tracking-wider">Logbook Bawahan Menunggu</div>
                                    <div class="text-2xl font-black text-white mt-1">{{ $pendingLogbookCount ?? 0 }}</div>
                                    <div class="text-xs text-blue-300 mt-0.5 group-hover:underline">Periksa & Berikan Pengesahan →</div>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-indigo-500/30 text-white flex items-center justify-center text-xl">
                                    📝
                                </div>
                            </a>

                            <a href="{{ route('pengajuan-cuti.index', ['status' => 'Menunggu Persetujuan']) }}" class="p-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 transition flex items-center justify-between group">
                                <div>
                                    <div class="text-xs font-semibold text-blue-200 uppercase tracking-wider">Permohonan Cuti Bawahan</div>
                                    <div class="text-2xl font-black text-white mt-1">{{ $pendingCutiCount ?? 0 }}</div>
                                    <div class="text-xs text-blue-300 mt-0.5 group-hover:underline">Tinjau & Berikan Keputusan →</div>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-amber-500/30 text-white flex items-center justify-center text-xl">
                                    🏖️
                                </div>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- 📊 KARTU PERSENTASE KELENGKAPAN DATA PEGAWAI MANDIRI --}}
                @if(isset($completenessData))
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
                        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border-b border-slate-100 pb-4 mb-4">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xl">📊</span>
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
                                    <span>⚠️</span> Harap Lengkapi {{ count($completenessData['missing_items']) }} Item Berikut Agar Data Anda 100% Terverifikasi:
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                    @foreach($completenessData['missing_items'] as $item)
                                        <div class="flex items-center justify-between p-2.5 rounded-lg bg-white border border-amber-200/60 shadow-2xs">
                                            <div class="flex items-center gap-2">
                                                <span class="text-rose-500 text-sm font-bold">❌</span>
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
                                <span class="text-2xl">🎉</span>
                                <div>
                                    <h4 class="font-bold text-xs">Selamat! Data Profil Anda Sudah 100% Lengkap.</h4>
                                    <p class="text-[11px] text-emerald-700 mt-0.5">Seluruh berkas SK, identitas, dan riwayat karir Anda telah memenuhi standar kelengkapan dokumen SIMPEG.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- 4 KARTU STATISTIK PROFIL SAYA --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    
                    {{-- Kartu 1: Status Kepegawaian --}}
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status Kepegawaian</span>
                            <h4 class="text-lg font-bold text-slate-800 mt-1">{{ $p->jenis_pegawai ?? 'Pegawai' }}</h4>
                            <p class="text-xs text-blue-600 font-semibold mt-0.5">{{ $p->status_asn ?? 'ASN' }} ({{ $p->status_pegawai ?? 'Aktif' }})</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                            👨‍🏫
                        </div>
                    </div>

                    {{-- Kartu 2: Jabatan Aktif --}}
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Jabatan Terkini</span>
                            <h4 class="text-base font-bold text-slate-800 mt-1 line-clamp-1" title="{{ $p->jabatan?->nama_jabatan ?? '-' }}">{{ $p->jabatan?->nama_jabatan ?? '-' }}</h4>
                            <p class="text-xs text-slate-500 truncate mt-0.5" title="{{ $p->unitKerja?->nama_unit ?? '-' }}">{{ $p->unitKerja?->nama_unit ?? '-' }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                            🧑‍💼
                        </div>
                    </div>

                    {{-- Kartu 3: Pangkat / Golongan --}}
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Pangkat / Golongan</span>
                            <h4 class="text-base font-bold text-slate-800 mt-1">{{ $p->golongan?->nama_golongan ?? '-' }}</h4>
                            <p class="text-xs text-slate-500 font-mono mt-0.5">TMT: {{ isset($p->tmt_pangkat_terakhir) ? \Carbon\Carbon::parse($p->tmt_pangkat_terakhir)->format('d-m-Y') : '-' }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                            🎖️
                        </div>
                    </div>

                    {{-- Kartu 4: Sisa Cuti Tahunan --}}
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Sisa Cuti Tahunan</span>
                            <h4 class="text-2xl font-black text-indigo-600 mt-1">{{ $p->sisa_cuti_tahunan ?? 12 }} Hari</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Kuota Tahun {{ date('Y') }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                            🏖️
                        </div>
                    </div>

                </div>

                {{-- 🔔 PUSAT REMINDER KARIR PRIBADI SAYA 🔔 --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-2 border-b border-slate-100 pb-3 mb-4">
                        <span class="text-xl">🔔</span>
                        <h3 class="font-bold text-base text-slate-800">Pusat Reminder & Target Karir Pribadi Saya</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        
                        {{-- KGB Saya --}}
                        <div class="p-4 rounded-xl border border-amber-200 bg-amber-50/60 flex flex-col justify-between">
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
                            <div class="mt-3 text-[11px] font-semibold text-amber-800 bg-amber-100 p-2 rounded-lg text-center">
                                @if(isset($p->kgb_berikutnya))
                                    @php
                                        $diffKgb = (int) round(\Carbon\Carbon::now()->diffInMonths(\Carbon\Carbon::parse($p->kgb_berikutnya), false));
                                    @endphp
                                    @if($diffKgb <= 0)
                                        ⚠️ Jatuh Tempo KGB! Silakan ajukan berkas.
                                    @else
                                        @php
                                            $thnKgb = floor($diffKgb / 12);
                                            $blnKgb = $diffKgb % 12;
                                            $labelKgb = $thnKgb > 0 ? "{$thnKgb} Thn " . ($blnKgb > 0 ? "{$blnKgb} Bln" : "") : "{$blnKgb} Bulan";
                                        @endphp
                                        ⏳ Est. {{ trim($labelKgb) }} ({{ $diffKgb }} Bulan)
                                    @endif
                                @else
                                    Info KGB belum diset.
                                @endif
                            </div>
                        </div>

                        {{-- KP Saya --}}
                        <div class="p-4 rounded-xl border border-emerald-200 bg-emerald-50/60 flex flex-col justify-between">
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
                            <div class="mt-3 text-[11px] font-semibold text-emerald-800 bg-emerald-100 p-2 rounded-lg text-center">
                                @if(isset($p->kp_berikutnya))
                                    @php
                                        $diffKp = (int) round(\Carbon\Carbon::now()->diffInMonths(\Carbon\Carbon::parse($p->kp_berikutnya), false));
                                    @endphp
                                    @if($diffKp <= 0)
                                        ⚠️ Siap Pengajuan Kenaikan Pangkat!
                                    @else
                                        @php
                                            $thnKp = floor($diffKp / 12);
                                            $blnKp = $diffKp % 12;
                                            $labelKp = $thnKp > 0 ? "{$thnKp} Thn " . ($blnKp > 0 ? "{$blnKp} Bln" : "") : "{$blnKp} Bulan";
                                        @endphp
                                        ⏳ Est. {{ trim($labelKp) }} ({{ $diffKp }} Bulan)
                                    @endif
                                @else
                                    Info KP belum diset.
                                @endif
                            </div>
                        </div>

                        {{-- Satyalancana Saya --}}
                        <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/60 flex flex-col justify-between">
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
                            <div class="mt-3 text-[11px] font-semibold text-indigo-800 bg-indigo-100 p-2 rounded-lg text-center">
                                🎖️ Perolehan Kehormatan
                            </div>
                        </div>

                        {{-- KARTU KE-4: KONDISIONAL STR/SIP ATAU MASA KONTRAK PPPK ATAU SKP --}}
                        @php
                            $activeStr = (isset($p->riwayatStrSip) && $p->riwayatStrSip->count() > 0) ? $p->riwayatStrSip->first() : null;
                            $isPppkOrContract = ($p->jenis_pegawai === 'PPPK' || $p->jenis_pegawai === 'PHL' || !empty($p->tanggal_kontrak_selesai));
                        @endphp

                        @if($activeStr)
                            {{-- 1. STR & SIP (Khusus Tenaga Medis / Dosen Klinis) --}}
                            <div class="p-4 rounded-xl border border-sky-200 bg-sky-50/60 flex flex-col justify-between">
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
                                <div class="mt-3 text-[11px] font-semibold text-sky-800 bg-sky-100 p-2 rounded-lg text-center">
                                    🩺 Izin Praktik / Profesi
                                </div>
                            </div>
                        @elseif($isPppkOrContract)
                            {{-- 2. Kontrak Kerja PPPK / PHL --}}
                            <div class="p-4 rounded-xl border border-cyan-200 bg-cyan-50/60 flex flex-col justify-between">
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
                                <div class="mt-3 text-[11px] font-semibold text-cyan-800 bg-cyan-100 p-2 rounded-lg text-center">
                                    @if(isset($p->tanggal_kontrak_selesai))
                                        @php
                                            $diffKontrak = (int) round(\Carbon\Carbon::now()->diffInMonths(\Carbon\Carbon::parse($p->tanggal_kontrak_selesai), false));
                                        @endphp
                                        @if($diffKontrak <= 0)
                                            ⚠️ Masa Kontrak Berakhir!
                                        @else
                                            ⏳ Sisa {{ $diffKontrak }} Bulan Masa Kontrak
                                        @endif
                                    @else
                                        Perjanjian Kerja Aktif
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- 3. Sasaran Kinerja Pegawai (SKP) --}}
                            <div class="p-4 rounded-xl border border-purple-200 bg-purple-50/60 flex flex-col justify-between">
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
                                <div class="mt-3 text-[11px] font-semibold text-purple-800 bg-purple-100 p-2 rounded-lg text-center">
                                    🎯 Sasaran Kinerja Pegawai
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

                {{-- Ucapan Selamat Datang --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border border-gray-200">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold">Selamat Datang, {{ Auth::user()->name ?? 'Administrator' }}!</h3>
                        <p class="text-sm text-gray-500 mt-1">Berikut adalah ringkasan data sistem informasi kepegawaian dan operasional harian saat ini.</p>
                    </div>
                </div>

                {{-- 🚨 PUSAT TINDAKAN & OPERASIONAL HARIAN (ACTION ITEMS) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    {{-- 1. Pending Cuti --}}
                    <a href="{{ route('pengajuan-cuti.index') }}" class="p-4 rounded-2xl bg-amber-50 border border-amber-200 shadow-sm hover:shadow-md transition flex items-center justify-between group">
                        <div>
                            <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Cuti Menunggu</div>
                            <div class="text-2xl font-black text-amber-900 mt-1">{{ $pendingCutiCount ?? 0 }}</div>
                            <div class="text-[11px] text-amber-600 mt-0.5 group-hover:underline">Perlu Persetujuan →</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-200/60 text-amber-800 flex items-center justify-center text-2xl">
                            🏖️
                        </div>
                    </a>

                    {{-- 2. Pending Logbook --}}
                    <a href="{{ route('admin.logbook.index', ['status' => 'diajukan']) }}" class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 shadow-sm hover:shadow-md transition flex items-center justify-between group">
                        <div>
                            <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Logbook Menunggu</div>
                            <div class="text-2xl font-black text-indigo-900 mt-1">{{ $pendingLogbookCount ?? 0 }}</div>
                            <div class="text-[11px] text-indigo-600 mt-0.5 group-hover:underline">Perlu Diverifikasi →</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-200/60 text-indigo-800 flex items-center justify-center text-2xl">
                            📝
                        </div>
                    </a>

                    {{-- 3. Presensi Hari Ini --}}
                    <a href="{{ route('admin.presensi.index') }}" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 shadow-sm hover:shadow-md transition flex items-center justify-between group">
                        <div>
                            <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Presensi Hari Ini</div>
                            <div class="text-2xl font-black text-emerald-900 mt-1">{{ $todayPresentCount ?? 0 }}</div>
                            <div class="text-[11px] text-emerald-600 mt-0.5 group-hover:underline">Pegawai Hadir →</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-200/60 text-emerald-800 flex items-center justify-center text-2xl">
                            📍
                        </div>
                    </a>

                    {{-- 4. Total Jam Logbook Bulan Ini --}}
                    <a href="{{ route('admin.logbook.index') }}" class="p-4 rounded-2xl bg-blue-50 border border-blue-200 shadow-sm hover:shadow-md transition flex items-center justify-between group">
                        <div>
                            <div class="text-xs font-bold text-blue-700 uppercase tracking-wider">Kinerja Bulan Ini</div>
                            <div class="text-2xl font-black text-blue-900 mt-1">{{ $adminLogbookStats['total_jam'] ?? 0 }} Jam</div>
                            <div class="text-[11px] text-blue-600 mt-0.5 group-hover:underline">{{ $adminLogbookStats['total'] ?? 0 }} Aktivitas Terdata →</div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-200/60 text-blue-800 flex items-center justify-center text-2xl">
                            ⏱️
                        </div>
                    </a>
                </div>

                {{-- BARIS 1: STATISTIK UTAMA PEGAWAI --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <a href="{{ route('pegawai.index') }}" class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group" style="background-color: #1d4ed8;">
                        <div>
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90 group-hover:underline">Total Pegawai</h3>
                            <p class="text-3xl font-bold mt-2">{{ $statistik['total'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </a>

                    <a href="{{ route('pegawai.index', ['filter' => 'aktif']) }}" class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group" style="background-color: #15803d;">
                        <div>
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90 group-hover:underline">Pegawai Aktif</h3>
                            <p class="text-3xl font-bold mt-2">{{ $statistik['aktif'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </a>

                    <a href="{{ route('pegawai.index', ['filter' => 'pns']) }}" class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group" style="background-color: #0284c7;">
                        <div>
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90 group-hover:underline">Status ASN</h3>
                            <p class="text-3xl font-bold mt-2">{{ $statistik['asn'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 012-2h2a2 2 0 012 2v1m-6 0h6"/></svg>
                        </div>
                    </a>

                    <a href="{{ route('pegawai.index', ['filter' => 'pensiun']) }}" class="p-6 rounded-lg shadow-sm text-white flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group" style="background-color: #b45309;">
                        <div>
                            <h3 class="text-sm font-medium uppercase tracking-wider opacity-90 group-hover:underline">Pegawai Pensiun</h3>
                            <p class="text-3xl font-bold mt-2">{{ $statistik['pensiun'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-white bg-opacity-20 rounded-full group-hover:bg-opacity-30 transition">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                    </a>
                </div>

                {{-- 📊 MONITORING KELENGKAPAN DATA PEGAWAI FAKULTAS (KHUSUS ADMIN & PIMPINAN) 📊 --}}
                @if(isset($facultyCompleteness))
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6"
                         x-data="{ 
                             filter: 'all', 
                             search: '',
                             counts: {
                                 all: {{ count($facultyCompleteness['pegawai_scores'] ?? []) }},
                                 complete: {{ $facultyCompleteness['total_complete'] ?? 0 }},
                                 moderate: {{ $facultyCompleteness['total_moderate'] ?? 0 }},
                                 low: {{ $facultyCompleteness['total_low'] ?? 0 }}
                             }
                         }">
                        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-100 pb-4 mb-4 gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">📊</span>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-800">Monitoring Kelengkapan Data Pegawai Fakultas</h3>
                                    <p class="text-xs text-slate-500">Evaluasi pemenuhan dokumen SK & data profil seluruh pegawai (Klik kartu untuk memfilter tabel di bawah)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200 shrink-0">
                                <span class="text-xs font-bold text-slate-600">Rata-Rata Fakultas:</span>
                                <span class="text-xl font-black text-blue-600">{{ $facultyCompleteness['average_score'] }}%</span>
                            </div>
                        </div>

                        {{-- Ringkasan 3 Kategori yang Dapat Diklik (Interactive Filter Cards) --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                            
                            {{-- Kartu 1: 100% Lengkap --}}
                            <div @click="filter = (filter === 'complete' ? 'all' : 'complete')"
                                 :class="filter === 'complete' ? 'ring-2 ring-emerald-500 shadow-md bg-emerald-100 border-emerald-400 scale-[1.01]' : 'bg-emerald-50 hover:bg-emerald-100/70 border-emerald-200 hover:shadow-xs'"
                                 class="border rounded-xl p-4 flex items-center justify-between cursor-pointer transition-all duration-200 select-none group"
                                 title="Klik untuk memfilter pegawai dengan data 100% Lengkap">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-800">100% Lengkap (Sempurna)</span>
                                        <span x-show="filter === 'complete'" class="text-[9px] font-black bg-emerald-600 text-white px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Aktif</span>
                                    </div>
                                    <h4 class="text-2xl font-black text-emerald-700 mt-1">{{ $facultyCompleteness['total_complete'] }} Pegawai</h4>
                                    <span class="text-[11px] text-emerald-600 font-semibold group-hover:underline flex items-center gap-1 mt-1">
                                        <span x-text="filter === 'complete' ? '✕ Reset / Tampilkan Semua' : '🔍 Klik untuk filter tabel'"></span>
                                    </span>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-emerald-200/80 text-emerald-800 flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform shadow-xs">💎</div>
                            </div>

                            {{-- Kartu 2: Cukup Lengkap (50% - 99%) --}}
                            <div @click="filter = (filter === 'moderate' ? 'all' : 'moderate')"
                                 :class="filter === 'moderate' ? 'ring-2 ring-amber-500 shadow-md bg-amber-100 border-amber-400 scale-[1.01]' : 'bg-amber-50 hover:bg-amber-100/70 border-amber-200 hover:shadow-xs'"
                                 class="border rounded-xl p-4 flex items-center justify-between cursor-pointer transition-all duration-200 select-none group"
                                 title="Klik untuk memfilter pegawai dengan data Cukup Lengkap (50% - 99%)">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Cukup Lengkap (50% - 99%)</span>
                                        <span x-show="filter === 'moderate'" class="text-[9px] font-black bg-amber-600 text-white px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Aktif</span>
                                    </div>
                                    <h4 class="text-2xl font-black text-amber-700 mt-1">{{ $facultyCompleteness['total_moderate'] }} Pegawai</h4>
                                    <span class="text-[11px] text-amber-700 font-semibold group-hover:underline flex items-center gap-1 mt-1">
                                        <span x-text="filter === 'moderate' ? '✕ Reset / Tampilkan Semua' : '🔍 Klik untuk filter tabel'"></span>
                                    </span>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-amber-200/80 text-amber-800 flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform shadow-xs">🟡</div>
                            </div>

                            {{-- Kartu 3: Perlu Dilengkapi (< 50%) --}}
                            <div @click="filter = (filter === 'low' ? 'all' : 'low')"
                                 :class="filter === 'low' ? 'ring-2 ring-rose-500 shadow-md bg-rose-100 border-rose-400 scale-[1.01]' : 'bg-rose-50 hover:bg-rose-100/70 border-rose-200 hover:shadow-xs'"
                                 class="border rounded-xl p-4 flex items-center justify-between cursor-pointer transition-all duration-200 select-none group"
                                 title="Klik untuk memfilter pegawai yang Perlu Dilengkapi (< 50%)">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-bold uppercase tracking-wider text-rose-800">Perlu Dilengkapi (&lt; 50%)</span>
                                        <span x-show="filter === 'low'" class="text-[9px] font-black bg-rose-600 text-white px-1.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">Aktif</span>
                                    </div>
                                    <h4 class="text-2xl font-black text-rose-700 mt-1">{{ $facultyCompleteness['total_low'] }} Pegawai</h4>
                                    <span class="text-[11px] text-rose-700 font-semibold group-hover:underline flex items-center gap-1 mt-1">
                                        <span x-text="filter === 'low' ? '✕ Reset / Tampilkan Semua' : '🔍 Klik untuk filter tabel'"></span>
                                    </span>
                                </div>
                                <div class="w-11 h-11 rounded-xl bg-rose-200/80 text-rose-800 flex items-center justify-center text-xl font-bold group-hover:scale-110 transition-transform shadow-xs">🔴</div>
                            </div>

                        </div>

                        {{-- Bilah Status Filter & Pencarian Cepat --}}
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3 pt-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-semibold text-slate-500">Tampilan Data:</span>
                                
                                <button type="button" 
                                        @click="filter = 'all'"
                                        :class="filter === 'all' ? 'bg-slate-800 text-white shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700'"
                                        class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                    <span>Semua Pegawai</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'all' ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-600'" x-text="counts.all"></span>
                                </button>

                                <button type="button" 
                                        @click="filter = 'complete'"
                                        :class="filter === 'complete' ? 'bg-emerald-700 text-white shadow-xs ring-1 ring-emerald-600' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200'"
                                        class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                    <span>💎 100% Lengkap</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'complete' ? 'bg-emerald-800 text-white' : 'bg-emerald-200 text-emerald-800'" x-text="counts.complete"></span>
                                </button>

                                <button type="button" 
                                        @click="filter = 'moderate'"
                                        :class="filter === 'moderate' ? 'bg-amber-600 text-white shadow-xs ring-1 ring-amber-500' : 'bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200'"
                                        class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                    <span>🟡 Cukup Lengkap</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'moderate' ? 'bg-amber-700 text-white' : 'bg-amber-200 text-amber-800'" x-text="counts.moderate"></span>
                                </button>

                                <button type="button" 
                                        @click="filter = 'low'"
                                        :class="filter === 'low' ? 'bg-rose-700 text-white shadow-xs ring-1 ring-rose-600' : 'bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-200'"
                                        class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                    <span>🔴 Perlu Dilengkapi</span>
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full" :class="filter === 'low' ? 'bg-rose-800 text-white' : 'bg-rose-200 text-rose-800'" x-text="counts.low"></span>
                                </button>
                            </div>

                            {{-- Input Pencarian Nama / NIP --}}
                            <div class="relative w-full sm:w-64">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400 text-xs">
                                    🔍
                                </span>
                                <input type="text" 
                                       x-model="search" 
                                       placeholder="Cari Nama / NIP..." 
                                       class="w-full pl-8 pr-7 py-1.5 text-xs bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-200 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition">
                                <button type="button" 
                                        x-show="search.length > 0" 
                                        @click="search = ''" 
                                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600 text-xs font-bold">
                                    ✕
                                </button>
                            </div>
                        </div>

                        {{-- Tabel Monitoring Kelengkapan Pegawai --}}
                        <div class="overflow-x-auto max-h-88 overflow-y-auto border border-slate-200 rounded-xl">
                            <table class="w-full text-xs text-left text-slate-600">
                                <thead class="bg-slate-50 text-slate-700 uppercase font-bold text-[10px] sticky top-0 border-b border-slate-200 shadow-2xs">
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
                                    @foreach($facultyCompleteness['pegawai_scores'] ?? [] as $item)
                                        @php
                                            $peg = data_get($item, 'pegawai');
                                            if (!$peg) continue;
                                            $pegId = data_get($peg, 'id');
                                            $pegNama = data_get($peg, 'nama_lengkap') ?? data_get($peg, 'nama') ?? '-';
                                            $pegNip = data_get($peg, 'nip') ?? '-';
                                            $pegJabatan = data_get($peg, 'jabatan_nama') ?? data_get($peg, 'jabatan.nama_jabatan') ?? '-';
                                            $pegUnit = data_get($peg, 'unit_nama') ?? data_get($peg, 'unitKerja.nama_unit') ?? '-';
                                            $score = data_get($item, 'score', 0);
                                            $progressColor = data_get($item, 'progress_color', 'bg-blue-500');
                                            $badgeColor = data_get($item, 'badge_color', 'bg-slate-100 text-slate-700 border-slate-200');
                                            $statusLabel = data_get($item, 'status_label', '-');
                                            $missingCount = data_get($item, 'missing_count', 0);
                                            
                                            // Klasifikasi Kategori
                                            $itemCategory = ($score >= 100) ? 'complete' : (($score >= 50) ? 'moderate' : 'low');
                                            $searchPayload = strtolower(addslashes($pegNama . ' ' . $pegNip . ' ' . $pegJabatan . ' ' . $pegUnit));
                                        @endphp
                                        <tr class="hover:bg-slate-50 transition"
                                            x-show="(filter === 'all' || filter === '{{ $itemCategory }}') && (search === '' || '{{ $searchPayload }}'.includes(search.toLowerCase().trim()))">
                                            <td class="px-4 py-3">
                                                <div class="font-bold text-slate-900">{{ $pegNama }}</div>
                                                <div class="text-[10px] text-slate-500 font-mono">NIP: {{ $pegNip }}</div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="font-semibold text-slate-800">{{ $pegJabatan }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $pegUnit }}</div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                                        <div class="{{ $progressColor }} h-2 rounded-full" style="width: {{ $score }}%"></div>
                                                    </div>
                                                    <span class="font-black text-slate-900 text-xs">{{ $score }}%</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $badgeColor }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($missingCount > 0)
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-900">
                                                        ⚠️ {{ $missingCount }} Item Belum
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-900">
                                                        ✅ Complete
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ $pegId ? route('pegawai.show', $pegId) : '#' }}" class="inline-flex items-center px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-[11px] font-semibold transition border border-slate-300">
                                                    Lihat Profil &rarr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach

                                    {{-- Pesan Ketika Kategori Terfilter Bernilai 0 Pegawai --}}
                                    <tr x-show="filter === 'complete' && counts.complete === 0">
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs">
                                            <span class="text-3xl block mb-2">💎</span>
                                            <p class="font-semibold text-slate-600">Belum ada pegawai dengan data 100% Lengkap (Sempurna).</p>
                                            <button type="button" @click="filter = 'all'" class="mt-2 text-blue-600 font-bold hover:underline">
                                                &larr; Tampilkan Semua Pegawai
                                            </button>
                                        </td>
                                    </tr>

                                    <tr x-show="filter === 'moderate' && counts.moderate === 0">
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs">
                                            <span class="text-3xl block mb-2">🟡</span>
                                            <p class="font-semibold text-slate-600">Tidak ada pegawai dalam kategori Cukup Lengkap (50% - 99%).</p>
                                            <button type="button" @click="filter = 'all'" class="mt-2 text-blue-600 font-bold hover:underline">
                                                &larr; Tampilkan Semua Pegawai
                                            </button>
                                        </td>
                                    </tr>

                                    <tr x-show="filter === 'low' && counts.low === 0">
                                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-xs">
                                            <span class="text-3xl block mb-2">🎉</span>
                                            <p class="font-semibold text-slate-600">Luar biasa! Tidak ada pegawai dalam kategori Perlu Dilengkapi (&lt; 50%).</p>
                                            <button type="button" @click="filter = 'all'" class="mt-2 text-blue-600 font-bold hover:underline">
                                                &larr; Tampilkan Semua Pegawai
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- 🔔 BARIS 2: PUSAT REMINDER TRANSAKSI KEPEGAWAIAN (GRID 4 KOLOM) 🔔 --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-b border-gray-100 pb-3 mb-4 gap-3">
                        <div class="flex items-center space-x-2">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <h3 class="text-lg font-bold text-gray-800">Pusat Reminder Transaksi Kepegawaian</h3>
                        </div>
                        @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                            <div class="flex items-center gap-2">
                                <a href="{{ route('reports.reminder.pdf') }}" target="_blank"
                                   class="inline-flex items-center px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Cetak PDF Pengingat
                                </a>
                                <a href="{{ route('reports.reminder.excel') }}"
                                   class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Export Excel
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        
                        {{-- Card 1: Reminder KGB --}}
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-amber-800 flex justify-between items-center mb-2">
                                    <div>
                                        <span>Gaji Berkala (KGB)</span>
                                        <span class="block text-[10px] text-amber-600 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-amber-200 text-amber-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ is_countable($reminder['kgb'] ?? null) ? count($reminder['kgb']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['kgb']) && is_countable($reminder['kgb']) && count($reminder['kgb']) > 0)
                                    <ul class="text-xs text-amber-900 divide-y divide-amber-200 max-h-48 overflow-y-auto">
                                        @foreach($reminder['kgb'] as $r)
                                            <li class="py-1.5 flex justify-between items-center">
                                                <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <strong class="text-amber-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-amber-600 mt-2 italic">Aman. Tidak ada jatuh tempo.</p>
                                @endif
                            @if(Auth::user()->hasRole('admin'))
                                <div class="mt-3 pt-2 border-t border-amber-200/60 text-right">
                                    <a href="{{ route('kgb.index', ['filter' => 'reminder']) }}" class="text-[11px] font-semibold text-amber-800 hover:text-amber-950 hover:underline inline-flex items-center gap-1">
                                        Buka Monitoring KGB ({{ is_countable($reminder['kgb'] ?? null) ? count($reminder['kgb']) : 0 }} Pegawai) &rarr;
                                    </a>
                                </div>
                            @endif
                            </div>
                        </div>

                        {{-- Card 2: Reminder Kenaikan Pangkat --}}
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-emerald-800 flex justify-between items-center mb-2">
                                    <div>
                                        <span>Kenaikan Pangkat (KP)</span>
                                        <span class="block text-[10px] text-emerald-600 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-emerald-200 text-emerald-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ is_countable($reminder['kp'] ?? null) ? count($reminder['kp']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['kp']) && is_countable($reminder['kp']) && count($reminder['kp']) > 0)
                                    <ul class="text-xs text-emerald-900 divide-y divide-emerald-200 max-h-48 overflow-y-auto">
                                        @foreach($reminder['kp'] as $r)
                                            <li class="py-1.5 flex justify-between items-center">
                                                <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <strong class="text-emerald-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-emerald-600 mt-2 italic">Aman. Tidak ada jatuh tempo.</p>
                                @endif
                            @if(Auth::user()->hasRole('admin'))
                                <div class="mt-3 pt-2 border-t border-emerald-200/60 text-right">
                                    <a href="{{ route('kp.index', ['filter' => 'reminder']) }}" class="text-[11px] font-semibold text-emerald-800 hover:text-emerald-950 hover:underline inline-flex items-center gap-1">
                                        Buka Monitoring KP ({{ is_countable($reminder['kp'] ?? null) ? count($reminder['kp']) : 0 }} Pegawai) &rarr;
                                    </a>
                                </div>
                            @endif
                            </div>
                        </div>

                        {{-- Card 3: Reminder Satyalancana --}}
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-indigo-800 flex justify-between items-center mb-2">
                                    <div>
                                        <span>Satyalancana</span>
                                        <span class="block text-[10px] text-indigo-600 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-indigo-200 text-indigo-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ is_countable($reminder['satyalancana'] ?? null) ? count($reminder['satyalancana']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['satyalancana']) && is_countable($reminder['satyalancana']) && count($reminder['satyalancana']) > 0)
                                    <ul class="text-xs text-indigo-900 divide-y divide-indigo-200 max-h-48 overflow-y-auto">
                                        @foreach($reminder['satyalancana'] as $r)
                                            <li class="py-1.5 flex justify-between items-center">
                                                <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <strong class="text-indigo-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-indigo-600 mt-2 italic">Aman. Tidak ada jatuh tempo.</p>
                                @endif
                            @if(Auth::user()->hasRole('admin'))
                                <div class="mt-3 pt-2 border-t border-indigo-200/60 text-right">
                                    <a href="{{ route('satyalancana.index') }}" class="text-[11px] font-semibold text-indigo-800 hover:text-indigo-950 hover:underline inline-flex items-center gap-1">
                                        Buka Monitoring Satyalancana &rarr;
                                    </a>
                                </div>
                            @endif
                            </div>
                        </div>

                        {{-- Card 4: Reminder Pensiun --}}
                        <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-rose-800 flex justify-between items-center mb-2">
                                    <div>
                                        <span>Masa Pensiun (BUP 58)</span>
                                        <span class="block text-[10px] text-rose-600 font-semibold">1 Tahun ke Depan</span>
                                    </div>
                                    <span class="bg-rose-200 text-rose-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ is_countable($reminder['pensiun'] ?? null) ? count($reminder['pensiun']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['pensiun']) && is_countable($reminder['pensiun']) && count($reminder['pensiun']) > 0)
                                    <ul class="text-xs text-rose-900 divide-y divide-rose-200 max-h-48 overflow-y-auto">
                                        @foreach($reminder['pensiun'] as $r)
                                            <li class="py-1.5 flex justify-between items-center">
                                                <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }}">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <strong class="text-rose-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-rose-600 mt-2 italic">Aman. Tidak ada masa pensiun terdekat.</p>
                                @endif
                            </div>
                        </div>

                        {{-- Card 5: Reminder STR & SIP (Khas Ners/Klinis) --}}
                        <div class="bg-sky-50 border border-sky-200 rounded-lg p-4 flex flex-col justify-between">
                            <div>
                                <h4 class="font-semibold text-sky-800 flex justify-between items-center mb-2">
                                    <div>
                                        <span>STR & SIP (Ners/Klinis)</span>
                                        <span class="block text-[10px] text-sky-600 font-semibold">6 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-sky-200 text-sky-900 text-xs px-2 py-0.5 rounded-full font-bold">{{ is_countable($reminder['str_sip'] ?? null) ? count($reminder['str_sip']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['str_sip']) && is_countable($reminder['str_sip']) && count($reminder['str_sip']) > 0)
                                    <ul class="text-xs text-sky-900 divide-y divide-sky-200 max-h-48 overflow-y-auto">
                                        @foreach($reminder['str_sip'] as $r)
                                            <li class="py-1.5 flex justify-between items-center">
                                                <span class="truncate mr-2" title="{{ $r->nama_lengkap ?? $r->nama }} ({{ $r->jenis_dokumen }})">{{ $r->nama_lengkap ?? $r->nama }}</span> 
                                                <strong class="text-sky-700 font-mono flex-shrink-0">{{ $r->tanggal_kegiatan }}</strong>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-xs text-sky-600 mt-2 italic">Aman. Tidak ada STR/SIP kedaluwarsa terdekat.</p>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                {{-- BARIS 3: SUB-STATISTIK KLASIFIKASI KEPEGAWAIAN (INTERAKTIF & DAPAT DIKLIK) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <a href="{{ route('kepegawaian.dosen.index', ['filter' => 'pns']) }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group">
                        <div>
                            <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wider group-hover:text-indigo-600">Dosen PNS</h3>
                            <p class="text-3xl font-extrabold mt-1.5 text-indigo-600">{{ $statistik['dosen_pns'] ?? $statistik['dosen'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl group-hover:bg-indigo-100 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('kepegawaian.dosen.index', ['filter' => 'pppk']) }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group">
                        <div>
                            <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wider group-hover:text-purple-600">Dosen PPPK</h3>
                            <p class="text-3xl font-extrabold mt-1.5 text-purple-600">{{ $statistik['dosen_pppk'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-purple-50 text-purple-600 rounded-xl group-hover:bg-purple-100 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('kepegawaian.tendik.index', ['filter' => 'pns']) }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group">
                        <div>
                            <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wider group-hover:text-blue-600">Tendik PNS</h3>
                            <p class="text-3xl font-extrabold mt-1.5 text-blue-600">{{ $statistik['tendik_pns'] ?? $statistik['tendik'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl group-hover:bg-blue-100 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                    </a>

                    <a href="{{ route('kepegawaian.tendik.index', ['filter' => 'pppk']) }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group">
                        <div>
                            <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wider group-hover:text-emerald-600">Tendik PPPK</h3>
                            <p class="text-3xl font-extrabold mt-1.5 text-emerald-600">{{ $statistik['tendik_pppk'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl group-hover:bg-emerald-100 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </a>

                    <a href="{{ route('kepegawaian.phl.index') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 flex items-center justify-between transition transform hover:scale-[1.02] hover:shadow-md cursor-pointer group">
                        <div>
                            <h3 class="font-bold text-gray-500 text-xs uppercase tracking-wider group-hover:text-amber-600">Pegawai PHL</h3>
                            <p class="text-3xl font-extrabold mt-1.5 text-amber-600">{{ $statistik['phl'] ?? $statistik['honorer'] ?? 0 }}</p>
                        </div>
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl group-hover:bg-amber-100 transition">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    </a>
                </div>

            @endif

        </div>
    </div>
</x-app-layout>