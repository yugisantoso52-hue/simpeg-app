{{-- ============================================================== --}}
{{-- 1. DASHBOARD UTAMA (Akses Seluruh Pengguna)                    --}}
{{-- ============================================================== --}}
<a href="{{ route('dashboard') }}" 
   class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('dashboard') ? 'bg-[#007a3d]/10 text-[#007a3d] font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
    <x-icon name="dashboard" class="w-4 h-4 {{ request()->routeIs('dashboard') ? 'text-[#007a3d]' : 'text-slate-500' }}" />
    <span>Dashboard</span>
</a>

{{-- ============================================================== --}}
{{-- 2. ANALITIK EKSEKUTIF (Dekan, Wadek, Kabag Umum, Pokja, Admin) --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessExecutiveKepegawaianMenus())
    <a href="{{ route('pimpinan.analytics') }}" 
       class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('pimpinan.analytics*') ? 'bg-indigo-50 text-indigo-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
        <x-icon name="chart" class="w-4 h-4 {{ request()->routeIs('pimpinan.analytics*') ? 'text-indigo-600' : 'text-slate-500' }}" />
        <span>Analitik Eksekutif</span>
    </a>
@endif

{{-- ============================================================== --}}
{{-- 3. PROFIL & KARIR SAYA (Pegawai Mandiri & Unsur Pimpinan)      --}}
{{-- ============================================================== --}}
@if(Auth::user()->hasRole(['pegawai', 'pimpinan']) || Auth::user()->pegawai_id)
    @php
        $routePegawaiParam = request()->route('pegawai');
        $routePegawaiId = is_object($routePegawaiParam) ? $routePegawaiParam->id : $routePegawaiParam;
        $isViewingOwnProfile = request()->routeIs('pegawai.my-profile')
            || (request()->routeIs('pegawai.show', 'pegawai.edit') && Auth::user()->pegawai_id && (int) $routePegawaiId === (int) Auth::user()->pegawai_id);
        $isProfilKarirActive = $isViewingOwnProfile
            || request()->routeIs('manajemen-talenta.my-talent')
            || (!Auth::user()->canAccessExecutiveKepegawaianMenus() && request()->routeIs('duk.*', 'pengajuan-karir.*'));
    @endphp
    <x-dropdown align="left" width="w-64">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ $isProfilKarirActive ? 'bg-blue-50 text-blue-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="user" class="w-4 h-4 {{ $isProfilKarirActive ? 'text-blue-600' : 'text-slate-500' }}" />
                <span>Profil & Karir Saya</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Profil Pegawai
            </div>
            <x-dropdown-link :href="route('pegawai.my-profile')" class="flex items-center gap-2 {{ $isViewingOwnProfile ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <x-icon name="user" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Profil Saya</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('duk.index')" class="flex items-center gap-2 {{ request()->routeIs('duk.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <x-icon name="list-ordered" class="w-4 h-4 text-sky-600 shrink-0" />
                <span>Daftar Urut Kepangkatan (DUK)</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Layanan Karir Mandiri
            </div>
            <x-dropdown-link :href="route('pengajuan-karir.index', ['tab' => 'kp', 'jenis' => 'KP'])" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-karir.*') && request('tab') === 'kp' ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                <x-icon name="award" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Kenaikan Pangkat (KP)</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('pengajuan-karir.index', ['tab' => 'kgb', 'jenis' => 'KGB'])" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-karir.*') && request('tab') === 'kgb' ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                <x-icon name="banknote" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>Kenaikan Gaji Berkala (KGB)</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('pengajuan-karir.index', ['tab' => 'satyalancana'])" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-karir.*') && request('tab') === 'satyalancana' ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                <x-icon name="award" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Satyalancana Karya Satya</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('pengajuan-karir.index', Auth::user()->canAccessExecutiveKepegawaianMenus() ? ['scope' => 'saya'] : [])" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-karir.*') && !request()->has('tab') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                <x-icon name="clipboard-check" class="w-4 h-4 text-teal-600 shrink-0" />
                <span>Riwayat Usulan Karir Saya</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('tugas-belajar.index')" class="flex items-center gap-2 {{ request()->routeIs('tugas-belajar.*') ? 'bg-sky-50 text-sky-700 font-semibold' : '' }}">
                <x-icon name="graduation-cap" class="w-4 h-4 text-sky-600 shrink-0" />
                <span>Tugas Belajar & Studi Saya</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('manajemen-talenta.my-talent')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.my-talent') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                <x-icon name="sparkles" class="w-4 h-4 text-rose-500 shrink-0" />
                <span>Portofolio Talenta Saya</span>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif

