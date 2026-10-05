<nav x-data="{ open: false }" class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-xs transition-all">
    <!-- ========================================== -->
    <!-- 1. KOP HEADER INSTANSI & PROFIL PENGGUNA   -->
    <!-- ========================================== -->
    <div class="relative w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10 pt-2.5 sm:pt-3 pb-2 overflow-visible">
        
        <div class="flex items-center justify-between gap-3 sm:gap-6">
            
            <!-- Sisi Kiri: Logo Kementerian Pendidikan Tinggi, Sains, dan Teknologi (Gbr 3) -->
            <div class="flex items-center justify-start shrink-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 group transition transform hover:scale-105 duration-200" title="Kementerian Pendidikan Tinggi, Sains, dan Teknologi">
                    <img src="{{ asset('logo-kemendiktisaintek.png') }}" 
                         alt="Logo Kementerian Pendidikan Tinggi, Sains, dan Teknologi" 
                         class="h-[50px] sm:h-[62px] md:h-[72px] w-auto object-contain drop-shadow-2xs">
                </a>
            </div>

            <!-- Bagian Tengah: Teks Kop Surat Resmi Institusi -->
            <div class="flex-1 text-center leading-tight px-1 sm:px-3 notranslate max-w-4xl mx-auto" translate="no">
                <h2 class="text-[10px] sm:text-[11.5px] md:text-[12.5px] font-semibold tracking-wider text-slate-600 uppercase">
                    KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI
                </h2>
                <h3 class="text-[12.5px] sm:text-[14px] md:text-[16px] font-bold tracking-wide text-slate-900 uppercase my-0.5">
                    UNIVERSITAS RIAU
                </h3>
                <h4 class="text-[13.5px] sm:text-[15.5px] md:text-[17px] font-black tracking-wide uppercase text-[#007a3d]">
                    FAKULTAS KEPERAWATAN
                </h4>
                <h1 class="text-[11.5px] sm:text-[13px] md:text-[14px] font-bold tracking-wider uppercase text-slate-800 mt-0.5">
                    SISTEM INFORMASI KEPEGAWAIAN (SIKAP)
                </h1>
                <p class="text-[9.5px] sm:text-[10.5px] leading-tight text-slate-500 mt-1">
                    Kampus Bina Widya Gedung Health Studies Complex Km.12,5 Simpang Baru 28293
                </p>
                <p class="text-[9.5px] sm:text-[10.5px] leading-tight text-slate-500">
                    Laman: <a href="http://keperawatan.unri.ac.id" target="_blank" class="text-blue-600 hover:underline">http://keperawatan.unri.ac.id</a> | Email: <a href="mailto:keperawatan@unri.ac.id" class="text-blue-600 hover:underline">keperawatan@unri.ac.id</a>
                </p>
            </div>

            <!-- Sisi Kanan: Logo Universitas Riau & Profil Pengguna Desktop -->
            <div class="flex items-center justify-end gap-3 sm:gap-4 shrink-0">
                <a href="{{ route('dashboard') }}" class="block transition transform hover:scale-105 duration-200" title="Universitas Riau">
                    <img src="{{ asset('logo-unri.png') }}" 
                         alt="Logo Universitas Riau" 
                         class="h-[50px] sm:h-[62px] md:h-[72px] w-auto object-contain drop-shadow-2xs">
                </a>

                <!-- Profile User Desktop Dropdown -->
                <div class="hidden md:flex items-center pl-1 sm:pl-2">
                    <x-dropdown align="right" width="60">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-1.5 border border-slate-200 text-xs font-semibold rounded-full text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-300 focus:outline-none transition shadow-2xs cursor-pointer gap-2">
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                <span class="max-w-[130px] truncate font-medium">{{ Auth::user()->name }}</span>
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

                <!-- Mobile Hamburger Button (< 768px) -->
                <div class="flex items-center md:hidden">
                    <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none transition" aria-label="Buka Menu">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Garis Pemisah Khas Kop Surat Resmi Instansi (Garis Ganda Formal) -->
    <div class="w-full max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-10">
        <div class="border-b-2 border-slate-900"></div>
        <div class="border-b border-slate-400 mt-[1.5px] mb-1"></div>
    </div>

    <!-- ========================================== -->
    <!-- 2. MENU NAVIGASI UTAMA (FLUID 1 BARIS RAPI) -->
    <!-- ========================================== -->
    <div class="w-full max-w-[1920px] mx-auto px-2 sm:px-4 lg:px-6 xl:px-8 notranslate" translate="no">
        <div class="flex items-center justify-center min-h-[46px]">

            <!-- Desktop Navigation Links (Full Width 1 Baris Simetris Tanpa Terpotong) -->
            <nav class="hidden md:flex items-center justify-center flex-nowrap overflow-x-auto lg:overflow-visible gap-x-1 sm:gap-x-1.5 md:gap-x-2 lg:gap-x-2.5 xl:gap-x-3 py-1.5 notranslate" translate="no">
                @include('layouts.partials.nav-desktop')
            </nav>

            <!-- Mobile Sub-bar Text (< 768px) -->
            <div class="flex items-center justify-between w-full md:hidden py-1.5 px-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-[#007a3d]"></span>
                    SIKAP UNRI
                </span>
                <span class="text-xs text-slate-500 font-medium">
                    {{ Auth::user()->name }}
                </span>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Khusus Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden md:hidden border-t border-gray-200 bg-gray-50 max-h-[80vh] overflow-y-auto">
        @include('layouts.partials.nav-mobile')

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-3 border-t border-gray-200 bg-white">
            <div class="px-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="font-bold text-base text-gray-800">{{ Auth::user()->name }}</div>
                    @if(Auth::user()->hasRole('admin'))
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 border border-red-200">Admin</span>
                    @elseif(Auth::user()->isPimpinan())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Pimpinan</span>
                    @elseif(Auth::user()->isAtasan())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">Atasan</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700 border border-gray-200">Pegawai</span>
                    @endif
                </div>
                @if(Auth::user()->pegawai?->jabatan?->nama_jabatan)
                    <div class="text-xs font-medium text-slate-600 mt-0.5">{{ Auth::user()->pegawai->jabatan->nama_jabatan }}</div>
                @endif
                <div class="font-mono text-xs text-gray-400 mt-0.5">NIP: {{ Auth::user()->pegawai?->nip ?? '-' }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="flex items-center gap-2">
                    <x-icon name="settings" class="w-4 h-4 text-slate-500" />
                    <span>{{ __('Profile & Pengaturan') }}</span>
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();"
                            class="flex items-center gap-2 text-rose-600">
                        <x-icon name="log-out" class="w-4 h-4 text-rose-500" />
                        <span>{{ __('Log Out') }}</span>
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>