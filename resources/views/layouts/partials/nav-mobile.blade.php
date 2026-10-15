@php
    $currentSection = '';
    if (request()->routeIs('presensi.*', 'admin.presensi.*', 'logbook.*', 'admin.logbook.*')) {
        $currentSection = 'presensi-kinerja';
    } elseif (request()->routeIs('pengajuan-cuti.*', 'layanan-surat.*', 'regulasi-tata-naskah.*')) {
        $currentSection = 'tata-naskah';
    } elseif (request()->routeIs('pegawai.*', 'kepegawaian.*', 'duk.*', 'riwayat-*')) {
        $currentSection = 'kepegawaian';
    } elseif (request()->routeIs('kp.*', 'kgb.*', 'satyalancana.*', 'mutasi-pegawai.*', 'tugas-belajar.*', 'pengajuan-karir.*')) {
        $currentSection = 'karir';
    } elseif (request()->routeIs('anjab.*', 'abk.*', 'manajemen-talenta.*')) {
        $currentSection = 'perencanaan-talenta';
    } elseif (request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*', 'jenis-jabatan.*', 'audit-logs.*', 'backup.*')) {
        $currentSection = 'master';
    }
@endphp

<div x-data="{ openSection: '{{ $currentSection }}' }" class="pt-2 pb-2 px-2 sm:px-3 space-y-1.5 notranslate" translate="no">

    {{-- 1. DASHBOARD UTAMA --}}
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

    {{-- 3. PROFIL & KARIR SAYA (PEGAWAI MANDIRI NON-EKSEKUTIF) --}}
    @if(Auth::user()->hasRole('pegawai') && !Auth::user()->canAccessExecutiveKepegawaianMenus())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'profil-karir' ? '' : 'profil-karir'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="user" class="w-4 h-4 text-blue-600" />
                    <span>Profil & Karir Saya</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'profil-karir'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'profil-karir'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Profil Pegawai</div>
                <x-responsive-nav-link :href="route('pegawai.my-profile')" :active="request()->routeIs('pegawai.my-profile', 'pegawai.show')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="user" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Profil Saya</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('duk.index')" :active="request()->routeIs('duk.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="list-ordered" class="w-3.5 h-3.5 text-sky-600" />
                    <span>Daftar Urut Kepangkatan (DUK)</span>
                </x-responsive-nav-link>

                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Layanan Karir Mandiri</div>
                <x-responsive-nav-link :href="route('pengajuan-karir.index')" :active="request()->routeIs('pengajuan-karir.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Usulan Karir & Satyalancana</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('manajemen-talenta.my-talent')" :active="request()->routeIs('manajemen-talenta.my-talent')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="sparkles" class="w-3.5 h-3.5 text-rose-500" />
                    <span>Portofolio Talenta Saya</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @endif

    {{-- 4. DATA KEPEGAWAIAN (DATA INDUK & RIWAYAT - EKSEKUTIF) --}}
    @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'kepegawaian' ? '' : 'kepegawaian'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="users" class="w-4 h-4 text-emerald-600" />
                    <span>Data Kepegawaian</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'kepegawaian'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'kepegawaian'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Data Induk</div>
                <x-responsive-nav-link :href="route('pegawai.index')" :active="request()->routeIs('pegawai.index', 'pegawai.edit', 'pegawai.create')" class="flex items-center gap-2 text-xs rounded-lg">
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
                <x-responsive-nav-link :href="route('duk.index')" :active="request()->routeIs('duk.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="list-ordered" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Daftar Urut Kepangkatan (DUK)</span>
                </x-responsive-nav-link>

                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Riwayat & Portofolio ASN</div>
                <x-responsive-nav-link :href="route('riwayat-pangkat.index')" :active="request()->routeIs('riwayat-pangkat.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-amber-600" />
                    <span>Riwayat Pangkat & Golongan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-jabatan.index')" :active="request()->routeIs('riwayat-jabatan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="briefcase" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Riwayat Jabatan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-pendidikan.index')" :active="request()->routeIs('riwayat-pendidikan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="graduation-cap" class="w-3.5 h-3.5 text-sky-600" />
                    <span>Riwayat Pendidikan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-diklat.index')" :active="request()->routeIs('riwayat-diklat.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="file-text" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Riwayat Diklat & Kursus</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-skp.index')" :active="request()->routeIs('riwayat-skp.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="chart" class="w-3.5 h-3.5 text-teal-600" />
                    <span>Riwayat SKP & Kinerja</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-str-sip.index')" :active="request()->routeIs('riwayat-str-sip.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="stethoscope" class="w-3.5 h-3.5 text-rose-500" />
                    <span>Riwayat STR & SIP Medis</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('riwayat-publikasi.index')" :active="request()->routeIs('riwayat-publikasi.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="book-open" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Riwayat Publikasi Ilmiah</span>
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

    {{-- 5. LAYANAN KARIR & MUTASI (EKSEKUTIF) --}}
    @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'karir' ? '' : 'karir'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="award" class="w-4 h-4 text-amber-600" />
                    <span>Layanan Karir & Mutasi</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'karir'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'karir'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pengusulan & Verifikasi</div>
                <x-responsive-nav-link :href="route('pengajuan-karir.index')" :active="request()->routeIs('pengajuan-karir.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="clipboard-check" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Verifikasi Usulan Karir & SLKS</span>
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

                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mutasi & Pengembangan</div>
                <x-responsive-nav-link :href="route('mutasi-pegawai.index')" :active="request()->routeIs('mutasi-pegawai.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="git-branch" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Mutasi Pegawai</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('tugas-belajar.index')" :active="request()->routeIs('tugas-belajar.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="graduation-cap" class="w-3.5 h-3.5 text-rose-600" />
                    <span>Tugas Belajar & Studi</span>
                </x-responsive-nav-link>
            </div>
        </div>
    @endif

    {{-- 6. PRESENSI & LOGBOOK KINERJA (SEMUA PENGGUNA) --}}
    <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
        <button @click="openSection = openSection === 'presensi-kinerja' ? '' : 'presensi-kinerja'" 
                type="button" 
                class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
            <span class="flex items-center gap-2">
                <x-icon name="clock" class="w-4 h-4 text-blue-600" />
                <span>Presensi & Kinerja</span>
            </span>
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'presensi-kinerja'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div x-show="openSection === 'presensi-kinerja'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
            <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Presensi Kehadiran</div>
            @if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
                <x-responsive-nav-link :href="route('admin.presensi.index')" :active="request()->routeIs('admin.presensi.index')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="calendar-check" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Rekap Presensi {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
                </x-responsive-nav-link>
                @if(Auth::user()->hasRole('admin') || Auth::user()->canAccessExecutiveKepegawaianMenus())
                    <x-responsive-nav-link :href="route('admin.presensi.locations')" :active="request()->routeIs('admin.presensi.locations')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="map-pin" class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Titik Acuan Lokasi GPS</span>
                    </x-responsive-nav-link>
                @endif
            @endif
            <x-responsive-nav-link :href="route('presensi.index')" :active="request()->routeIs('presensi.index')" class="flex items-center gap-2 text-xs rounded-lg">
                <x-icon name="clock" class="w-3.5 h-3.5 text-emerald-600" />
                <span>Presensi Harian Saya</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('presensi.history')" :active="request()->routeIs('presensi.history')" class="flex items-center gap-2 text-xs rounded-lg">
                <x-icon name="history" class="w-3.5 h-3.5 text-slate-500" />
                <span>Riwayat Presensi Saya</span>
            </x-responsive-nav-link>

            <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Logbook Aktivitas Kinerja</div>
            @if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
                <x-responsive-nav-link :href="route('admin.logbook.index')" :active="request()->routeIs('admin.logbook.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="clipboard-check" class="w-3.5 h-3.5 text-violet-600" />
                    <span>Verifikasi Logbook {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
                </x-responsive-nav-link>
            @endif
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

    {{-- 7. TATA NASKAH & E-LAYANAN (CUTI & SURAT - SEMUA PENGGUNA) --}}
    <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
        <button @click="openSection = openSection === 'tata-naskah' ? '' : 'tata-naskah'" 
                type="button" 
                class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
            <span class="flex items-center gap-2">
                <x-icon name="file-text" class="w-4 h-4 text-purple-600" />
                <span>Tata Naskah & E-Layanan</span>
            </span>
            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'tata-naskah'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div x-show="openSection === 'tata-naskah'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
            <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Layanan Cuti ASN</div>
            @if(Auth::user()->isAtasan() || Auth::user()->hasRole('admin'))
                <x-responsive-nav-link :href="route('pengajuan-cuti.index')" :active="request()->routeIs('pengajuan-cuti.index') && !request()->has('mine')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="file-text" class="w-3.5 h-3.5 text-amber-600" />
                    <span>{{ Auth::user()->hasRole('admin') ? 'Monitoring Seluruh Cuti' : 'Persetujuan Cuti Bawahan' }}</span>
                </x-responsive-nav-link>
            @endif
            <x-responsive-nav-link :href="route('pengajuan-cuti.index')" :active="request()->routeIs('pengajuan-cuti.index') && request()->has('mine')" class="flex items-center gap-2 text-xs rounded-lg">
                <x-icon name="calendar-off" class="w-3.5 h-3.5 text-emerald-600" />
                <span>Permohonan Cuti Saya</span>
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('pengajuan-cuti.create')" :active="request()->routeIs('pengajuan-cuti.create')" class="flex items-center gap-2 text-xs rounded-lg">
                <x-icon name="plus" class="w-3.5 h-3.5 text-blue-600" />
                <span>+ Buat Permohonan Cuti Baru</span>
            </x-responsive-nav-link>

            <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Layanan Persuratan</div>
            <x-responsive-nav-link :href="route('layanan-surat.index')" :active="request()->routeIs('layanan-surat.*')" class="flex items-center gap-2 text-xs rounded-lg">
                <x-icon name="file-text" class="w-3.5 h-3.5 text-blue-600" />
                <span>E-Surat Keterangan Aktif Bekerja</span>
            </x-responsive-nav-link>

            <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tata Naskah & SOP</div>
            <x-responsive-nav-link :href="route('regulasi-tata-naskah.index')" :active="request()->routeIs('regulasi-tata-naskah.*')" class="flex items-center gap-2 text-xs rounded-lg">
                <x-icon name="scale" class="w-3.5 h-3.5 text-indigo-600" />
                <span>Regulasi & SOP Paraf Digital</span>
            </x-responsive-nav-link>
        </div>
    </div>

    {{-- 8. PERENCANAAN & TALENTA ASN (ANJAB, ABK & MERIT 9-BOX) --}}
    @if(Auth::user()->canAccessAnjabAbk() || Auth::user()->canAccessTalentManagement())
        <div class="rounded-xl border border-slate-200/80 bg-white overflow-hidden transition">
            <button @click="openSection = openSection === 'perencanaan-talenta' ? '' : 'perencanaan-talenta'" 
                    type="button" 
                    class="w-full flex items-center justify-between px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                <span class="flex items-center gap-2">
                    <x-icon name="target" class="w-4 h-4 text-rose-600" />
                    <span>Perencanaan & Talenta ASN</span>
                </span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="{'rotate-180': openSection === 'perencanaan-talenta'}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="openSection === 'perencanaan-talenta'" x-transition class="px-2 pb-2 space-y-0.5 bg-slate-50/60 border-t border-slate-100">
                @if(Auth::user()->canAccessAnjabAbk())
                    <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Analisis Jabatan & ABK</div>
                    <x-responsive-nav-link :href="route('anjab.peta-jabatan')" :active="request()->routeIs('anjab.peta-jabatan*')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="git-branch" class="w-3.5 h-3.5 text-teal-600" />
                        <span>Peta Jabatan Digital</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('abk.index')" :active="request()->routeIs('abk.index', 'abk.edit')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="chart" class="w-3.5 h-3.5 text-blue-600" />
                        <span>Analisis Beban Kerja (ABK)</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('abk.simulasi-formasi')" :active="request()->routeIs('abk.simulasi-formasi')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="target" class="w-3.5 h-3.5 text-purple-600" />
                        <span>Simulasi Formasi CASN</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('anjab.index')" :active="request()->routeIs('anjab.index', 'anjab.show', 'anjab.edit', 'anjab.evaluasi*')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="file-text" class="w-3.5 h-3.5 text-teal-600" />
                        <span>Katalog Dokumen Anjab</span>
                    </x-responsive-nav-link>
                    @if(Auth::user()->canManageAnjabAbk())
                        <x-responsive-nav-link :href="route('anjab.create')" class="flex items-center gap-2 text-xs rounded-lg">
                            <x-icon name="plus" class="w-3.5 h-3.5 text-emerald-600" />
                            <span>+ Tambah Dokumen Anjab Baru</span>
                        </x-responsive-nav-link>
                    @endif
                @endif

                @if(Auth::user()->canAccessTalentManagement())
                    <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Sistem Merit & Talenta (PermenPAN 3/2020)</div>
                    <x-responsive-nav-link :href="route('manajemen-talenta.index')" :active="request()->routeIs('manajemen-talenta.index')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="target" class="w-3.5 h-3.5 text-rose-600" />
                        <span>Matriks 9-Kotak ASN</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manajemen-talenta.rekap')" :active="request()->routeIs('manajemen-talenta.rekap')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="clipboard-list" class="w-3.5 h-3.5 text-emerald-600" />
                        <span>Rekap Data & Pemetaan</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manajemen-talenta.suksesi.index')" :active="request()->routeIs('manajemen-talenta.suksesi.*')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="crown" class="w-3.5 h-3.5 text-amber-600" />
                        <span>Rencana Suksesi Jabatan</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manajemen-talenta.idp')" :active="request()->routeIs('manajemen-talenta.idp*', 'manajemen-talenta.pegawai.idp*')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="book-open" class="w-3.5 h-3.5 text-teal-600" />
                        <span>Pengembangan 20 JP (IDP)</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manajemen-talenta.sidang-komite.index')" :active="request()->routeIs('manajemen-talenta.sidang-komite*')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="scale" class="w-3.5 h-3.5 text-indigo-600" />
                        <span>Sidang Komite Talenta</span>
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('manajemen-talenta.asesmen.index')" :active="request()->routeIs('manajemen-talenta.asesmen.*')" class="flex items-center gap-2 text-xs rounded-lg">
                        <x-icon name="award" class="w-3.5 h-3.5 text-indigo-600" />
                        <span>Uji Kompetensi & Asesmen</span>
                    </x-responsive-nav-link>
                @endif
            </div>
        </div>
    @endif

    {{-- 9. MASTER DATA & SISTEM (ADMIN) --}}
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
                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Struktur Instansi</div>
                <x-responsive-nav-link :href="route('unit-kerja.index')" :active="request()->routeIs('unit-kerja.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="building" class="w-3.5 h-3.5 text-blue-600" />
                    <span>Unit Kerja</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('jabatan.index')" :active="request()->routeIs('jabatan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="briefcase" class="w-3.5 h-3.5 text-indigo-600" />
                    <span>Master Jabatan</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('golongan.index')" :active="request()->routeIs('golongan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="list-ordered" class="w-3.5 h-3.5 text-emerald-600" />
                    <span>Golongan & Ruang</span>
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('jenis-jabatan.index')" :active="request()->routeIs('jenis-jabatan.*')" class="flex items-center gap-2 text-xs rounded-lg">
                    <x-icon name="award" class="w-3.5 h-3.5 text-amber-600" />
                    <span>Jenis Jabatan</span>
                </x-responsive-nav-link>

                <div class="px-2 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Keamanan & Pemeliharaan</div>
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
