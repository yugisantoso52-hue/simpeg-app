<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-sm">
    <!-- ========================================== -->
    <!-- 1. KOP HEADER INSTANSI (FULLY RESPONSIVE)  -->
    <!-- ========================================== -->
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-3 md:pt-4 pb-2 md:pb-3 overflow-x-hidden">
        <div class="flex flex-col md:flex-row items-center justify-center gap-2.5 md:gap-4 text-center">
            <!-- Logo UNRI -->
            <a href="{{ route('dashboard') }}" class="shrink-0 transition transform hover:scale-105 duration-200">
                <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-[48px] md:h-[68px] w-auto object-contain">
            </a>

            <!-- Teks Kop Surat -->
            <div class="leading-tight max-w-full px-1">
                <h2 class="text-[10px] md:text-[11px] font-semibold tracking-wider text-slate-600 uppercase">
                    KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI
                </h2>
                <h3 class="text-[12px] md:text-[14px] font-bold tracking-wide text-slate-900 uppercase my-0.5">
                    UNIVERSITAS RIAU
                </h3>
                <h4 class="text-[13px] md:text-[15px] font-extrabold tracking-wide uppercase text-[#007a3d]">
                    FAKULTAS KEPERAWATAN
                </h4>
                <h1 class="text-[11.5px] md:text-[13px] font-bold tracking-wide uppercase text-slate-800 mt-0.5">
                    SISTEM INFORMASI KEPEGAWAIAN (SIKAP)
                </h1>
                <p class="text-[9.5px] md:text-[10.5px] leading-tight text-slate-500 mt-1 break-words">
                    Kampus Bina Widya Gedung Health Studies Complex Km.12,5 Simpang Baru 28293
                </p>
                <p class="text-[9.5px] md:text-[10.5px] leading-tight text-slate-500 break-words">
                    Laman: <a href="http://keperawatan.unri.ac.id" target="_blank" class="text-blue-600 hover:underline">http://keperawatan.unri.ac.id</a> | Email: <a href="mailto:keperawatan@unri.ac.id" class="text-blue-600 hover:underline">keperawatan@unri.ac.id</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Garis Pemisah Khas Kop Surat Instansi -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="border-b-2 border-gray-800 my-1"></div>
    </div>

    <!-- ========================================== -->
    <!-- 2. MENU NAVIGASI UTAMA (CENTERED MENU)     -->
    <!-- ========================================== -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="relative flex items-center justify-center h-14">

            <!-- Navigation Links (Center Aligned Desktop) -->
            <div class="hidden sm:flex sm:items-center sm:space-x-8">

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

                {{-- 3. Dropdown Data Pegawai (Khusus Admin & Pimpinan) --}}
                @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('pegawai.*', 'duk.*', 'mutasi-pegawai.*') ? 'border-blue-600 text-gray-900 font-semibold' : '' }}">
                                <span>Data Pegawai</span>
                                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('pegawai.index')">
                                📄 Data Utama Pegawai
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('duk.index')">
                                📊 Daftar Urut Kepangkatan (DUK)
                            </x-dropdown-link>
                            @if(Auth::user()->hasRole('admin'))
                                <x-dropdown-link :href="route('mutasi-pegawai.index')">
                                    🔄 Mutasi Pegawai
                                </x-dropdown-link>
                            @endif
                        </x-slot>
                    </x-dropdown>
                @endif

                {{-- 4. Dropdown Riwayat Pegawai (Khusus Admin & Pimpinan) --}}
                @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('riwayat-*') ? 'border-blue-600 text-gray-900 font-semibold' : '' }}">
                                <span>Riwayat Pegawai</span>
                                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('riwayat-pendidikan.index')">
                                🎓 Riwayat Pendidikan
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('riwayat-jabatan.index')">
                                💼 Riwayat Jabatan
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('riwayat-pangkat.index')">
                                🎖️ Riwayat Pangkat
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('riwayat-diklat.index')">
                                📜 Riwayat Diklat
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                @endif

                {{-- 5. Dropdown Master Data (Khusus Admin) --}}
                @if(Auth::user()->hasRole('admin'))
                    <x-dropdown align="left" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none transition ease-in-out duration-150 {{ request()->routeIs('unit-kerja.*', 'jabatan.*', 'golongan.*') ? 'border-blue-600 text-gray-900 font-semibold' : '' }}">
                                <span>Master Data</span>
                                <svg class="ms-1.5 h-4 w-4 fill-current text-gray-400" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('unit-kerja.index')">
                                🏢 Unit Kerja
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('jabatan.index')">
                                👔 Jabatan
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('golongan.index')">
                                📊 Golongan
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                @endif

            </div>

            <!-- Profile User (Absolut di Pojok Kanan) -->
            <div class="hidden sm:flex sm:items-center absolute right-0">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-gray-200 text-sm leading-4 font-medium rounded-lg text-gray-700 bg-gray-50 hover:bg-gray-100 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <span>{{ Auth::user()->name }}</span>
                            </div>

                            <div class="ms-1.5">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            ⚙️ {{ __('Profile & Pengaturan') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                🚪 {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger Button (Mobile View - Pojok Kanan) -->
            <div class="flex items-center sm:hidden absolute right-0">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile View) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-200 bg-gray-50">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                Dashboard
            </x-responsive-nav-link>

            {{-- Group Profil Saya (Pegawai Biasa) --}}
            @if(Auth::user()->hasRole('pegawai'))
                <x-responsive-nav-link :href="route('pegawai.my-profile')" :active="request()->routeIs('pegawai.my-profile', 'pegawai.show')">
                    👤 Profil Saya
                </x-responsive-nav-link>
            @endif

            {{-- Group Data Pegawai (Admin & Pimpinan) --}}
            @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                <div class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Data Pegawai</div>
                <x-responsive-nav-link :href="route('pegawai.index')" :active="request()->routeIs('pegawai.*')">
                    📄 Data Utama Pegawai
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('duk.index')" :active="request()->routeIs('duk.*')">
                    📊 Daftar Urut Kepangkatan (DUK)
                </x-responsive-nav-link>
                @if(Auth::user()->hasRole('admin'))
                    <x-responsive-nav-link :href="route('mutasi-pegawai.index')" :active="request()->routeIs('mutasi-pegawai.*')">
                        🔄 Mutasi Pegawai
                    </x-responsive-nav-link>
                @endif
            @endif

            {{-- Group Riwayat (Admin & Pimpinan) --}}
            @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                <div class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Riwayat Pegawai</div>
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
            @endif

            {{-- Group Master Data (Khusus Admin) --}}
            @if(Auth::user()->hasRole('admin'))
                <div class="px-4 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Master Data</div>
                <x-responsive-nav-link :href="route('unit-kerja.index')" :active="request()->routeIs('unit-kerja.*')">
                    🏢 Unit Kerja
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('jabatan.index')" :active="request()->routeIs('jabatan.*')">
                    👔 Jabatan
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('golongan.index')" :active="request()->routeIs('golongan.*')">
                    📊 Golongan
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-200 bg-white">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    ⚙️ {{ __('Profile & Pengaturan') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        🚪 {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>