{{-- ============================================================== --}}
{{-- 1. DASHBOARD (Akses Semua Pengguna)                            --}}
{{-- ============================================================== --}}
<a href="{{ route('dashboard') }}" 
   class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-[#007a3d]/10 text-[#007a3d] font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
    <x-icon name="dashboard" class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-[#007a3d]' : 'text-slate-500' }}" />
    <span>Dashboard</span>
</a>

{{-- ============================================================== --}}
{{-- 2. ANALITIK EKSEKUTIF (Dekan, Wadek II, Kabag Umum, Admin)     --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessExecutiveKepegawaianMenus())
    <a href="{{ route('pimpinan.analytics') }}" 
       class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('pimpinan.analytics*') ? 'bg-indigo-50 text-indigo-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <x-icon name="chart" class="w-4 h-4 {{ request()->routeIs('pimpinan.analytics*') ? 'text-indigo-600' : 'text-slate-500' }}" />
        <span>Analitik</span>
    </a>
@endif

{{-- ============================================================== --}}
{{-- KHUSUS PEGAWAI: PROFIL SAYA & TALENTA SAYA                    --}}
{{-- ============================================================== --}}
@if(Auth::user()->hasRole('pegawai'))
    <a href="{{ route('pegawai.my-profile') }}" 
       class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('pegawai.my-profile', 'pegawai.show') ? 'bg-blue-50 text-blue-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <x-icon name="user" class="w-4 h-4 {{ request()->routeIs('pegawai.my-profile', 'pegawai.show') ? 'text-blue-600' : 'text-slate-500' }}" />
        <span>Profil Saya</span>
    </a>
@endif

{{-- ============================================================== --}}
{{-- 3. PRESENSI (Mandiri & Rekap Kehadiran)                        --}}
{{-- ============================================================== --}}
@if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
    <x-dropdown align="left" width="w-64">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('presensi.*', 'admin.presensi.*') ? 'bg-blue-50 text-blue-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="clock" class="w-4 h-4 {{ request()->routeIs('presensi.*', 'admin.presensi.*') ? 'text-blue-600' : 'text-slate-500' }}" />
                <span>Presensi</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Monitoring Kehadiran
            </div>
            <x-dropdown-link :href="route('admin.presensi.index')" class="flex items-center gap-2 {{ request()->routeIs('admin.presensi.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <x-icon name="calendar-check" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Rekap Presensi {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
            </x-dropdown-link>
            @if(Auth::user()->hasRole('admin'))
                <x-dropdown-link :href="route('admin.presensi.locations')" class="flex items-center gap-2 {{ request()->routeIs('admin.presensi.locations') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                    <x-icon name="map-pin" class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Titik Lokasi Pegawai (GPS)</span>
                </x-dropdown-link>
            @endif

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Presensi Mandiri
            </div>
            <x-dropdown-link :href="route('presensi.index')" class="flex items-center gap-2">
                <x-icon name="clock" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Presensi Saya</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('presensi.history')" class="flex items-center gap-2">
                <x-icon name="history" class="w-4 h-4 text-slate-500 shrink-0" />
                <span>Riwayat Presensi Saya</span>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@else
    <a href="{{ route('presensi.index') }}" 
       class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('presensi.*') ? 'bg-blue-50 text-blue-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <x-icon name="clock" class="w-4 h-4 {{ request()->routeIs('presensi.*') ? 'text-blue-600' : 'text-slate-500' }}" />
        <span>Presensi</span>
    </a>
@endif

{{-- ============================================================== --}}
{{-- 4. LOGBOOK KINERJA (Mandiri & Verifikasi)                      --}}
{{-- ============================================================== --}}
@if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
    <x-dropdown align="left" width="w-60">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('logbook.*', 'admin.logbook.*') ? 'bg-violet-50 text-violet-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="book-open" class="w-4 h-4 {{ request()->routeIs('logbook.*', 'admin.logbook.*') ? 'text-violet-600' : 'text-slate-500' }}" />
                <span>Logbook</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Verifikasi Kinerja
            </div>
            <x-dropdown-link :href="route('admin.logbook.index')" class="flex items-center gap-2 {{ request()->routeIs('admin.logbook.*') ? 'bg-violet-50 text-violet-700 font-semibold' : '' }}">
                <x-icon name="clipboard-check" class="w-4 h-4 text-violet-600 shrink-0" />
                <span>Verifikasi Logbook {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Kinerja Mandiri
            </div>
            <x-dropdown-link :href="route('logbook.index')" class="flex items-center gap-2">
                <x-icon name="clipboard-list" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Logbook Kinerja Saya</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('logbook.create')" class="flex items-center gap-2">
                <x-icon name="plus" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>+ Catat Aktivitas Baru</span>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@else
    <a href="{{ route('logbook.index') }}" 
       class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('logbook.*') ? 'bg-violet-50 text-violet-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <x-icon name="book-open" class="w-4 h-4 {{ request()->routeIs('logbook.*') ? 'text-violet-600' : 'text-slate-500' }}" />
        <span>Logbook</span>
    </a>
@endif

{{-- ============================================================== --}}
{{-- 5. E-CUTI (Pengajuan & Persetujuan)                            --}}
{{-- ============================================================== --}}
@if(Auth::user()->isAtasan() || Auth::user()->hasRole('admin'))
    <x-dropdown align="left" width="w-60">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('pengajuan-cuti.*') ? 'bg-amber-50 text-amber-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="calendar-off" class="w-4 h-4 {{ request()->routeIs('pengajuan-cuti.*') ? 'text-amber-600' : 'text-slate-500' }}" />
                <span>E-Cuti</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Persetujuan & Monitoring
            </div>
            <x-dropdown-link :href="route('pengajuan-cuti.index')" class="flex items-center gap-2">
                <x-icon name="file-text" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>{{ Auth::user()->hasRole('admin') ? 'Monitoring Cuti Pegawai' : 'Persetujuan Cuti Bawahan' }}</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Layanan Cuti Mandiri
            </div>
            <x-dropdown-link :href="route('pengajuan-cuti.index')" class="flex items-center gap-2">
                <x-icon name="calendar-off" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Permohonan Cuti Saya</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('pengajuan-cuti.create')" class="flex items-center gap-2">
                <x-icon name="plus" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>+ Buat Permohonan Cuti Baru</span>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@else
    <a href="{{ route('pengajuan-cuti.index') }}" 
       class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('pengajuan-cuti.*') ? 'bg-amber-50 text-amber-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <x-icon name="calendar-off" class="w-4 h-4 {{ request()->routeIs('pengajuan-cuti.*') ? 'text-amber-600' : 'text-slate-500' }}" />
        <span>E-Cuti</span>
    </a>
@endif

{{-- ============================================================== --}}
{{-- 6. DATA KEPEGAWAIAN & RIWAYAT (Dropdown Multi-Kolom Terpadu)   --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessExecutiveKepegawaianMenus())
    <x-dropdown align="left" width="w-[540px]">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('kepegawaian.*', 'pegawai.*', 'duk.*', 'kp.*', 'kgb.*', 'satyalancana.*', 'mutasi-pegawai.*', 'tugas-belajar.*', 'riwayat-*') ? 'bg-emerald-50 text-emerald-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="users" class="w-4 h-4 {{ request()->routeIs('kepegawaian.*', 'pegawai.*', 'duk.*', 'kp.*', 'kgb.*', 'satyalancana.*', 'mutasi-pegawai.*', 'tugas-belajar.*', 'riwayat-*') ? 'text-emerald-600' : 'text-slate-500' }}" />
                <span>Kepegawaian</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="grid grid-cols-2 divide-x divide-slate-100">
                {{-- Kolom Kiri: Kategori & Karir Pegawai --}}
                <div class="py-1">
                    <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                        Data Induk & Kategori
                    </div>
                    <x-dropdown-link :href="route('pegawai.index')" class="flex items-center gap-2 {{ request()->routeIs('pegawai.index', 'pegawai.show', 'pegawai.edit', 'pegawai.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                        <x-icon name="users" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span>Semua Pegawai (Master)</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('kepegawaian.dosen.index')" class="flex items-center gap-2 {{ request()->routeIs('kepegawaian.dosen.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="user" class="w-4 h-4 text-blue-600 shrink-0" />
                        <span>Data Dosen</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('kepegawaian.tendik.index')" class="flex items-center gap-2 {{ request()->routeIs('kepegawaian.tendik.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                        <x-icon name="user" class="w-4 h-4 text-teal-600 shrink-0" />
                        <span>Data Tenaga Kependidikan</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('kepegawaian.phl.index')" class="flex items-center gap-2 {{ request()->routeIs('kepegawaian.phl.*') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                        <x-icon name="user" class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Data PHL / Honorer</span>
                    </x-dropdown-link>

                    <div class="border-t border-slate-100 my-1"></div>
                    <div class="px-3.5 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                        Layanan Karir
                    </div>
                    <x-dropdown-link :href="route('duk.index')" class="flex items-center gap-2 {{ request()->routeIs('duk.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="list-ordered" class="w-4 h-4 text-blue-600 shrink-0" />
                        <span>Daftar Urut Kepangkatan (DUK)</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('kp.index')" class="flex items-center gap-2 {{ request()->routeIs('kp.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                        <x-icon name="award" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span>Kenaikan Pangkat (KP)</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('kgb.index')" class="flex items-center gap-2 {{ request()->routeIs('kgb.*') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                        <x-icon name="banknote" class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Kenaikan Gaji Berkala (KGB)</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('satyalancana.index')" class="flex items-center gap-2 {{ request()->routeIs('satyalancana.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                        <x-icon name="award" class="w-4 h-4 text-indigo-600 shrink-0" />
                        <span>Satyalancana Karya Satya</span>
                    </x-dropdown-link>
                    @if(Auth::user()->hasRole('admin'))
                        <x-dropdown-link :href="route('mutasi-pegawai.index')" class="flex items-center gap-2 {{ request()->routeIs('mutasi-pegawai.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                            <x-icon name="git-branch" class="w-4 h-4 text-blue-600 shrink-0" />
                            <span>Mutasi Pegawai</span>
                        </x-dropdown-link>
                    @endif
                    <x-dropdown-link :href="route('tugas-belajar.index')" class="flex items-center gap-2 {{ request()->routeIs('tugas-belajar.*') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                        <x-icon name="graduation-cap" class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>Tugas Belajar & Studi</span>
                    </x-dropdown-link>
                </div>

                {{-- Kolom Kanan: Riwayat Pegawai Lengkap --}}
                <div class="py-1">
                    <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                        Riwayat & Dokumen
                    </div>
                    <x-dropdown-link :href="route('riwayat-pendidikan.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-pendidikan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="graduation-cap" class="w-4 h-4 text-blue-600 shrink-0" />
                        <span>Riwayat Pendidikan</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('riwayat-diklat.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-diklat.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="file-text" class="w-4 h-4 text-indigo-600 shrink-0" />
                        <span>Riwayat Diklat & Pelatihan</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('riwayat-str-sip.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-str-sip.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="stethoscope" class="w-4 h-4 text-sky-600 shrink-0" />
                        <span>Riwayat STR & SIP Profesi</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('riwayat-skp.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-skp.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="chart" class="w-4 h-4 text-teal-600 shrink-0" />
                        <span>Riwayat SKP & Kinerja</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('riwayat-publikasi.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-publikasi.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="book-open" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span>Riwayat Publikasi & Karya</span>
                    </x-dropdown-link>

                    <div class="border-t border-slate-100 my-1"></div>
                    <div class="px-3.5 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                        Karir & Jabatan
                    </div>
                    <x-dropdown-link :href="route('riwayat-pangkat.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-pangkat.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="award" class="w-4 h-4 text-amber-600 shrink-0" />
                        <span>Riwayat Pangkat & Golongan</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('riwayat-jabatan.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-jabatan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="briefcase" class="w-4 h-4 text-blue-600 shrink-0" />
                        <span>Riwayat Jabatan</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('riwayat-penghargaan.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-penghargaan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="award" class="w-4 h-4 text-rose-600 shrink-0" />
                        <span>Riwayat Penghargaan</span>
                    </x-dropdown-link>
                    <x-dropdown-link :href="route('riwayat-organisasi.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-organisasi.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                        <x-icon name="building" class="w-4 h-4 text-slate-600 shrink-0" />
                        <span>Riwayat Organisasi</span>
                    </x-dropdown-link>
                </div>
            </div>
        </x-slot>
    </x-dropdown>
@endif

{{-- ============================================================== --}}
{{-- 7. ANJAB & ABK (Analisis Jabatan & Formasi Kebutuhan)          --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessAnjabAbk())
    <x-dropdown align="left" width="w-64">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('anjab.*', 'abk.*') ? 'bg-teal-50 text-teal-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="scale" class="w-4 h-4 {{ request()->routeIs('anjab.*', 'abk.*') ? 'text-teal-600' : 'text-slate-500' }}" />
                <span>Anjab & ABK</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Peta & Formasi BKN / MenPAN-RB
            </div>
            <x-dropdown-link :href="route('anjab.peta-jabatan')" class="flex items-center gap-2 {{ request()->routeIs('anjab.peta-jabatan*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                <x-icon name="git-branch" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Peta Jabatan Digital</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('abk.index')" class="flex items-center gap-2 {{ request()->routeIs('abk.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <x-icon name="chart" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Analisis Beban Kerja (ABK)</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Dokumen Anjab (PermenPAN 1/2020)
            </div>
            <x-dropdown-link :href="route('anjab.index')" class="flex items-center gap-2 {{ request()->routeIs('anjab.index', 'anjab.show', 'anjab.edit') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                <x-icon name="file-text" class="w-4 h-4 text-teal-600 shrink-0" />
                <span>Katalog Dokumen Anjab</span>
            </x-dropdown-link>
            @if(Auth::user()->canManageAnjabAbk())
                <x-dropdown-link :href="route('anjab.create')" class="flex items-center gap-2">
                    <x-icon name="plus" class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>+ Tambah Dokumen Anjab Baru</span>
                </x-dropdown-link>
            @endif
        </x-slot>
    </x-dropdown>
@endif

{{-- ============================================================== --}}
{{-- 8. MANAJEMEN TALENTA ASN (PermenPAN-RB No. 3/2020)             --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessTalentManagement())
    <x-dropdown align="left" width="w-64">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('manajemen-talenta.*') ? 'bg-rose-50 text-rose-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="target" class="w-4 h-4 {{ request()->routeIs('manajemen-talenta.*') ? 'text-rose-600' : 'text-slate-500' }}" />
                <span>Manajemen Talenta</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Sistem Merit (PermenPAN 3/2020)
            </div>
            <x-dropdown-link :href="route('manajemen-talenta.index')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.index') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                <x-icon name="target" class="w-4 h-4 text-rose-600 shrink-0" />
                <span>Matriks 9-Kotak ASN</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('manajemen-talenta.rekap')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.rekap') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                <x-icon name="clipboard-list" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Rekap Data & Suksesi</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('manajemen-talenta.suksesi.index')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.suksesi.*') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                <x-icon name="crown" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>Rencana Suksesi Jabatan</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Assessment Center BKN
            </div>
            <x-dropdown-link :href="route('manajemen-talenta.asesmen.index')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.asesmen.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                <x-icon name="award" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Uji Kompetensi & Asesmen</span>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@elseif(Auth::user()->hasRole('pegawai'))
    <a href="{{ route('manajemen-talenta.my-talent') }}" 
       class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('manajemen-talenta.my-talent') ? 'bg-rose-50 text-rose-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <x-icon name="sparkles" class="w-4 h-4 {{ request()->routeIs('manajemen-talenta.my-talent') ? 'text-rose-600' : 'text-slate-500' }}" />
        <span>Talenta Saya</span>
    </a>
@endif

{{-- ============================================================== --}}
{{-- 9. MASTER DATA (Khusus Administrator Sistem)                   --}}
{{-- ============================================================== --}}
@if(Auth::user()->hasRole('admin'))
    <x-dropdown align="right" width="w-56">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*', 'jenis-jabatan.*', 'audit-logs.*', 'backup.*') ? 'bg-slate-100 text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="database" class="w-4 h-4 {{ request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*', 'jenis-jabatan.*', 'audit-logs.*', 'backup.*') ? 'text-slate-800' : 'text-slate-500' }}" />
                <span>Master Data</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Struktur Instansi
            </div>
            <x-dropdown-link :href="route('unit-kerja.index')" class="flex items-center gap-2 {{ request()->routeIs('unit-kerja.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="building" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Unit Kerja</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('jabatan.index')" class="flex items-center gap-2 {{ request()->routeIs('jabatan.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="briefcase" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Jabatan</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('golongan.index')" class="flex items-center gap-2 {{ request()->routeIs('golongan.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="list-ordered" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Golongan & Ruang</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('jenis-jabatan.index')" class="flex items-center gap-2 {{ request()->routeIs('jenis-jabatan.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="award" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>Jenis Jabatan</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Sistem & Keamanan
            </div>
            <x-dropdown-link :href="route('audit-logs.index')" class="flex items-center gap-2 {{ request()->routeIs('audit-logs.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="shield-check" class="w-4 h-4 text-violet-600 shrink-0" />
                <span>Audit Log System</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('backup.index')" class="flex items-center gap-2 {{ request()->routeIs('backup.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="database" class="w-4 h-4 text-rose-600 shrink-0" />
                <span>Backup & Restore DB</span>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif
