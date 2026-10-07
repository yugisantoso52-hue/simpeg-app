<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs transition-all">
    <!-- ========================================== -->
    <!-- 1. KOP HEADER INSTANSI & PROFIL PENGGUNA   -->
    <!-- ========================================== -->

    <!-- A. HEADER KHUSUS MOBILE (< 768px / Android & Smartphone) -->
    <div class="block md:hidden px-3 py-2 border-b border-slate-100 bg-white">
        <div class="flex items-center justify-between gap-2">
            <!-- Sisi Kiri: Logo UNRI + Identitas Lembaga Proporsional Rapi -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0 flex-1" title="SIKAP Fakultas Keperawatan UNRI">
                <img src="{{ asset('logo-unri.png') }}" 
                     alt="Logo Universitas Riau" 
                     class="h-10 w-auto object-contain shrink-0 drop-shadow-2xs">
                <div class="leading-tight min-w-0">
                    <div class="text-[9.5px] font-semibold text-slate-500 uppercase tracking-wider truncate">
                        Universitas Riau
                    </div>
                    <div class="text-[12px] font-black text-[#007a3d] uppercase tracking-wide truncate">
                        Fakultas Keperawatan
                    </div>
                    <div class="text-[9.5px] font-bold text-slate-700 tracking-wider flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                        <span>SIKAP</span>
                        <span class="text-slate-400 font-normal">| Kepegawaian</span>
                    </div>
                </div>
            </a>

            <!-- Sisi Kanan: Logo Kemendiktisaintek + Tombol Menu Hamburger -->
            <div class="flex items-center gap-1.5 shrink-0">
                <img src="{{ asset('logo-kemendiktisaintek.png') }}" 
                     alt="Kemendiktisaintek" 
                     class="h-8 w-auto object-contain hidden xs:block drop-shadow-2xs">
                
                <button @click="open = ! open" 
                        class="inline-flex items-center justify-center p-2 rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 active:bg-slate-300 focus:outline-none transition active:scale-95 shadow-2xs cursor-pointer" 
                        aria-label="Buka Menu Navigasi">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- B. HEADER KOP SURAT LENGKAP UNTUK TABLET & DESKTOP (>= 768px) -->
    <div class="hidden md:block relative w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 pt-2.5 sm:pt-3 pb-2 overflow-visible">
        <!-- Profile User Dropdown Desktop / Hamburger Tablet -->
        <div class="absolute right-4 sm:right-6 lg:right-8 xl:right-10 top-3 sm:top-4 z-20 flex items-center gap-2">
            <!-- Profile Desktop (>= 1280px / xl) -->
            <div class="hidden xl:flex items-center">
                <x-dropdown align="right" width="60">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-semibold rounded-full text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none transition shadow-2xs cursor-pointer gap-2">
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            <span class="max-w-[120px] lg:max-w-[140px] truncate font-medium">{{ Auth::user()->name }}</span>
                            <x-icon name="chevron-down" class="h-3.5 w-3.5 text-slate-400" />
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2.5 border-b border-gray-100 bg-gray-50/80">
                            <div class="flex items-center justify-between gap-2">
                                <div class="text-xs font-bold text-gray-800 truncate">{{ Auth::user()->name }}</div>
                                @if(Auth::user()->hasRole('admin'))
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-100 text-red-800 border border-red-200">Admin</span>
                                @elseif(Auth::user()->isPimpinan())
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">Pimpinan</span>
                                @elseif(Auth::user()->isAtasan())
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-800 border border-blue-200">Atasan</span>
                                @else
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">Pegawai</span>
                                @endif
                            </div>
                            @if(Auth::user()->pegawai?->jabatan?->nama_jabatan)
                                <div class="text-[10px] font-medium text-slate-600 truncate mt-0.5">{{ Auth::user()->pegawai->jabatan->nama_jabatan }}</div>
                            @endif
                            <div class="text-[10px] text-gray-400 font-mono truncate mt-0.5">NIP: {{ Auth::user()->pegawai?->nip ?? '-' }}</div>
                        </div>

                        <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                            <x-icon name="settings" class="w-4 h-4 text-slate-500" />
                            <span>{{ __('Profile & Pengaturan') }}</span>
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();"
                                    class="flex items-center gap-2 text-rose-600 hover:text-rose-700">
                                <x-icon name="log-out" class="w-4 h-4 text-rose-500" />
                                <span>{{ __('Log Out') }}</span>
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger Button untuk Layar Tablet / Setengah Layar PC (< 1280px / < xl) -->
            <div class="flex xl:hidden items-center">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 focus:outline-none transition active:scale-95 shadow-2xs" aria-label="Buka Menu Navigasi">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- STRUKTUR KOP UTAMA LENGKAP -->
        <div class="w-full max-w-4xl mx-auto flex items-center justify-center gap-4 sm:gap-6 md:gap-7 lg:gap-8 px-2 sm:px-4">
            
            <!-- Sisi Kiri: Logo Universitas Riau -->
            <div class="shrink-0 flex items-center justify-center">
                <a href="{{ route('dashboard') }}" class="block transition transform hover:scale-105 duration-200" title="Universitas Riau">
                    <img src="{{ asset('logo-unri.png') }}" 
                         alt="Logo Universitas Riau" 
                         class="h-[68px] sm:h-[76px] md:h-[84px] lg:h-[88px] w-auto object-contain drop-shadow-2xs">
                </a>
            </div>

            <!-- Bagian Tengah: Teks Kop Surat Resmi Institusi -->
            <div class="flex-1 text-center leading-tight px-1 sm:px-2 notranslate" translate="no">
                <h2 class="text-[10.5px] sm:text-[11.5px] md:text-[12.5px] font-semibold tracking-wider text-slate-600 uppercase">
                    KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI
                </h2>
                <h3 class="text-[13px] sm:text-[14px] md:text-[15.5px] font-bold tracking-wide text-slate-900 uppercase my-0.5">
                    UNIVERSITAS RIAU
                </h3>
                <h4 class="text-[14px] sm:text-[15.5px] md:text-[16.5px] font-black tracking-wide uppercase text-[#007a3d]">
                    FAKULTAS KEPERAWATAN
                </h4>
                <h1 class="text-[12px] sm:text-[13px] md:text-[14px] font-bold tracking-wider uppercase text-slate-800 mt-0.5">
                    SISTEM INFORMASI KEPEGAWAIAN (SIKAP)
                </h1>
                <p class="text-[9.5px] sm:text-[10px] leading-tight text-slate-500 mt-1">
                    Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru 28293
                </p>
                <p class="text-[9.5px] sm:text-[10px] leading-tight text-slate-500">
                    Laman : <a href="http://www.keperawatan.unri.ac.id" target="_blank" class="text-blue-600 hover:underline">www.keperawatan.unri.ac.id</a> &nbsp;|&nbsp; Email : <a href="mailto:keperawatan@unri.co.id" class="text-blue-600 hover:underline">keperawatan@unri.co.id</a>
                </p>
            </div>

            <!-- Sisi Kanan: Logo Kemendiktisaintek -->
            <div class="shrink-0 flex items-center justify-center">
                <a href="{{ route('dashboard') }}" class="block transition transform hover:scale-105 duration-200" title="Kementerian Pendidikan Tinggi, Sains, dan Teknologi">
                    <img src="{{ asset('logo-kemendiktisaintek.png') }}" 
                         alt="Logo Kementerian Pendidikan Tinggi, Sains, dan Teknologi" 
                         class="h-[68px] sm:h-[76px] md:h-[84px] lg:h-[88px] w-auto object-contain drop-shadow-2xs">
                </a>
            </div>

        </div>
    </div>

    <!-- Garis Pemisah Khas Kop Surat Resmi Instansi (Garis Ganda Formal) -->
    <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <div class="border-b-2 border-slate-900"></div>
        <div class="border-b border-slate-400 mt-[1.5px] mb-1"></div>
    </div>

    <!-- ============================================================== -->
    <!-- 2. MENU NAVIGASI UTAMA                                         -->
    <!-- Desktop (>= 1280px): 1 Baris Penuh, overflow-visible, TANPA SCROLLBAR -->
    <!-- Tablet / Setengah Layar PC (< 1280px): Sub-bar Ringkas + Drawer Menu -->
    <!-- ============================================================== -->
    <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4 lg:px-6 xl:px-8 notranslate" translate="no">
        <div class="flex items-center justify-center min-h-[44px]">

            <!-- Desktop Navigation Links (Layar Lebar >= 1280px / xl, overflow-visible agar dropdown tidak pernah terpotong) -->
            <nav class="hidden xl:flex items-center justify-center flex-nowrap overflow-visible gap-x-1 sm:gap-x-1.5 md:gap-x-2 xl:gap-x-2.5 py-1.5 notranslate" translate="no">
                @include('layouts.partials.nav-desktop')
            </nav>

            <!-- Sub-bar Status & Menu Toggler (< 1280px / Layar Setengah PC & Mobile) -->
            <div class="flex items-center justify-between w-full xl:hidden py-1 px-2 sm:px-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#007a3d]"></span>
                    SIKAP UNRI
                </span>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-600 font-medium truncate max-w-[120px] sm:max-w-[200px]">
                        {{ Auth::user()->name }}
                    </span>
                    <button @click="open = ! open" 
                            type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition active:scale-95 shadow-2xs cursor-pointer">
                        <svg class="h-4 w-4 text-[#007a3d]" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span x-text="open ? 'Tutup Menu' : 'Menu Navigasi'">Menu Navigasi</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- 3. RESPONSIVE MENU DRAWER (Mobile Android & PC Setengah Layar) -->
    <!-- Dilengkapi max-h calc dan pb-32 agar Log Out SELALU BISA DI-SCROLL  -->
    <!-- ============================================================== -->
    <div :class="{'block': open, 'hidden': ! open}" 
         class="hidden xl:hidden border-t border-slate-200 bg-white max-h-[calc(100dvh-60px)] overflow-y-auto overscroll-contain pb-32 shadow-2xl transition-all">
        
        @include('layouts.partials.nav-mobile')

        <!-- Responsive Settings & Logout Options -->
        <div class="mt-4 pt-4 pb-4 border-t border-slate-200 bg-slate-50/90 rounded-b-2xl">
            <div class="px-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-sm text-slate-800 truncate">{{ Auth::user()->name }}</div>
                    @if(Auth::user()->hasRole('admin'))
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-100 text-red-800 border border-red-200">Admin</span>
                    @elseif(Auth::user()->isPimpinan())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">Pimpinan</span>
                    @elseif(Auth::user()->isAtasan())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-blue-100 text-blue-800 border border-blue-200">Atasan</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 text-gray-700 border border-gray-200">Pegawai</span>
                    @endif
                </div>
                @if(Auth::user()->pegawai?->jabatan?->nama_jabatan)
                    <div class="text-xs font-medium text-slate-600 mt-0.5 truncate">{{ Auth::user()->pegawai->jabatan->nama_jabatan }}</div>
                @endif
                <div class="font-mono text-[11px] text-slate-400 mt-0.5">NIP: {{ Auth::user()->pegawai?->nip ?? '-' }}</div>
            </div>

            <div class="mt-3 px-3 space-y-2">
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-200 transition shadow-2xs">
                    <x-icon name="settings" class="w-4 h-4 text-slate-500" />
                    <span>{{ __('Profile & Pengaturan Akun') }}</span>
                </a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 transition text-left shadow-2xs cursor-pointer">
                        <span class="flex items-center gap-2">
                            <x-icon name="log-out" class="w-4 h-4 text-rose-600" />
                            <span>{{ __('Log Out (Keluar Aplikasi)') }}</span>
                        </span>
                        <span class="text-[10px] text-rose-500 font-normal">Selesai Sesi</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>