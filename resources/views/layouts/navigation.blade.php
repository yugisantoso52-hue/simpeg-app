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
                @include('layouts.partials.nav-desktop')
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
        @include('layouts.partials.nav-mobile')

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