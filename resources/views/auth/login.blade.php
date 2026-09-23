<x-guest-layout :fullWidth="true">
    <div x-data="{ 
            showPassword: false,
            openVerifyModal: false,
            verifyCode: ''
         }" 
         class="min-h-screen w-full flex flex-col lg:flex-row bg-slate-50 text-slate-800 antialiased selection:bg-[#007a3d] selection:text-white">

        <!-- ======================================================== -->
        <!-- SISI KIRI: BRANDING, VISUAL & SECURITY SHOWCASE (DESKTOP) -->
        <!-- ======================================================== -->
        <div class="hidden lg:flex lg:w-7/12 xl:w-3/5 bg-gradient-to-br from-[#002f17] via-[#005a2b] to-[#007a3d] text-white p-8 xl:p-14 relative flex-col justify-between overflow-hidden shadow-2xl">
            
            <!-- Ornamen Latar Belakang (Grid & Glow Circles) -->
            <div class="absolute inset-0 opacity-10 pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.3) 1px, transparent 1px); background-size: 24px 24px;"></div>
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-emerald-400/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-emerald-300/15 blur-3xl pointer-events-none"></div>

            <!-- 1. Header Instansi -->
            <div class="relative z-10">
                <div class="flex items-center gap-4">
                    <a href="/" class="shrink-0 transition transform hover:scale-105 duration-200">
                        <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-16 w-auto object-contain drop-shadow-md">
                    </a>
                    <div class="leading-tight notranslate" translate="no">
                        <p class="text-[11px] font-semibold tracking-widest text-emerald-200 uppercase">
                            KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI
                        </p>
                        <h2 class="text-base font-extrabold tracking-wide uppercase text-white mt-0.5">
                            UNIVERSITAS RIAU
                        </h2>
                        <h3 class="text-xs font-bold tracking-wider uppercase text-emerald-300">
                            FAKULTAS KEPERAWATAN
                        </h3>
                    </div>
                </div>
            </div>

            <!-- 2. Hero Headline & Informasi Utama -->
            <div class="relative z-10 my-auto py-8">
                <!-- Badge Kategori -->
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold text-emerald-200 uppercase tracking-wider mb-4 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Informasi Kepegawaian (SIKAP)
                </div>

                <h1 class="text-3xl xl:text-4xl 2xl:text-5xl font-black text-white leading-tight tracking-tight">
                    Platform Terpadu Manajemen SDM & Kinerja ASN
                </h1>

                <p class="mt-4 text-emerald-100/90 text-sm xl:text-base leading-relaxed max-w-2xl font-light">
                    Mewujudkan tata kelola kepegawaian Fakultas Keperawatan Universitas Riau yang transparan, akuntabel, dan berbasis digital untuk seluruh Dosen dan Tenaga Kependidikan.
                </p>

                <!-- Box Peringatan Keamanan & Pembaruan (Inspirasi BKN ASN Digital) -->
                <div class="mt-8 bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-5 shadow-lg max-w-2xl text-left">
                    <div class="flex items-center gap-2.5 text-amber-300 font-bold text-xs uppercase tracking-wider mb-2.5">
                        <svg class="w-4 h-4 fill-current text-amber-300 shrink-0" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Keamanan Akun & Fitur Resmi SIKAP
                    </div>
                    <ul class="space-y-2 text-xs text-emerald-50/95 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-300 font-bold">•</span>
                            <span><strong>Jaga Kerahasiaan Akun:</strong> Jangan bagikan NIP dan kata sandi Anda kepada pihak lain yang tidak berwenang.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-300 font-bold">•</span>
                            <span><strong>Watermark & Tanda Tangan Digital:</strong> Seluruh berkas SK dan Formulir Cuti yang diunduh kini otomatis dilindungi tanda tangan digital terverifikasi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-emerald-300 font-bold">•</span>
                            <span><strong>Dukungan PWA & Offline:</strong> Aplikasi dapat diinstal ke layar utama HP Anda dengan fitur penyimpanan draf logbook saat tanpa internet.</span>
                        </li>
                    </ul>
                </div>

                <!-- Tautan Cepat Layanan Publik -->
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="openVerifyModal = true"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-xs font-semibold text-white transition shadow-xs cursor-pointer">
                        <span>🔍</span>
                        <span>Verifikasi Dokumen SK Publik</span>
                    </button>
                    <a href="http://keperawatan.unri.ac.id" target="_blank"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/25 text-xs font-semibold text-white transition shadow-xs">
                        <span>🌐</span>
                        <span>Portal Resmi FKP UNRI</span>
                    </a>
                </div>
            </div>

            <!-- 3. Footer Kiri -->
            <div class="relative z-10 pt-4 border-t border-white/15 flex flex-col sm:flex-row items-center justify-between text-xs text-emerald-200/80 gap-2">
                <span>&copy; {{ date('Y') }} Fakultas Keperawatan Universitas Riau</span>
                <span>Kampus Bina Widya Gedung HSC Km. 12,5 Pekanbaru</span>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- SISI KANAN: FORMULIR LOGIN CEPAT & MODERN               -->
        <!-- ======================================================== -->
        <div class="w-full lg:w-5/12 xl:w-2/5 flex flex-col justify-between p-6 sm:p-10 xl:p-14 bg-white relative z-10 min-h-screen lg:min-h-0">
            
            <!-- Mobile Header (Hanya Muncul di Tablet & HP) -->
            <div class="flex lg:hidden flex-col items-center text-center pb-6 border-b border-slate-100">
                <a href="/" class="shrink-0 transition transform hover:scale-105 duration-200">
                    <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-14 w-auto object-contain">
                </a>
                <h2 class="text-xs font-extrabold uppercase text-[#007a3d] tracking-wider mt-2.5">
                    FAKULTAS KEPERAWATAN UNIVERSITAS RIAU
                </h2>
                <h1 class="text-xl font-black text-slate-900 uppercase tracking-tight mt-0.5">
                    SIKAP FKP UNRI
                </h1>
            </div>

            <!-- Bagian Tengah: Kartu Formulir Login -->
            <div class="my-auto py-4 max-w-md w-full mx-auto">
                <div class="mb-8">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-emerald-50 border border-emerald-200 text-[#007a3d] text-[11px] font-bold uppercase tracking-wider mb-2">
                        <span>🔐</span> Portal Akses Pegawai
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Selamat Datang
                    </h2>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">
                        Silakan masukkan identitas NIP atau Username untuk mengakses sistem kepegawaian Anda.
                    </p>
                </div>

                <!-- Notifikasi Status Sesi -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Formulir Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Field NIP / Username -->
                    <div>
                        <label for="login" class="block font-bold text-xs text-slate-700 uppercase tracking-wider mb-1.5">
                            NIP / USERNAME
                        </label>
                        <div class="relative rounded-xl shadow-2xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="login"
                                   type="text"
                                   name="login"
                                   value="{{ old('login') }}"
                                   placeholder="Contoh: 198501012010121001"
                                   class="block w-full pl-10 pr-3 py-2.5 text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:border-[#007a3d] focus:ring-2 focus:ring-[#007a3d]/20 transition-all font-medium text-slate-800"
                                   required
                                   autofocus
                                   autocomplete="username" />
                        </div>
                        <x-input-error :messages="$errors->get('login')" class="mt-1" />
                    </div>

                    <!-- Field Password dengan Toggle Show/Hide -->
                    <div>
                        <label for="password" class="block font-bold text-xs text-slate-700 uppercase tracking-wider mb-1.5">
                            KATA SANDI
                        </label>
                        <div class="relative rounded-xl shadow-2xs">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   placeholder="Masukkan kata sandi akun Anda"
                                   class="block w-full pl-10 pr-10 py-2.5 text-sm bg-slate-50 hover:bg-white focus:bg-white rounded-xl border border-slate-300 focus:border-[#007a3d] focus:ring-2 focus:ring-[#007a3d]/20 transition-all font-medium text-slate-800"
                                   required
                                   autocomplete="current-password" />

                            <!-- Tombol Show/Hide Password -->
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                    title="Tampilkan / Sembunyikan Kata Sandi">
                                <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-1" />
                    </div>

                    <!-- Petunjuk Akun Baru -->
                    <div class="p-3 bg-emerald-50/70 border border-emerald-200/80 rounded-xl text-emerald-900 text-xs leading-relaxed flex items-start gap-2.5">
                        <span class="text-emerald-700 font-bold select-none text-sm">💡</span>
                        <div class="text-[11px] text-slate-700">
                            <strong class="text-emerald-900 font-bold">Login Pertama Kali:</strong> Masukkan 18 digit NIP Anda dan kata sandi default <span class="font-mono font-bold text-emerald-800 bg-emerald-100 px-1 py-0.5 rounded">Password</span>.
                        </div>
                    </div>

                    <!-- Ingat Saya & Lupa Sandi -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-slate-300 text-[#007a3d] focus:ring-[#007a3d] cursor-pointer" name="remember">
                            <span class="ms-2 text-xs text-slate-600 font-medium">{{ __('Ingat saya') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-xs font-semibold text-[#007a3d] hover:text-[#005a2b] hover:underline focus:outline-none" href="{{ route('password.request') }}">
                                {{ __('Lupa password?') }}
                            </a>
                        @endif
                    </div>

                    <!-- Tombol Log In -->
                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-[#007a3d] hover:bg-[#006030] active:bg-[#004d26] border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-wider shadow-md hover:shadow-lg transition-all duration-150 ease-in-out cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#007a3d]">
                            <span>MASUK KE SIKAP</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </button>
                    </div>
                </form>

                <!-- Layanan Cepat untuk Layar Mobile -->
                <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col gap-2.5 lg:hidden">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center">Layanan Publik</span>
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" @click="openVerifyModal = true" class="text-xs font-semibold text-[#007a3d] hover:underline">
                            🔍 Cek SK Publik
                        </button>
                        <span class="text-slate-300">•</span>
                        <a href="http://keperawatan.unri.ac.id" target="_blank" class="text-xs font-semibold text-slate-600 hover:underline">
                            🌐 Web Fakultas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bagian Bawah Form -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Server SIKAP Online
                </span>
                <span>Enkripsi TLS/SSL 256-Bit</span>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- MODAL VERIFIKASI KEASLIAN DOKUMEN PUBLIK                -->
        <!-- ======================================================== -->
        <div x-show="openVerifyModal" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="openVerifyModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-100 sm:align-middle relative z-10">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#007a3d] flex items-center justify-center font-bold text-lg">
                                🔍
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 leading-tight">Verifikasi Dokumen SK</h3>
                                <p class="text-xs text-slate-500">Periksa keabsahan berkas resmi FKP UNRI</p>
                            </div>
                        </div>
                        <button type="button" @click="openVerifyModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kode Digital Signature Dokumen
                        </label>
                        <input type="text" 
                               x-model="verifyCode"
                               placeholder="Contoh: SK-KGB-2026-XXXX atau hash unik" 
                               class="w-full px-3.5 py-2.5 text-sm bg-slate-50 rounded-xl border border-slate-300 focus:border-[#007a3d] focus:ring-2 focus:ring-[#007a3d]/20 font-mono text-slate-800"
                               @keydown.enter="if(verifyCode.trim()){ window.location.href = '/verifikasi-dokumen/' + encodeURIComponent(verifyCode.trim()); }">
                        <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                            Masukkan kode tanda tangan elektronik yang tertera pada bagian bawah lembar dokumen SK/KGB atau pindai QR Code dokumen tersebut.
                        </p>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="openVerifyModal = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                            Batal
                        </button>
                        <button type="button"
                                @click="if(verifyCode.trim()){ window.location.href = '/verifikasi-dokumen/' + encodeURIComponent(verifyCode.trim()); }"
                                class="px-4 py-2 text-xs font-bold text-white bg-[#007a3d] hover:bg-[#006030] rounded-xl shadow-xs transition cursor-pointer">
                            Periksa Dokumen →
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-guest-layout>