{{-- ============================================================== --}}
{{-- 4. DATA INDUK & RIWAYAT (Khusus Pimpinan & Admin Eksekutif)    --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessExecutiveKepegawaianMenus())
    @php
        $isKepegawaianActive = (request()->routeIs('pegawai.*', 'kepegawaian.*', 'duk.*', 'riwayat-*')) && !($isViewingOwnProfile ?? false);
    @endphp
    <x-dropdown align="left" width="w-72">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ $isKepegawaianActive ? 'bg-emerald-50 text-emerald-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="users" class="w-4 h-4 {{ $isKepegawaianActive ? 'text-emerald-600' : 'text-slate-500' }}" />
                <span>Data Kepegawaian</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Data Induk Pegawai
            </div>
            <x-dropdown-link :href="route('pegawai.index')" class="flex items-center gap-2 {{ request()->routeIs('pegawai.index', 'pegawai.edit', 'pegawai.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                <x-icon name="users" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>Semua Pegawai (Master)</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('kepegawaian.dosen.index')" class="flex items-center gap-2 {{ request()->routeIs('kepegawaian.dosen.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <x-icon name="user" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Data Dosen</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('kepegawaian.tendik.index')" class="flex items-center gap-2 {{ request()->routeIs('kepegawaian.tendik.*') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                <x-icon name="user" class="w-4 h-4 text-teal-600 shrink-0" />
                <span>Data Tenaga Kependidikan</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('kepegawaian.phl.index')" class="flex items-center gap-2 {{ request()->routeIs('kepegawaian.phl.*') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                <x-icon name="user" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>Data PHL / Pegawai Honorer</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('duk.index')" class="flex items-center gap-2 {{ request()->routeIs('duk.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                <x-icon name="list-ordered" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Daftar Urut Kepangkatan (DUK)</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Riwayat & Portofolio ASN
            </div>
            <div class="max-h-64 overflow-y-auto">
                <x-dropdown-link :href="route('riwayat-pangkat.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-pangkat.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="award" class="w-4 h-4 text-amber-600 shrink-0" />
                    <span>Riwayat Pangkat & Golongan</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-jabatan.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-jabatan.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="briefcase" class="w-4 h-4 text-blue-600 shrink-0" />
                    <span>Riwayat Jabatan</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-pendidikan.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-pendidikan.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="graduation-cap" class="w-4 h-4 text-sky-600 shrink-0" />
                    <span>Riwayat Pendidikan</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-diklat.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-diklat.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="file-text" class="w-4 h-4 text-indigo-600 shrink-0" />
                    <span>Riwayat Diklat & Kursus</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-skp.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-skp.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="chart" class="w-4 h-4 text-teal-600 shrink-0" />
                    <span>Riwayat SKP & Kinerja</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-str-sip.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-str-sip.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="stethoscope" class="w-4 h-4 text-rose-500 shrink-0" />
                    <span>Riwayat STR & SIP Medis</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-publikasi.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-publikasi.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="book-open" class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Riwayat Publikasi Ilmiah</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-penghargaan.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-penghargaan.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="award" class="w-4 h-4 text-rose-600 shrink-0" />
                    <span>Riwayat Penghargaan</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('riwayat-organisasi.index')" class="flex items-center gap-2 {{ request()->routeIs('riwayat-organisasi.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="building" class="w-4 h-4 text-slate-600 shrink-0" />
                    <span>Riwayat Organisasi</span>
                </x-dropdown-link>
            </div>
        </x-slot>
    </x-dropdown>
@endif

{{-- ============================================================== --}}
{{-- 5. LAYANAN KARIR & MUTASI (Khusus Pimpinan & Admin Eksekutif)  --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessExecutiveKepegawaianMenus())
    <x-dropdown align="left" width="w-68">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('kp.*', 'kgb.*', 'satyalancana.*', 'mutasi-pegawai.*', 'tugas-belajar.*', 'pengajuan-karir.*') ? 'bg-amber-50 text-amber-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="award" class="w-4 h-4 {{ request()->routeIs('kp.*', 'kgb.*', 'satyalancana.*', 'mutasi-pegawai.*', 'tugas-belajar.*', 'pengajuan-karir.*') ? 'text-amber-600' : 'text-slate-500' }}" />
                <span>Layanan Karir</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Pengusulan & Verifikasi Karir
            </div>
            <x-dropdown-link :href="route('pengajuan-karir.index')" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-karir.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                <x-icon name="clipboard-check" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Verifikasi Usulan Karir & SLKS</span>
            </x-dropdown-link>
            @if(Auth::user()->hasRole('admin'))
                <x-dropdown-link :href="route('kp.index')" class="flex items-center gap-2 {{ request()->routeIs('kp.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                    <x-icon name="award" class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Kenaikan Pangkat (KP)</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('kgb.index')" class="flex items-center gap-2 {{ request()->routeIs('kgb.*') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                    <x-icon name="banknote" class="w-4 h-4 text-amber-600 shrink-0" />
                    <span>Kenaikan Gaji Berkala (KGB)</span>
                </x-dropdown-link>
            @endif
            <x-dropdown-link :href="route('satyalancana.index')" class="flex items-center gap-2 {{ request()->routeIs('satyalancana.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                <x-icon name="award" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Satyalancana Karya Satya</span>
            </x-dropdown-link>

            <div class="border-t border-slate-100 my-1"></div>
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Mutasi & Pengembangan
            </div>
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
        </x-slot>
    </x-dropdown>
@endif

{{-- ============================================================== --}}
{{-- 6. PRESENSI & KINERJA (Akses Seluruh Pengguna)                 --}}
{{-- ============================================================== --}}
<x-dropdown align="left" width="w-68">
    <x-slot name="trigger">
        <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('presensi.*', 'admin.presensi.*', 'logbook.*', 'admin.logbook.*') ? 'bg-blue-50 text-blue-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <x-icon name="clock" class="w-4 h-4 {{ request()->routeIs('presensi.*', 'admin.presensi.*', 'logbook.*', 'admin.logbook.*') ? 'text-blue-600' : 'text-slate-500' }}" />
            <span>Presensi & Kinerja</span>
            <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
        </button>
    </x-slot>
    <x-slot name="content">
        {{-- BAGIAN PRESENSI --}}
        <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
            Presensi Kehadiran
        </div>
        @if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
            <x-dropdown-link :href="route('admin.presensi.index')" class="flex items-center gap-2 {{ request()->routeIs('admin.presensi.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                <x-icon name="calendar-check" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>Rekap Presensi {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
            </x-dropdown-link>
            @if(Auth::user()->hasRole('admin') || Auth::user()->canAccessExecutiveKepegawaianMenus())
                <x-dropdown-link :href="route('admin.presensi.locations')" class="flex items-center gap-2 {{ request()->routeIs('admin.presensi.locations') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                    <x-icon name="map-pin" class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Titik Lokasi Pegawai (GPS)</span>
                </x-dropdown-link>
            @endif
        @endif
        <x-dropdown-link :href="route('presensi.index')" class="flex items-center gap-2 {{ request()->routeIs('presensi.index') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
            <x-icon name="clock" class="w-4 h-4 text-emerald-600 shrink-0" />
            <span>Presensi Harian Saya</span>
        </x-dropdown-link>
        <x-dropdown-link :href="route('presensi.history')" class="flex items-center gap-2 {{ request()->routeIs('presensi.history') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
            <x-icon name="history" class="w-4 h-4 text-slate-500 shrink-0" />
            <span>Riwayat Presensi Saya</span>
        </x-dropdown-link>

        {{-- BAGIAN LOGBOOK --}}
        <div class="border-t border-slate-100 my-1"></div>
        <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
            Logbook Aktivitas Kinerja
        </div>
        @if(Auth::user()->hasRole('admin') || Auth::user()->isAtasan())
            <x-dropdown-link :href="route('admin.logbook.index')" class="flex items-center gap-2 {{ request()->routeIs('admin.logbook.*') ? 'bg-violet-50 text-violet-700 font-semibold' : '' }}">
                <x-icon name="clipboard-check" class="w-4 h-4 text-violet-600 shrink-0" />
                <span>Verifikasi Logbook {{ Auth::user()->hasRole('admin') ? 'Pegawai' : 'Bawahan' }}</span>
            </x-dropdown-link>
        @endif
        <x-dropdown-link :href="route('logbook.index')" class="flex items-center gap-2 {{ request()->routeIs('logbook.index') ? 'bg-violet-50 text-violet-700 font-semibold' : '' }}">
            <x-icon name="clipboard-list" class="w-4 h-4 text-blue-600 shrink-0" />
            <span>Logbook Kinerja Saya</span>
        </x-dropdown-link>
        <x-dropdown-link :href="route('logbook.create')" class="flex items-center gap-2 {{ request()->routeIs('logbook.create') ? 'bg-violet-50 text-violet-700 font-semibold' : '' }}">
            <x-icon name="plus" class="w-4 h-4 text-emerald-600 shrink-0" />
            <span>+ Catat Aktivitas Baru</span>
        </x-dropdown-link>
    </x-slot>
</x-dropdown>

{{-- ============================================================== --}}
{{-- 7. TATA NASKAH & E-LAYANAN (Cuti, Surat, & Regulasi SOP)       --}}
{{-- ============================================================== --}}
<x-dropdown align="left" width="w-72">
    <x-slot name="trigger">
        <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('pengajuan-cuti.*', 'layanan-surat.*', 'regulasi-tata-naskah.*', 'pengajuan-karir.*') ? 'bg-purple-50 text-purple-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
            <x-icon name="file-text" class="w-4 h-4 {{ request()->routeIs('pengajuan-cuti.*', 'layanan-surat.*', 'regulasi-tata-naskah.*', 'pengajuan-karir.*') ? 'text-purple-600' : 'text-slate-500' }}" />
            <span>Tata Naskah & E-Layanan</span>
            <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
        </button>
    </x-slot>
    <x-slot name="content">
        {{-- MEJA PENGESAHAN BERJENJANG (SOP 4 TAHAP) --}}
        @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <span>Meja Pengesahan Berjenjang</span>
                <span class="text-[9px] font-extrabold text-indigo-700 bg-indigo-100 px-1.5 py-0.2 rounded">SOP 4 Tahap</span>
            </div>
            <x-dropdown-link :href="route('dashboard') . '#section-action-center'" class="flex items-center gap-2">
                <x-icon name="shield-check" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>Executive Action Center</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('layanan-surat.index')" class="flex items-center gap-2 {{ request()->routeIs('layanan-surat.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                <x-icon name="file-text" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>E-Surat Keterangan Aktif</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('pengajuan-karir.index')" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-karir.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                <x-icon name="award" class="w-4 h-4 text-indigo-600 shrink-0" />
                <span>Usulan Karir KP, KGB & Satya</span>
            </x-dropdown-link>
            <div class="border-t border-slate-100 my-1"></div>
        @else
            {{-- LAYANAN E-SURAT KETERANGAN UNTUK PEGAWAI --}}
            <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                Layanan Surat Mandiri
            </div>
            <x-dropdown-link :href="route('layanan-surat.index')" class="flex items-center gap-2 {{ request()->routeIs('layanan-surat.*') ? 'bg-purple-50 text-purple-700 font-semibold' : '' }}">
                <x-icon name="file-text" class="w-4 h-4 text-blue-600 shrink-0" />
                <span>E-Surat Keterangan Aktif</span>
            </x-dropdown-link>
            <div class="border-t border-slate-100 my-1"></div>
        @endif

        {{-- LAYANAN E-CUTI --}}
        <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
            Layanan Cuti ASN (PerBKN 7/2022)
        </div>
        @if(Auth::user()->isAtasan() || Auth::user()->hasRole('admin'))
            <x-dropdown-link :href="route('pengajuan-cuti.index')" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-cuti.index') && !request()->has('mine') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                <x-icon name="clipboard-check" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>{{ Auth::user()->hasRole('admin') ? 'Monitoring Seluruh Cuti' : 'Persetujuan Cuti Bawahan' }}</span>
            </x-dropdown-link>
        @endif
        <x-dropdown-link :href="route('pengajuan-cuti.index', (Auth::user()->isAtasan() || Auth::user()->hasRole('admin')) ? ['mine' => 1] : [])" class="flex items-center gap-2 {{ (request()->routeIs('pengajuan-cuti.index') && (request()->has('mine') || (!Auth::user()->isAtasan() && !Auth::user()->hasRole('admin')))) ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
            <x-icon name="calendar-off" class="w-4 h-4 text-emerald-600 shrink-0" />
            <span>Permohonan Cuti Saya</span>
        </x-dropdown-link>
        <x-dropdown-link :href="route('pengajuan-cuti.create')" class="flex items-center gap-2 {{ request()->routeIs('pengajuan-cuti.create') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
            <x-icon name="plus" class="w-4 h-4 text-blue-600 shrink-0" />
            <span>+ Buat Permohonan Cuti Baru</span>
        </x-dropdown-link>

        {{-- REGULASI & TATA NASKAH --}}
        <div class="border-t border-slate-100 my-1"></div>
        <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
            Pedoman & Dasar Hukum
        </div>
        <x-dropdown-link :href="route('regulasi-tata-naskah.index')" class="flex items-center gap-2 {{ request()->routeIs('regulasi-tata-naskah.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
            <x-icon name="scale" class="w-4 h-4 text-indigo-600 shrink-0" />
            <span>Regulasi & SOP Paraf Digital</span>
        </x-dropdown-link>
    </x-slot>
</x-dropdown>

{{-- ============================================================== --}}
{{-- 8. PERENCANAAN & TALENTA ASN (Anjab, ABK & Sistem Merit 9-Box)  --}}
{{-- ============================================================== --}}
@if(Auth::user()->canAccessAnjabAbk() || Auth::user()->canAccessTalentManagement())
    <x-dropdown align="right" width="w-72">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('anjab.*', 'abk.*', 'manajemen-talenta.*') ? 'bg-rose-50 text-rose-700 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="target" class="w-4 h-4 {{ request()->routeIs('anjab.*', 'abk.*', 'manajemen-talenta.*') ? 'text-rose-600' : 'text-slate-500' }}" />
                <span>Perencanaan & Talenta</span>
                <x-icon name="chevron-down" class="w-3.5 h-3.5 text-slate-400" />
            </button>
        </x-slot>
        <x-slot name="content">
            {{-- ANJAB & ABK --}}
            @if(Auth::user()->canAccessAnjabAbk())
                <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                    Analisis Jabatan & ABK
                </div>
                <x-dropdown-link :href="route('anjab.peta-jabatan')" class="flex items-center gap-2 {{ request()->routeIs('anjab.peta-jabatan*') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                    <x-icon name="git-branch" class="w-4 h-4 text-teal-600 shrink-0" />
                    <span>Peta Jabatan Digital</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('abk.index')" class="flex items-center gap-2 {{ request()->routeIs('abk.index', 'abk.edit') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                    <x-icon name="chart" class="w-4 h-4 text-blue-600 shrink-0" />
                    <span>Analisis Beban Kerja (ABK)</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('abk.simulasi-formasi')" class="flex items-center gap-2 {{ request()->routeIs('abk.simulasi-formasi') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                    <x-icon name="target" class="w-4 h-4 text-purple-600 shrink-0" />
                    <span>Simulasi Formasi CASN</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('anjab.index')" class="flex items-center gap-2 {{ request()->routeIs('anjab.index', 'anjab.show', 'anjab.edit', 'anjab.evaluasi*') ? 'bg-teal-50 text-teal-700 font-semibold' : '' }}">
                    <x-icon name="file-text" class="w-4 h-4 text-teal-600 shrink-0" />
                    <span>Katalog Dokumen Anjab</span>
                </x-dropdown-link>
                @if(Auth::user()->canManageAnjabAbk())
                    <x-dropdown-link :href="route('anjab.create')" class="flex items-center gap-2">
                        <x-icon name="plus" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span>+ Tambah Dokumen Anjab Baru</span>
                    </x-dropdown-link>
                @endif
                <div class="border-t border-slate-100 my-1"></div>
            @endif

            {{-- SISTEM MERIT & TALENTA ASN --}}
            @if(Auth::user()->canAccessTalentManagement())
                <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/80 border-b border-slate-100">
                    Sistem Merit & Talenta (PermenPAN 3/2020)
                </div>
                <x-dropdown-link :href="route('manajemen-talenta.index')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.index') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                    <x-icon name="target" class="w-4 h-4 text-rose-600 shrink-0" />
                    <span>Matriks 9-Kotak ASN</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('manajemen-talenta.rekap')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.rekap') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                    <x-icon name="clipboard-list" class="w-4 h-4 text-emerald-600 shrink-0" />
                    <span>Rekap Data & Pemetaan</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('manajemen-talenta.suksesi.index')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.suksesi.*') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                    <x-icon name="crown" class="w-4 h-4 text-amber-600 shrink-0" />
                    <span>Rencana Suksesi Jabatan</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('manajemen-talenta.idp')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.idp*', 'manajemen-talenta.pegawai.idp*') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                    <x-icon name="book-open" class="w-4 h-4 text-teal-600 shrink-0" />
                    <span>Pengembangan 20 JP (IDP)</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('manajemen-talenta.sidang-komite.index')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.sidang-komite*') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                    <x-icon name="scale" class="w-4 h-4 text-indigo-600 shrink-0" />
                    <span>Sidang Komite Talenta</span>
                </x-dropdown-link>
                <x-dropdown-link :href="route('manajemen-talenta.asesmen.index')" class="flex items-center gap-2 {{ request()->routeIs('manajemen-talenta.asesmen.*') ? 'bg-rose-50 text-rose-700 font-semibold' : '' }}">
                    <x-icon name="award" class="w-4 h-4 text-indigo-600 shrink-0" />
                    <span>Uji Kompetensi & Asesmen</span>
                </x-dropdown-link>
            @endif
        </x-slot>
    </x-dropdown>
@endif

{{-- ============================================================== --}}
{{-- 9. MASTER DATA & SISTEM (Khusus Administrator Sistem)         --}}
{{-- ============================================================== --}}
@if(Auth::user()->hasRole('admin'))
    <x-dropdown align="right" width="w-60">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-1.5 px-2.5 sm:px-3 py-1.5 rounded-lg text-xs md:text-[13px] font-semibold transition whitespace-nowrap {{ request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*', 'jenis-jabatan.*', 'audit-logs.*', 'backup.*') ? 'bg-slate-800 text-white font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                <x-icon name="database" class="w-4 h-4 {{ request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*', 'jenis-jabatan.*', 'audit-logs.*', 'backup.*') ? 'text-white' : 'text-slate-500' }}" />
                <span>Master & Sistem</span>
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
                <span>Master Jabatan</span>
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
                Keamanan & Pemeliharaan
            </div>
            <x-dropdown-link :href="route('audit-logs.index')" class="flex items-center gap-2 {{ request()->routeIs('audit-logs.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="shield-check" class="w-4 h-4 text-violet-600 shrink-0" />
                <span>Audit Log Sistem</span>
            </x-dropdown-link>
            <x-dropdown-link :href="route('backup.index')" class="flex items-center gap-2 {{ request()->routeIs('backup.*') ? 'bg-slate-100 text-slate-900 font-semibold' : '' }}">
                <x-icon name="database" class="w-4 h-4 text-rose-600 shrink-0" />
                <span>Backup & Restore DB</span>
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif
