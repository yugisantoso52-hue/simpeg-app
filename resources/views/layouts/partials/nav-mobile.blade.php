@php
    $currentSection = '';
    if (request()->routeIs('presensi.*', 'admin.presensi.*')) {
        $currentSection = 'presensi';
    } elseif (request()->routeIs('logbook.*', 'admin.logbook.*')) {
        $currentSection = 'logbook';
    } elseif (request()->routeIs('pengajuan-cuti.*')) {
        $currentSection = 'cuti';
    } elseif (request()->routeIs('kepegawaian.*', 'pegawai.*', 'duk.*', 'kp.*', 'kgb.*', 'satyalancana.*', 'mutasi-pegawai.*', 'tugas-belajar.*', 'pengajuan-karir.*')) {
        $currentSection = 'kepegawaian';
    } elseif (request()->routeIs('riwayat-*')) {
        $currentSection = 'riwayat';
    } elseif (request()->routeIs('anjab.*', 'abk.*')) {
        $currentSection = 'anjab';
    } elseif (request()->routeIs('manajemen-talenta.*')) {
        $currentSection = 'talenta';
    } elseif (request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*', 'jenis-jabatan.*', 'audit-logs.*', 'backup.*')) {
        $currentSection = 'master';
    }
@endphp

<div x-data="{ openSection: '{{ $currentSection }}' }" class="pt-2 pb-2 px-2 sm:px-3 space-y-1 notranslate" translate="no">

    {{-- 1. DASHBOARD --}}
    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="flex items-center gap-2.5 rounded-xl font-bold">
        <x-icon name="dashboard" class="w-4 h-4 text-[#007a3d]" />
        <span>Dashboard Utama</span>
    </x-responsive-nav-link>

    {{-- 2. ANALITIK EKSEKUTIF --}}
    @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
        <x-responsive-nav-link :href="route('pimpinan.analytics')" :active="request()->routeIs('pimpinan.analytics*')" class="flex items-center gap-2.5 rounded-xl font-bold">
            <x-icon name="chart" class="w-4 h-4 text-indigo-600" />
            <span>Analitik Eksekutif Dekanat</span>
        </x-responsive-nav-link>
    @endif

    {{-- 3. PROFIL & TALENTA SAYA (PEGAWAI) --}}
    @if(Auth::user()->hasRole('pegawai'))
        <x-responsive-nav-link :href="route('pegawai.my-profile')" :active="request()->routeIs('pegawai.my-profile', 'pegawai.show')" class="flex items-center gap-2.5 rounded-xl font-bold">
            <x-icon name="user" class="w-4 h-4 text-blue-600" />
            <span>Profil Saya</span>
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('pengajuan-karir.index')" :active="request()->routeIs('pengajuan-karir.*')" class="flex items-center gap-2.5 rounded-xl font-bold">
            <x-icon name="award" class="w-4 h-4 text-emerald-600" />
            <span>Usul KGB & Kenaikan Pangkat</span>
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('manajemen-talenta.my-talent')" :active="request()->routeIs('manajemen-talenta.my-talent')" class="flex items-center gap-2.5 rounded-xl font-bold">
            <x-icon name="sparkles" class="w-4 h-4 text-amber-500" />
            <span>Talenta Saya</span>
        </x-responsive-nav-link>
    @endif

    <div class="border-t border-slate-100 my-1.5"></div>

    {{-- 4. PRESENSI PEGAWAI (ACCORDION) --}}
    @if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'presensi' ? '' : 'presensi'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="clock" class="w-4 h-4 text-blue-600" />
                    <span>Presensi Pegawai</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'presensi'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'presensi'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <x-responsive-nav-link :href="route('admin.presensi.index')" :active="request()->routeIs('admin.presensi.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="calendar-check" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Rekap Presensi {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
                </x-responsive-nav-link>
                @if(Auth::user()->hasRole('admin'))
                    <x-responsive-nav-link :href="route('admin.presensi.locations')" :active="request()->routeIs('admin.presensi.locations')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="map-pin" class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Titik Acuan Lokasi Pegawai</span>
                    </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('presensi.index')" :active="request()->routeIs('presensi.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="clock" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Presensi Saya</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('presensi.history')" :active="request()->routeIs('presensi.history')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="history" class="w-3.5 h-3.5 text-slate-500" />
                    <span>Riwayat Presensi Saya</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @else
        <x-responsive-nav-link :href="route('presensi.index')" :active="request()->routeIs('presensi.*')" class="flex items-center gap-2.5 rounded-xl font-bold">
            <x-icon name="clock" class="w-4 h-4 text-blue-600" />
            <span>Presensi Pegawai</span>
        </x-responsive-nav-link>
    @endif

    {{-- 5. LOGBOOK KINERJA (ACCORDION) --}}
    @if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'logbook' ? '' : 'logbook'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="book-open" class="w-4 h-4 text-violet-600" />
                    <span>Logbook Kinerja</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'logbook'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'logbook'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <x-responsive-nav-link :href="route('admin.logbook.index')" :active="request()->routeIs('admin.logbook.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="clipboard-check" class="w-3.5 h-3.5 text-violet-600" />
                    <span>Verifikasi Logbook {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('logbook.index')" :active="request()->routeIs('logbook.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="clipboard-list" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Logbook Kinerja Saya</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('logbook.create')" :active="request()->routeIs('logbook.create')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="plus" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>+ Catat Aktivitas Baru</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @else
        <x-responsive-nav-link :href="route('logbook.index')" :active="request()->routeIs('logbook.*')" class="flex items-center gap-2.5 rounded-xl font-bold">
            <x-icon name="book-open" class="w-4 h-4 text-violet-600" />
            <span>Logbook Kinerja</span>
        </x-responsive-nav-link>
    @endif

    {{-- 6. E-CUTI (ACCORDION) --}}
    @if(Auth::user()->isAtasan() || Auth::user()->hasRole('admin'))
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'cuti' ? '' : 'cuti'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="calendar-off" class="w-4 h-4 text-amber-600" />
                    <span>E-Cuti</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'cuti'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'cuti'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <x-responsive-nav-link :href="route('pengajuan-cuti.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="file-text" class="w-3.5 h-3.5 text-amber-600" />
                    <span>{{ Auth::user()->hasRole('admin') ? 'Monitoring Cuti Pegawai' : 'Persetujuan Cuti Bawahan' }}</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pengajuan-cuti.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="calendar-off" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Permohonan Cuti Saya</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pengajuan-cuti.create')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="plus" class="w-3.5 h-3.5 text-blue-600" />
                    <span>+ Buat Permohonan Cuti Baru</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @else
        <x-responsive-nav-link :href="route('pengajuan-cuti.index')" :active="request()->routeIs('pengajuan-cuti.*')" class="flex items-center gap-2.5 rounded-xl font-bold">
            <x-icon name="calendar-off" class="w-4 h-4 text-amber-600" />
            <span>E-Cuti Pegawai</span>
        </x-responsive-nav-link>
    @endif

    {{-- 7. LAYANAN KEPEGAWAIAN (ACCORDION) --}}
    @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'kepegawaian' ? '' : 'kepegawaian'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="users" class="w-4 h-4 text-emerald-600" />
                    <span>Data & Layanan Kepegawaian</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'kepegawaian'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'kepegawaian'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Data Induk</div>
                <x-responsive-nav-link :href="route('pegawai.index')" :active="request()->routeIs('pegawai.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="users" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Semua Pegawai (Master)</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kepegawaian.dosen.index')" :active="request()->routeIs('kepegawaian.dosen.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="user" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Data Dosen</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kepegawaian.tendik.index')" :active="request()->routeIs('kepegawaian.tendik.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="user" class="w-3.5 h-3.5 text-teal-600" />
                    <span>Data Tenaga Kependidikan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kepegawaian.phl.index')" :active="request()->routeIs('kepegawaian.phl.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="user" class="w-3.5 h-3.5 text-amber-600" />
                    <span>Data PHL / Honorer</span>
                </x-responsive-nav-link>

                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Layanan Karir</div>
                <x-responsive-nav-link :href="route('duk.index')" :active="request()->routeIs('duk.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="list-ordered" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Daftar Urut Kepangkatan (DUK)</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('pengajuan-karir.index')" :active="request()->routeIs('pengajuan-karir.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="clipboard-check" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Verifikasi Usulan KGB & KP</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kp.index')" :active="request()->routeIs('kp.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Kenaikan Pangkat (KP)</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('kgb.index')" :active="request()->routeIs('kgb.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="banknote" class="w-3.5 h-3.5 text-amber-600" />
                    <span>Kenaikan Gaji Berkala (KGB)</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('satyalancana.index')" :active="request()->routeIs('satyalancana.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Satyalancana Karya Satya</span>
                </x-responsive-nav-link>
                @if(Auth::user()->hasRole('admin'))
                    <x-responsive-nav-link :href="route('mutasi-pegawai.index')" :active="request()->routeIs('mutasi-pegawai.*')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="git-branch" class="w-3.5 h-3.5 text-blue-600" />
                        <span>Mutasi Pegawai</span>
                    </x-responsive-nav-link>
                @endif
                <x-responsive-nav-link :href="route('tugas-belajar.index')" :active="request()->routeIs('tugas-belajar.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="graduation-cap" class="w-3.5 h-3.5 text-rose-600" />
                    <span>Tugas Belajar & Studi</span>
                </x-responsive-nav-link>
            </div>
        </div>

        {{-- 8. RIWAYAT PEGAWAI (ACCORDION) --}}
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'riwayat' ? '' : 'riwayat'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="history" class="w-4 h-4 text-blue-600" />
                    <span>Riwayat & Dokumen Pegawai</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'riwayat'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'riwayat'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <x-responsive-nav-link :href="route('riwayat-pendidikan.index')" :active="request()->routeIs('riwayat-pendidikan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="graduation-cap" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Riwayat Pendidikan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-diklat.index')" :active="request()->routeIs('riwayat-diklat.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="file-text" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Riwayat Diklat</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-str-sip.index')" :active="request()->routeIs('riwayat-str-sip.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="stethoscope" class="w-3.5 h-3.5 text-sky-600" />
                    <span>Riwayat STR & SIP</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-skp.index')" :active="request()->routeIs('riwayat-skp.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="chart" class="w-3.5 h-3.5 text-teal-600" />
                    <span>Riwayat SKP</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-publikasi.index')" :active="request()->routeIs('riwayat-publikasi.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="book-open" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Riwayat Publikasi</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-pangkat.index')" :active="request()->routeIs('riwayat-pangkat.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-amber-600" />
                    <span>Riwayat Pangkat</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-jabatan.index')" :active="request()->routeIs('riwayat-jabatan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="briefcase" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Riwayat Jabatan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-penghargaan.index')" :active="request()->routeIs('riwayat-penghargaan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-rose-600" />
                    <span>Riwayat Penghargaan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-organisasi.index')" :active="request()->routeIs('riwayat-organisasi.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="building" class="w-3.5 h-3.5 text-slate-600" />
                    <span>Riwayat Organisasi</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @endif

    {{-- 9. ANJAB & ABK (ACCORDION) --}}
    @if(Auth::user()->canAccessAnjabAbk())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'anjab' ? '' : 'anjab'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="scale" class="w-4 h-4 text-teal-600" />
                    <span>Anjab & ABK</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'anjab'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'anjab'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <x-responsive-nav-link :href="route('anjab.peta-jabatan')" :active="request()->routeIs('anjab.peta-jabatan*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="git-branch" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Peta Jabatan Digital</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('abk.index')" :active="request()->routeIs('abk.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="chart" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Analisis Beban Kerja (ABK)</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('anjab.index')" :active="request()->routeIs('anjab.index', 'anjab.show', 'anjab.edit')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="file-text" class="w-3.5 h-3.5 text-teal-600" />
                    <span>Katalog Dokumen Anjab</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @endif

    {{-- 10. MANAJEMEN TALENTA ASN (ACCORDION) --}}
    @if(Auth::user()->canAccessTalentManagement())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'talenta' ? '' : 'talenta'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="target" class="w-4 h-4 text-rose-600" />
                    <span>Manajemen Talenta ASN</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'talenta'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'talenta'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <x-responsive-nav-link :href="route('manajemen-talenta.index')" :active="request()->routeIs('manajemen-talenta.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="target" class="w-3.5 h-3.5 text-rose-600" />
                    <span>Matriks 9-Kotak ASN</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manajemen-talenta.rekap')" :active="request()->routeIs('manajemen-talenta.rekap')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="clipboard-list" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Rekap Data & Suksesi</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manajemen-talenta.suksesi.index')" :active="request()->routeIs('manajemen-talenta.suksesi.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="crown" class="w-3.5 h-3.5 text-amber-600" />
                    <span>Rencana Suksesi Jabatan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manajemen-talenta.asesmen.index')" :active="request()->routeIs('manajemen-talenta.asesmen.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Uji Kompetensi & Asesmen</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @endif

    {{-- 11. MASTER DATA (ACCORDION) --}}
    @if(Auth::user()->hasRole('admin'))
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'master' ? '' : 'master'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="database" class="w-4 h-4 text-slate-700" />
                    <span>Master Data & Sistem</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'master'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'master'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <x-responsive-nav-link :href="route('unit-kerja.index')" :active="request()->routeIs('unit-kerja.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="building" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Unit Kerja</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('jabatan.index')" :active="request()->routeIs('jabatan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="briefcase" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Jabatan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('golongan.index')" :active="request()->routeIs('golongan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="list-ordered" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Golongan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('jenis-jabatan.index')" :active="request()->routeIs('jenis-jabatan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-amber-600" />
                    <span>Jenis Jabatan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('audit-logs.index')" :active="request()->routeIs('audit-logs.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="shield-check" class="w-3.5 h-3.5 text-violet-600" />
                    <span>Audit Log System</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('backup.index')" :active="request()->routeIs('backup.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="database" class="w-3.5 h-3.5 text-rose-600" />
                    <span>Backup & Restore DB</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @endif

</div>
