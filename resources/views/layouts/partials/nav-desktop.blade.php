{{-- 1. Dashboard (Akses Semua Role) --}}
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
    Dashboard
</x-nav-link>

{{-- 2. Menu Profil Saya (Khusus Role Pegawai Biasa) --}}
@if(Auth::user()->hasRole('pegawai'))
    <x-nav-link :href="route('pegawai.my-profile')" :active="request()->routeIs('pegawai.my-profile', 'pegawai.show')">
        👤 Profil Saya
    </x-nav-link>
@endif

{{-- 3. E-Cuti Pegawai (Semua Role) --}}
<x-nav-link :href="route('pengajuan-cuti.index')" :active="request()->routeIs('pengajuan-cuti.*')">
    🏖️ E-Cuti
</x-nav-link>

{{-- 4. Dropdown Data Kepegawaian (Khusus Admin & Pimpinan) --}}
@if(Auth::user()->hasRole(['admin', 'pimpinan']))
    <x-dropdown align="left" width="60">
        <x-slot name="trigger">
            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('kepegawaian.*', 'pegawai.*', 'duk.*', 'mutasi-pegawai.*', 'tugas-belajar.*') ? 'border-blue-600 text-blue-700 font-bold' : '' }}">
                <span>Data Kepegawaian</span>
                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>
        <x-slot name="content">
            <div class="px-4 py-1.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                Kategori Pegawai
            </div>
            <x-dropdown-link :href="route('kepegawaian.dosen.index')" class="{{ request()->routeIs('kepegawaian.dosen.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                👨‍🏫 Data Dosen
            </x-dropdown-link>
            <x-dropdown-link :href="route('kepegawaian.tendik.index')" class="{{ request()->routeIs('kepegawaian.tendik.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                🧑‍💼 Data Tendik
            </x-dropdown-link>
            <x-dropdown-link :href="route('kepegawaian.phl.index')" class="{{ request()->routeIs('kepegawaian.phl.*') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                👷 Data PHL / Honorer
            </x-dropdown-link>

            <div class="border-t border-gray-100 my-1"></div>
            <div class="px-4 py-1.5 text-[10px] font-extrabold text-gray-400 uppercase tracking-wider bg-gray-50 border-b border-gray-100">
                Master & Karir
            </div>
            <x-dropdown-link :href="route('pegawai.index')" class="{{ request()->routeIs('pegawai.index', 'pegawai.show', 'pegawai.edit', 'pegawai.create') ? 'bg-gray-100 font-semibold text-gray-900' : '' }}">
                📋 Semua Pegawai (Master)
            </x-dropdown-link>
            <x-dropdown-link :href="route('duk.index')" class="{{ request()->routeIs('duk.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📊 Daftar Urut Kepangkatan (DUK)
            </x-dropdown-link>
            <x-dropdown-link :href="route('kp.index')" class="{{ request()->routeIs('kp.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : '' }}">
                🎖️ Kenaikan Pangkat (KP)
            </x-dropdown-link>
            <x-dropdown-link :href="route('kgb.index')" class="{{ request()->routeIs('kgb.*') ? 'bg-amber-50 text-amber-700 font-semibold' : '' }}">
                💵 Kenaikan Gaji Berkala (KGB)
            </x-dropdown-link>
            <x-dropdown-link :href="route('satyalancana.index')" class="{{ request()->routeIs('satyalancana.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : '' }}">
                🏅 Satyalancana
            </x-dropdown-link>
            @if(Auth::user()->hasRole('admin'))
                <x-dropdown-link :href="route('mutasi-pegawai.index')" class="{{ request()->routeIs('mutasi-pegawai.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                    🔄 Mutasi Pegawai
                </x-dropdown-link>
            @endif
            <x-dropdown-link :href="route('tugas-belajar.index')" class="{{ request()->routeIs('tugas-belajar.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                🎓 Tugas Belajar
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif

{{-- 5. Dropdown Riwayat Pegawai (Khusus Admin & Pimpinan) --}}
@if(Auth::user()->hasRole(['admin', 'pimpinan']))
    <x-dropdown align="left" width="52">
        <x-slot name="trigger">
            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('riwayat-*') ? 'border-blue-600 text-blue-700 font-bold' : '' }}">
                <span>Riwayat Pegawai</span>
                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>
        <x-slot name="content">
            <x-dropdown-link :href="route('riwayat-pendidikan.index')" class="{{ request()->routeIs('riwayat-pendidikan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                🎓 Riwayat Pendidikan
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-jabatan.index')" class="{{ request()->routeIs('riwayat-jabatan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                💼 Riwayat Jabatan
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-pangkat.index')" class="{{ request()->routeIs('riwayat-pangkat.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                🎖️ Riwayat Pangkat
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-diklat.index')" class="{{ request()->routeIs('riwayat-diklat.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📜 Riwayat Diklat
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-str-sip.index')" class="{{ request()->routeIs('riwayat-str-sip.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                🩺 Riwayat STR & SIP
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-skp.index')" class="{{ request()->routeIs('riwayat-skp.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📊 Riwayat SKP
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-penghargaan.index')" class="{{ request()->routeIs('riwayat-penghargaan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                🏅 Riwayat Penghargaan
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-organisasi.index')" class="{{ request()->routeIs('riwayat-organisasi.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                🏛️ Riwayat Organisasi
            </x-dropdown-link>
            <x-dropdown-link :href="route('riwayat-publikasi.index')" class="{{ request()->routeIs('riwayat-publikasi.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📚 Riwayat Publikasi
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif

{{-- 6. Dropdown Master Data (Khusus Admin) --}}
@if(Auth::user()->hasRole('admin'))
    <x-dropdown align="left" width="48">
        <x-slot name="trigger">
            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*') ? 'border-blue-600 text-blue-700 font-bold' : '' }}">
                <span>Master Data</span>
                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </x-slot>
        <x-slot name="content">
            <x-dropdown-link :href="route('unit-kerja.index')" class="{{ request()->routeIs('unit-kerja.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                🏢 Unit Kerja
            </x-dropdown-link>
            <x-dropdown-link :href="route('jabatan.index')" class="{{ request()->routeIs('jabatan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                👔 Jabatan
            </x-dropdown-link>
            <x-dropdown-link :href="route('golongan.index')" class="{{ request()->routeIs('golongan.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                📊 Golongan
            </x-dropdown-link>

            <div class="border-t border-gray-100 my-1"></div>
            <x-dropdown-link :href="route('backup.index')" class="{{ request()->routeIs('backup.*') ? 'bg-blue-50 text-blue-700 font-semibold' : '' }}">
                💾 Backup & Restore DB
            </x-dropdown-link>
            <x-dropdown-link :href="route('cloud-sync.pull-web')" onclick="return confirm('Apakah Anda yakin ingin menarik seluruh data terbaru dari Cloud Railway (https://sikap-app.up.railway.app) ke localhost?')" class="text-blue-600 font-bold hover:bg-blue-50">
                🔄 Tarik Data dari Cloud
            </x-dropdown-link>
        </x-slot>
    </x-dropdown>
@endif
