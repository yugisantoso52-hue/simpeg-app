<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

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
                                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-xs transition border border-white/30 hover:bg-white/30" style="background-color: rgba(255, 255, 255, 0.2) !important; color: #ffffff !important;" title="Lihat Struktur Organisasi & Peta Jabatan">
                                    <span>🏛️</span> Peta Organisasi
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
                                <div class="w-12 h-12 rounded-xl {{ $isHigh ? 'bg-emerald-600 text-white' : ($isMid ? 'bg-blue-600 text-white' : 'bg-amber-600 text-white') }} flex items-center justify-center text-2xl shadow-sm shrink-0">
                                    🎯
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
                                    <span>🔍</span> Buka Profil & Rekam Asesmen Lengkap →
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
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            👑 Talent Pool Suksesi
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
                                Selamat Datang, {{ Auth::user()->name }}! 👋
                            </h1>

                            <div class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-slate-200">
                                @if(Auth::user()->pegawai?->jabatan?->nama_jabatan)
                                    <span class="inline-flex items-center gap-1.5 font-medium text-emerald-300">
                                        💼 {{ Auth::user()->pegawai->jabatan->nama_jabatan }}
                                    </span>
                                    @if(Auth::user()->pegawai?->nip)
                                        <span class="text-slate-400 font-mono">• NIP: {{ Auth::user()->pegawai->nip }}</span>
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

                {{-- 🚨 PUSAT TINDAKAN & OPERASIONAL HARIAN (ACTION ITEMS) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    {{-- 1. Pending Cuti --}}
                    <a href="{{ route('pengajuan-cuti.index') }}" class="p-4 rounded-2xl bg-amber-50/90 border border-amber-200/80 shadow-xs hover:shadow-md hover:border-amber-300 transition duration-150 flex items-center justify-between group">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-amber-800 uppercase tracking-wider">Cuti Menunggu</span>
                                @if(($pendingCutiCount ?? 0) > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-200 text-amber-900 animate-pulse">Respon</span>
                                @endif
                            </div>
                            <div class="text-2xl font-black text-amber-950 mt-1">{{ $pendingCutiCount ?? 0 }}</div>
                            <div class="text-[11px] font-medium text-amber-700 mt-0.5 group-hover:underline flex items-center gap-1">
                                <span>{{ ($pendingCutiCount ?? 0) > 0 ? 'Verifikasi Permohonan' : 'Semua Cuti Selesai' }}</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-amber-200/70 text-amber-900 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition">
                            🏖️
                        </div>
                    </a>

                    {{-- 2. Pending Logbook --}}
                    <a href="{{ route('admin.logbook.index', ['status' => 'diajukan']) }}" class="p-4 rounded-2xl bg-indigo-50/90 border border-indigo-200/80 shadow-xs hover:shadow-md hover:border-indigo-300 transition duration-150 flex items-center justify-between group">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Logbook Menunggu</span>
                                @if(($pendingLogbookCount ?? 0) > 0)
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-indigo-200 text-indigo-900 animate-pulse">Review</span>
                                @endif
                            </div>
                            <div class="text-2xl font-black text-indigo-950 mt-1">{{ $pendingLogbookCount ?? 0 }}</div>
                            <div class="text-[11px] font-medium text-indigo-700 mt-0.5 group-hover:underline flex items-center gap-1">
                                <span>{{ ($pendingLogbookCount ?? 0) > 0 ? 'Verifikasi Kinerja' : 'Semua Logbook Terverifikasi' }}</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-200/70 text-indigo-900 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition">
                            📝
                        </div>
                    </a>

                    {{-- 3. Presensi Hari Ini --}}
                    <a href="{{ route('admin.presensi.index') }}" class="p-4 rounded-2xl bg-emerald-50/90 border border-emerald-200/80 shadow-xs hover:shadow-md hover:border-emerald-300 transition duration-150 flex items-center justify-between group">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Presensi Hari Ini</span>
                                <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-semibold bg-emerald-200 text-emerald-900">Live</span>
                            </div>
                            <div class="text-2xl font-black text-emerald-950 mt-1">{{ $todayPresentCount ?? 0 }}</div>
                            <div class="text-[11px] font-medium text-emerald-700 mt-0.5 group-hover:underline flex items-center gap-1">
                                <span>Pegawai Hadir Hari Ini</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-emerald-200/70 text-emerald-900 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition">
                            📍
                        </div>
                    </a>

                    {{-- 4. Total Jam Logbook Bulan Ini --}}
                    <a href="{{ route('admin.logbook.index') }}" class="p-4 rounded-2xl bg-blue-50/90 border border-blue-200/80 shadow-xs hover:shadow-md hover:border-blue-300 transition duration-150 flex items-center justify-between group">
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-blue-800 uppercase tracking-wider">Kinerja Bulan Ini</span>
                            </div>
                            <div class="text-2xl font-black text-blue-950 mt-1">{{ $adminLogbookStats['total_jam'] ?? 0 }} Jam</div>
                            <div class="text-[11px] font-medium text-blue-700 mt-0.5 group-hover:underline flex items-center gap-1">
                                <span>{{ $adminLogbookStats['total'] ?? 0 }} Aktivitas Terdata</span>
                                <span class="transition-transform group-hover:translate-x-0.5">&rarr;</span>
                            </div>
                        </div>
                        <div class="w-12 h-12 rounded-xl bg-blue-200/70 text-blue-900 flex items-center justify-center text-2xl shadow-2xs group-hover:scale-105 transition">
                            ⏱️
                        </div>
                    </a>
                </div>

                {{-- ⚡ PINTASAN CEPAT MODUL POPULER (QUICK ACTIONS) --}}
                <div class="bg-white rounded-2xl shadow-xs border border-slate-200/80 p-5 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3.5 mb-4 border-b border-slate-100 gap-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-base border border-blue-100 shadow-2xs">
                                ⚡
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Pintasan Cepat Modul Populer</h4>
                                <p class="text-[11px] text-slate-500">Akses langsung ke modul operasional, data kepegawaian, dan monitoring harian.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-4 lg:grid-cols-8 gap-3">
                        @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                            {{-- 1. Master Pegawai --}}
                            <a href="{{ route('pegawai.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-blue-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">📋</span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-blue-700">Master Pegawai</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">Kelola Data</span>
                            </a>
                        @endif

                        {{-- 2. Rekap Presensi --}}
                        <a href="{{ route('admin.presensi.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-emerald-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                            <span class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">📊</span>
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-emerald-700">Rekap Presensi</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Log & GPS</span>
                        </a>

                        {{-- 3. Verifikasi Logbook --}}
                        <a href="{{ route('admin.logbook.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-indigo-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                            <span class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">📝</span>
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-indigo-700">Logbook Kinerja</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Verifikasi</span>
                        </a>

                        {{-- 4. Pengajuan Cuti --}}
                        <a href="{{ route('pengajuan-cuti.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-amber-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                            <span class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">🏖️</span>
                            <span class="text-xs font-semibold text-slate-700 group-hover:text-amber-700">Layanan Cuti</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">Persetujuan</span>
                        </a>

                        @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                            {{-- 5. Kenaikan Pangkat --}}
                            <a href="{{ route('kp.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-purple-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">🎖️</span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-purple-700">Kenaikan Pangkat</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">Usulan KP</span>
                            </a>

                            {{-- 6. Kenaikan Gaji Berkala --}}
                            <a href="{{ route('kgb.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-teal-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">💵</span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-teal-700">Gaji Berkala</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">Jadwal KGB</span>
                            </a>

                            {{-- 7. DUK --}}
                            <a href="{{ route('duk.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-cyan-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">📊</span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-cyan-700">Matriks DUK</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">Urut Pangkat</span>
                            </a>

                            {{-- 8. Tugas Belajar --}}
                            <a href="{{ route('tugas-belajar.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl border border-slate-100 bg-slate-50/50 hover:bg-white hover:border-rose-400 hover:shadow-xs hover:-translate-y-0.5 transition duration-150 group text-center">
                                <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-xl mb-2 group-hover:scale-110 transition">🎓</span>
                                <span class="text-xs font-semibold text-slate-700 group-hover:text-rose-700">Tugas Belajar</span>
                                <span class="text-[10px] text-slate-400 mt-0.5">Studi Pegawai</span>
                            </a>
                        @endif
                    </div>
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
                        if (isset($tBoxes[9]['items'])) {
                            $topTalents = $topTalents->merge($tBoxes[9]['items']);
                        }
                        if (isset($tBoxes[8]['items'])) {
                            $topTalents = $topTalents->merge($tBoxes[8]['items']);
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            
                            {{-- KPI 1: Talent Pool Siap Promosi (Kotak 7, 8, 9) --}}
                            <a href="{{ route('manajemen-talenta.rekap', ['only_suksesi' => 1]) }}" class="group block p-4 rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50/80 to-teal-50/50 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Talent Pool Siap Promosi</span>
                                    <span class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center text-sm font-black shadow-xs">👑</span>
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-emerald-950">{{ $countPromosi }}</span>
                                    <span class="text-xs font-bold text-emerald-700">ASN ({{ $pctPromosi }}%)</span>
                                </div>
                                <div class="w-full bg-emerald-200/70 rounded-full h-1.5 mt-2 overflow-hidden">
                                    <div class="bg-emerald-600 h-1.5 rounded-full" style="width: {{ $pctPromosi }}%"></div>
                                </div>
                                <p class="text-[11px] text-emerald-700 mt-2 line-clamp-1 group-hover:underline">Kotak VII, VIII & IX (Prioritas Suksesi) →</p>
                            </a>

                            {{-- KPI 2: Kelompok Dipertahankan / Pengembangan (Kotak 4, 5, 6) --}}
                            <a href="{{ route('manajemen-talenta.rekap') }}" class="group block p-4 rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50/80 to-indigo-50/50 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800">Kelompok Pengembangan</span>
                                    <span class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center text-sm font-black shadow-xs">📈</span>
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-blue-950">{{ $countMid }}</span>
                                    <span class="text-xs font-bold text-blue-700">ASN ({{ $pctMid }}%)</span>
                                </div>
                                <div class="w-full bg-blue-200/70 rounded-full h-1.5 mt-2 overflow-hidden">
                                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $pctMid }}%"></div>
                                </div>
                                <p class="text-[11px] text-blue-700 mt-2 line-clamp-1 group-hover:underline">Kotak IV, V & VI (Fokus Diklat 20 JP) →</p>
                            </a>

                            {{-- KPI 3: Bimbingan & Konseling (Kotak 1, 2, 3) --}}
                            <a href="{{ route('manajemen-talenta.rekap') }}" class="group block p-4 rounded-xl border border-amber-200 bg-gradient-to-br from-amber-50/80 to-orange-50/50 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-amber-900">Bimbingan & Konseling</span>
                                    <span class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center text-sm font-black shadow-xs">⚠️</span>
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-amber-950">{{ $countLow }}</span>
                                    <span class="text-xs font-bold text-amber-800">ASN ({{ $pctLow }}%)</span>
                                </div>
                                <div class="w-full bg-amber-200/70 rounded-full h-1.5 mt-2 overflow-hidden">
                                    <div class="bg-amber-600 h-1.5 rounded-full" style="width: {{ $pctLow }}%"></div>
                                </div>
                                <p class="text-[11px] text-amber-800 mt-2 line-clamp-1 group-hover:underline">Kotak I, II & III (Penataan & Pembinaan) →</p>
                            </a>

                            {{-- KPI 4: Target Suksesi Jabatan Aktif --}}
                            <a href="{{ route('manajemen-talenta.suksesi.index') }}" class="group block p-4 rounded-xl border border-purple-200 bg-gradient-to-br from-purple-50/80 to-fuchsia-50/50 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-purple-900">Rencana Suksesi Jabatan</span>
                                    <span class="w-8 h-8 rounded-lg bg-purple-600 text-white flex items-center justify-center text-sm font-black shadow-xs">🏛️</span>
                                </div>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span class="text-3xl font-black text-purple-950">{{ $totalSuccessionPlans ?? 0 }}</span>
                                    <span class="text-xs font-bold text-purple-700">Jabatan Target</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-2">
                                    Total ASN Terpetakan: <strong class="text-slate-800">{{ $totalMapped }} ASN</strong>
                                </div>
                                <p class="text-[11px] text-purple-700 mt-1 line-clamp-1 group-hover:underline">Buka Pemetaan Suksesi Struktural →</p>
                            </a>

                        </div>

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
                                                            K{{ $talent->kuadran_box }}
                                                        </span>
                                                        <div class="text-[9px] text-slate-500 font-mono mt-0.5">
                                                            K:{{ number_format($talent->sumbu_kinerja_nilai, 0) }} P:{{ number_format($talent->sumbu_potensi_nilai, 0) }}
                                                        </div>
                                                    </div>
                                                </a>
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
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6"
                         x-data="{
                             filter: 'all',
                             search: '',
                             items: {{ Js::from($abkSummary['items'] ?? []) }},
                             get filteredItems() {
                                 let f = this.filter;
                                 let s = this.search.toLowerCase().trim();
                                 return this.items.filter(item => {
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
                                 if (this.filter === 'defisit') return '🔴 Formasi Kurang (' + this.items.filter(i => i.is_defisit).length + ' Jabatan)';
                                 if (this.filter === 'ideal') return '🟢 Formasi Ideal (' + this.items.filter(i => i.is_ideal).length + ' Jabatan)';
                                 if (this.filter === 'dokumen') return '📑 Dokumen Baku (' + this.items.length + ' Dokumen)';
                                 return '📋 Semua Formasi (' + this.items.length + ' Jabatan)';
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            {{-- Kartu 1: Rasio Keterisian Formasi (Semua) --}}
                            <div @click="setFilter('all')"
                                 :class="filter === 'all' ? 'ring-2 ring-indigo-500 shadow-md bg-indigo-100/90 border-indigo-400 scale-[1.01]' : 'border-indigo-200/80 bg-gradient-to-br from-indigo-50/70 to-blue-50/50 hover:shadow-xs hover:border-indigo-300'"
                                 class="p-4 rounded-xl border flex flex-col justify-between cursor-pointer transition-all duration-200 select-none group"
                                 title="Klik untuk melihat seluruh formasi jabatan teranalisis">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-bold uppercase tracking-wider text-indigo-900">Keterisian Formasi</span>
                                        <span class="text-xs font-black text-indigo-700">{{ $abkSummary['rasioKeterisian'] }}%</span>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-black text-indigo-950">{{ $abkSummary['totalBezetting'] }}</span>
                                        <span class="text-xs font-bold text-indigo-700">/ {{ $abkSummary['totalKebutuhan'] }} Formasi ABK</span>
                                    </div>
                                    <div class="w-full bg-indigo-200/60 rounded-full h-2 mt-2 overflow-hidden">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, $abkSummary['rasioKeterisian']) }}%"></div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-2 border-t border-indigo-200/50 flex items-center justify-between text-[11px] text-indigo-800">
                                    <span>Pegawai Aktif: <strong>{{ $statistik['aktif'] ?? 83 }}</strong></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold transition" :class="filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-white/80 text-indigo-700 border border-indigo-200'">
                                        <span x-text="filter === 'all' ? '● Aktif' : 'Lihat Rincian &rarr;'"></span>
                                    </span>
                                </div>
                            </div>

                            {{-- Kartu 2: Defisit Pegawai (Perlu Rekrutmen / Formasi Kurang) --}}
                            <div @click="setFilter('defisit')"
                                 :class="filter === 'defisit' ? 'ring-2 ring-rose-500 shadow-md bg-rose-100 border-rose-400 scale-[1.01]' : 'border-rose-200 bg-rose-50/60 hover:shadow-xs hover:border-rose-300'"
                                 class="p-4 rounded-xl border flex flex-col justify-between cursor-pointer transition-all duration-200 select-none group"
                                 title="Klik untuk melihat daftar jabatan yang defisit / kurang pegawai">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-bold uppercase tracking-wider text-rose-900">Defisit Formasi</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-200 text-rose-900">⚠️ Butuh SDM</span>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-black text-rose-700">{{ $abkSummary['totalDefisit'] }}</span>
                                        <span class="text-xs font-bold text-rose-800">Orang Kurang</span>
                                    </div>
                                    <p class="text-[11px] text-rose-600 mt-1.5">Kekurangan riil berdasarkan jam beban kerja standar (WKE 1.250 jam/thn)</p>
                                </div>
                                <div class="mt-3 pt-2 border-t border-rose-200/60 flex items-center justify-between text-[11px]">
                                    <span class="text-rose-700 font-bold">Prioritas Usulan CASN</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold transition" :class="filter === 'defisit' ? 'bg-rose-600 text-white' : 'bg-white/80 text-rose-700 border border-rose-200'">
                                        <span x-text="filter === 'defisit' ? '● Aktif' : 'Lihat Rincian &rarr;'"></span>
                                    </span>
                                </div>
                            </div>

                            {{-- Kartu 3: Formasi Ideal / Cukup --}}
                            <div @click="setFilter('ideal')"
                                 :class="filter === 'ideal' ? 'ring-2 ring-emerald-500 shadow-md bg-emerald-100 border-emerald-400 scale-[1.01]' : 'border-emerald-200 bg-emerald-50/60 hover:shadow-xs hover:border-emerald-300'"
                                 class="p-4 rounded-xl border flex flex-col justify-between cursor-pointer transition-all duration-200 select-none group"
                                 title="Klik untuk melihat formasi yang sudah terpenuhi / ideal">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-900">Formasi Ideal</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-200 text-emerald-900">✓ Cukup</span>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-black text-emerald-700">{{ $abkSummary['totalIdeal'] }}</span>
                                        <span class="text-xs font-bold text-emerald-800">Jabatan Terpenuhi</span>
                                    </div>
                                    <p class="text-[11px] text-emerald-600 mt-1.5">Jumlah pegawai aktif saat ini seimbang dengan beban kerja jabatan</p>
                                </div>
                                <div class="mt-3 pt-2 border-t border-emerald-200/60 flex items-center justify-between text-[11px]">
                                    <span class="text-emerald-700 font-semibold">Beban Kerja Seimbang</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold transition" :class="filter === 'ideal' ? 'bg-emerald-600 text-white' : 'bg-white/80 text-emerald-700 border border-emerald-200'">
                                        <span x-text="filter === 'ideal' ? '● Aktif' : 'Lihat Rincian &rarr;'"></span>
                                    </span>
                                </div>
                            </div>

                            {{-- Kartu 4: Dokumen & Standar Anjab Terisi --}}
                            <div @click="setFilter('dokumen')"
                                 :class="filter === 'dokumen' ? 'ring-2 ring-blue-500 shadow-md bg-blue-100 border-blue-400 scale-[1.01]' : 'border-blue-200 bg-blue-50/60 hover:shadow-xs hover:border-blue-300'"
                                 class="p-4 rounded-xl border flex flex-col justify-between cursor-pointer transition-all duration-200 select-none group"
                                 title="Klik untuk melihat katalog dokumen analisis jabatan baku">
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-bold uppercase tracking-wider text-blue-900">Dokumen Anjab Baku</span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-200 text-blue-900">17 Butir</span>
                                    </div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-2xl font-black text-blue-700">{{ $abkSummary['totalDokumen'] }}</span>
                                        <span class="text-xs font-bold text-blue-800">Jabatan Teranalisis</span>
                                    </div>
                                    <p class="text-[11px] text-blue-600 mt-1.5">Lengkap dengan rincian uraian tugas, kualifikasi, syarat jabatan, & kelas</p>
                                </div>
                                <div class="mt-3 pt-2 border-t border-blue-200/60 flex items-center justify-between text-[11px]">
                                    <span class="text-blue-700 font-semibold">Standar KemenPAN-RB</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold transition" :class="filter === 'dokumen' ? 'bg-blue-600 text-white' : 'bg-white/80 text-blue-700 border border-blue-200'">
                                        <span x-text="filter === 'dokumen' ? '● Aktif' : 'Lihat Rincian &rarr;'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

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
                             items: {{ Js::from($itemsJson) }},
                             counts: {
                                 all: {{ count($facultyCompleteness['pegawai_scores'] ?? []) }},
                                 complete: {{ $facultyCompleteness['total_complete'] ?? 0 }},
                                 moderate: {{ $facultyCompleteness['total_moderate'] ?? 0 }},
                                 low: {{ $facultyCompleteness['total_low'] ?? 0 }}
                             },
                             get filteredItems() {
                                 let f = this.filter;
                                 let s = this.search.toLowerCase().trim();
                                 return this.items.filter(item => {
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
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
                            
                            {{-- Kartu 1: 100% Lengkap --}}
                            <div @click="setFilter(filter === 'complete' ? 'all' : 'complete')"
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
                            <div @click="setFilter(filter === 'moderate' ? 'all' : 'moderate')"
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
                            <div @click="setFilter(filter === 'low' ? 'all' : 'low')"
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
                                <a href="{{ route('pegawai.index') }}" class="font-bold text-blue-600 hover:text-blue-800 hover:underline inline-flex items-center gap-1">
                                    Buka Seluruh Data di Master Pegawai &rarr;
                                </a>
                            </div>

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
                        <div class="bg-amber-50/90 border border-amber-200/80 rounded-xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
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

                        {{-- Card 2: Reminder Kenaikan Pangkat --}}
                        <div class="bg-emerald-50/90 border border-emerald-200/80 rounded-xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-emerald-900 flex justify-between items-center mb-2.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">Kenaikan Pangkat (KP)</span>
                                        <span class="block text-[10px] text-emerald-700 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-emerald-200/90 text-emerald-950 text-xs px-2 py-0.5 rounded-full font-bold border border-emerald-300/60">{{ is_countable($reminder['kp'] ?? null) ? count($reminder['kp']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['kp']) && is_countable($reminder['kp']) && count($reminder['kp']) > 0)
                                    <ul class="text-xs text-emerald-950 divide-y divide-emerald-200/70 max-h-48 overflow-y-auto">
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
                                    <a href="{{ route('kp.index', ['filter' => 'reminder']) }}" class="text-[11px] font-semibold text-emerald-800 hover:text-emerald-950 hover:underline inline-flex items-center gap-1">
                                        Buka Monitoring KP &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Card 3: Reminder Satyalancana --}}
                        <div class="bg-indigo-50/90 border border-indigo-200/80 rounded-xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-indigo-900 flex justify-between items-center mb-2.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">Satyalancana</span>
                                        <span class="block text-[10px] text-indigo-700 font-normal">3 Bulan ke Depan</span>
                                    </div>
                                    <span class="bg-indigo-200/90 text-indigo-950 text-xs px-2 py-0.5 rounded-full font-bold border border-indigo-300/60">{{ is_countable($reminder['satyalancana'] ?? null) ? count($reminder['satyalancana']) : 0 }}</span>
                                </h4>
                                @if(!empty($reminder['satyalancana']) && is_countable($reminder['satyalancana']) && count($reminder['satyalancana']) > 0)
                                    <ul class="text-xs text-indigo-950 divide-y divide-indigo-200/70 max-h-48 overflow-y-auto">
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
                                    <a href="{{ route('satyalancana.index') }}" class="text-[11px] font-semibold text-indigo-800 hover:text-indigo-950 hover:underline inline-flex items-center gap-1">
                                        Buka Satyalancana &rarr;
                                    </a>
                                </div>
                            @endif
                        </div>

                        {{-- Card 4: Reminder Pensiun --}}
                        <div class="bg-rose-50/90 border border-rose-200/80 rounded-xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
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

                        {{-- Card 5: Reminder STR & SIP (Khas Ners/Klinis) --}}
                        <div class="bg-sky-50/90 border border-sky-200/80 rounded-xl p-4 flex flex-col justify-between shadow-2xs hover:shadow-xs transition">
                            <div>
                                <h4 class="font-semibold text-sky-900 flex justify-between items-center mb-2.5">
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider">STR & SIP (Ners)</span>
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