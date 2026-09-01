<div class="pt-2 pb-3 space-y-1">
    {{-- Dashboard --}}
    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        Dashboard
    </x-responsive-nav-link>

    {{-- Profil Saya (Pegawai) --}}
    @if(Auth::user()->hasRole('pegawai'))
        <x-responsive-nav-link :href="route('pegawai.my-profile')" :active="request()->routeIs('pegawai.my-profile', 'pegawai.show')">
            👤 Profil Saya
        </x-responsive-nav-link>
    @endif

    {{-- E-Cuti --}}
    <x-responsive-nav-link :href="route('pengajuan-cuti.index')" :active="request()->routeIs('pengajuan-cuti.*')">
        🏖️ E-Cuti Pegawai
    </x-responsive-nav-link>

    {{-- Group Data Kepegawaian (Admin & Pimpinan) --}}
    @if(Auth::user()->hasRole(['admin', 'pimpinan']))
        <div class="px-4 pt-3 pb-1 text-xs font-bold text-gray-500 uppercase tracking-wider">
            Data Kepegawaian
        </div>
        <x-responsive-nav-link :href="route('kepegawaian.dosen.index')" :active="request()->routeIs('kepegawaian.dosen.*')">
            👨‍🏫 Data Dosen
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('kepegawaian.tendik.index')" :active="request()->routeIs('kepegawaian.tendik.*')">
            🧑‍💼 Data Tendik
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('kepegawaian.phl.index')" :active="request()->routeIs('kepegawaian.phl.*')">
            👷 Data PHL / Honorer
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('pegawai.index')" :active="request()->routeIs('pegawai.index')">
            📋 Semua Pegawai (Master)
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('duk.index')" :active="request()->routeIs('duk.*')">
            📊 Daftar Urut Kepangkatan (DUK)
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('kp.index')" :active="request()->routeIs('kp.*')">
            🎖️ Kenaikan Pangkat (KP)
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('kgb.index')" :active="request()->routeIs('kgb.*')">
            💵 Kenaikan Gaji Berkala (KGB)
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('satyalancana.index')" :active="request()->routeIs('satyalancana.*')">
            🏅 Satyalancana
        </x-responsive-nav-link>
        @if(Auth::user()->hasRole('admin'))
            <x-responsive-nav-link :href="route('mutasi-pegawai.index')" :active="request()->routeIs('mutasi-pegawai.*')">
                🔄 Mutasi Pegawai
            </x-responsive-nav-link>
        @endif
        <x-responsive-nav-link :href="route('tugas-belajar.index')" :active="request()->routeIs('tugas-belajar.*')">
            🎓 Tugas Belajar
        </x-responsive-nav-link>
    @endif

    {{-- Group Riwayat Pegawai --}}
    @if(Auth::user()->hasRole(['admin', 'pimpinan']))
        <div class="px-4 pt-3 pb-1 text-xs font-bold text-gray-500 uppercase tracking-wider">
            Riwayat Pegawai
        </div>
        <x-responsive-nav-link :href="route('riwayat-pendidikan.index')" :active="request()->routeIs('riwayat-pendidikan.*')">
            🎓 Riwayat Pendidikan
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-jabatan.index')" :active="request()->routeIs('riwayat-jabatan.*')">
            💼 Riwayat Jabatan
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-pangkat.index')" :active="request()->routeIs('riwayat-pangkat.*')">
            🎖️ Riwayat Pangkat
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-diklat.index')" :active="request()->routeIs('riwayat-diklat.*')">
            📜 Riwayat Diklat
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-str-sip.index')" :active="request()->routeIs('riwayat-str-sip.*')">
            🩺 Riwayat STR & SIP
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-skp.index')" :active="request()->routeIs('riwayat-skp.*')">
            📊 Riwayat SKP
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-penghargaan.index')" :active="request()->routeIs('riwayat-penghargaan.*')">
            🏅 Riwayat Penghargaan
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-organisasi.index')" :active="request()->routeIs('riwayat-organisasi.*')">
            🏛️ Riwayat Organisasi
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('riwayat-publikasi.index')" :active="request()->routeIs('riwayat-publikasi.*')">
            📚 Riwayat Publikasi
        </x-responsive-nav-link>
    @endif

    {{-- Group Master Data --}}
    @if(Auth::user()->hasRole('admin'))
        <div class="px-4 pt-3 pb-1 text-xs font-bold text-gray-500 uppercase tracking-wider">
            Master Data
        </div>
        <x-responsive-nav-link :href="route('unit-kerja.index')" :active="request()->routeIs('unit-kerja.*')">
            🏢 Unit Kerja
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('jabatan.index')" :active="request()->routeIs('jabatan.*')">
            👔 Jabatan
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('golongan.index')" :active="request()->routeIs('golongan.*')">
            📊 Golongan
        </x-responsive-nav-link>
        <x-responsive-nav-link :href="route('backup.index')" :active="request()->routeIs('backup.*')">
            💾 Backup & Restore DB
        </x-responsive-nav-link>
    @endif
</div>
