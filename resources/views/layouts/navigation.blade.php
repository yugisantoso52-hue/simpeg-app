<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 shadow-xs">
    <!-- ========================================== -->
    <!-- 1. KOP HEADER INSTANSI & PROFIL PENGGUNA   -->
    <!-- ========================================== -->
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-3 md:pt-4 pb-2 md:pb-3 overflow-visible">
        
        <!-- Profile User Desktop (Pojok Kanan Atas - Memanfaatkan Ruang Kosong Header) -->
        <div class="hidden sm:flex items-center absolute top-3 md:top-4 right-4 sm:right-6 lg:right-8 z-30">
            <x-dropdown align="right" width="56">
                <x-slot name="trigger">
                    <button class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-semibold rounded-full text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none transition shadow-2xs cursor-pointer gap-2">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="max-w-[140px] truncate">{{ Auth::user()->name }}</span>
                        <svg class="h-3.5 w-3.5 fill-current text-slate-400" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="px-4 py-2 border-b border-gray-100 bg-gray-50">
                        <div class="text-xs font-bold text-gray-800 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-[10px] text-gray-500 font-mono truncate">{{ Auth::user()->pegawai?->nip ?? str_replace('@staff.unri.ac.id', '', Auth::user()->email) }}</div>
                    </div>

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

        <!-- Logo & Teks Kop Surat Instansi (Centered) -->
        <div class="flex flex-col md:flex-row items-center justify-center gap-2.5 md:gap-4 text-center">
            <!-- Logo UNRI -->
            <a href="{{ route('dashboard') }}" class="shrink-0 transition transform hover:scale-105 duration-200">
                <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-[48px] md:h-[68px] w-auto object-contain">
            </a>

            <!-- Teks Kop Surat -->
            <div class="leading-tight max-w-full px-1 notranslate" translate="no">
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
    <!-- 2. MENU NAVIGASI UTAMA (TABS TERPUSAT)     -->
    <!-- ========================================== -->
    <div class="max-w-7xl mx-auto px-2 sm:px-4 lg:px-8 notranslate" translate="no">
        <div class="flex items-center justify-center min-h-[52px]">

            <!-- Desktop & Laptop Navigation Links (Selalu Terbuka & Terpusat Penuh) -->
            <nav class="hidden sm:flex flex-wrap items-center justify-center gap-x-4 md:gap-x-6 lg:gap-x-8 gap-y-1.5 py-2 notranslate" translate="no">
                @include('layouts.partials.nav-desktop')
            </nav>

            <!-- Mobile Bar (Hanya Muncul di Smartphone < 640px) -->
            <div class="flex items-center justify-between w-full sm:hidden py-2 px-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#007a3d]"></span>
                    SIKAP UNRI
                </span>
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out" aria-label="Buka Menu">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Khusus Smartphone < 640px) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-gray-200 bg-gray-50">
        @include('layouts.partials.nav-mobile')

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-200 bg-white">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 font-mono">{{ Auth::user()->pegawai?->nip ?? str_replace('@staff.unri.ac.id', '', Auth::user()->email) }}</div>
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