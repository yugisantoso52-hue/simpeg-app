<x-guest-layout :fullWidth="true">
    <div x-data="{ 
            showPassword: false,
            openVerifyModal: false,
            openExecutiveModal: false,
            verifyCode: ''
         }" 
         class="min-h-screen w-full flex flex-col lg:flex-row text-slate-800 antialiased selection:bg-[#0b3b7c] selection:text-white">

        <!-- ======================================================== -->
        <!-- SISI KIRI: BRANDING, VISUAL & SECURITY SHOWCASE (DESKTOP) -->
        <!-- ======================================================== -->
        <div class="hidden lg:flex lg:w-7/12 xl:w-3/5 text-white p-8 xl:p-14 relative flex-col justify-between overflow-hidden shadow-2xl"
             style="background: linear-gradient(135deg, #031530 0%, #072552 40%, #0a3875 100%);">
            
            <!-- Ornamen Tekstur & Glow Latar Belakang -->
            <div class="absolute inset-0 opacity-40 pointer-events-none bg-cover bg-center" style="background-image: url('{{ asset('images/bg-blue-regal.jpg') }}'); mix-blend-mode: overlay;"></div>
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-blue-400/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-amber-400/15 blur-3xl pointer-events-none"></div>

            <!-- Bingkai Linework Geometris Emas Mewah (Vector Art Deco / Regal Certificate) -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" style="opacity: 0.65;" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" viewBox="0 0 1000 1000">
                <defs>
                    <linearGradient id="goldLineworkGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#fcedc0" />
                        <stop offset="35%" stop-color="#d4af37" />
                        <stop offset="70%" stop-color="#aa771c" />
                        <stop offset="100%" stop-color="#fcedc0" />
                    </linearGradient>
                </defs>
                <!-- Bingkai luar bersudut miring (chamfered) -->
                <polygon points="35,20 965,20 980,35 980,965 965,980 35,980 20,965 20,35" fill="none" stroke="url(#goldLineworkGrad)" stroke-width="1.6"/>
                <!-- Garis paralel dalam -->
                <polygon points="45,30 955,30 970,45 970,955 955,970 45,970 30,955 30,45" fill="none" stroke="url(#goldLineworkGrad)" stroke-width="0.8" stroke-dasharray="8 4"/>
                
                <!-- Garis diagonal sudut khas desain geometris mewah -->
                <line x1="20" y1="35" x2="160" y2="175" stroke="url(#goldLineworkGrad)" stroke-width="1.2"/>
                <line x1="980" y1="35" x2="840" y2="175" stroke="url(#goldLineworkGrad)" stroke-width="1.2"/>
                <line x1="20" y1="965" x2="160" y2="825" stroke="url(#goldLineworkGrad)" stroke-width="1.2"/>
                <line x1="980" y1="965" x2="840" y2="825" stroke="url(#goldLineworkGrad)" stroke-width="1.2"/>

                <!-- Aksen diamond / intan di setiap sudut -->
                <polygon points="90,90 105,75 120,90 105,105" fill="none" stroke="url(#goldLineworkGrad)" stroke-width="1"/>
                <polygon points="910,90 925,75 910,90 895,105" fill="none" stroke="url(#goldLineworkGrad)" stroke-width="1"/>
                <polygon points="90,910 105,895 120,910 105,925" fill="none" stroke="url(#goldLineworkGrad)" stroke-width="1"/>
                <polygon points="910,910 925,895 910,910 895,925" fill="none" stroke="url(#goldLineworkGrad)" stroke-width="1"/>
            </svg>

            <!-- 1. Header Instansi -->
            <div class="relative z-10">
                <div class="flex items-center gap-4">
                    <a href="/" class="shrink-0 transition transform hover:scale-105 duration-200">
                        <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-16 w-auto object-contain drop-shadow-md">
                    </a>
                    <div class="leading-tight notranslate" translate="no">
                        <p class="text-[11px] font-semibold tracking-widest text-[#a6c8f5] uppercase">
                            KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI
                        </p>
                        <h2 class="text-base font-extrabold tracking-wide uppercase text-white mt-0.5">
                            UNIVERSITAS RIAU
                        </h2>
                        <h3 class="text-xs sm:text-sm font-bold tracking-wider uppercase text-[#e2bb69]">
                            FAKULTAS KEPERAWATAN
                        </h3>
                    </div>
                </div>
            </div>

            <!-- 2. Hero Headline & Informasi Utama -->
            <div class="relative z-10 my-auto py-8">
                <!-- Badge Kategori -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-[#072450]/80 border border-[#d4af37]/50 text-xs font-semibold text-[#f1c97a] uppercase tracking-wider mb-5 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                    SISTEM INFORMASI KEPEGAWAIAN (SIKAP)
                </div>

                <h1 class="text-3xl xl:text-4xl 2xl:text-5xl font-extrabold text-white leading-tight tracking-tight font-serif">
                    Platform Terpadu Manajemen SDM & Kinerja ASN
                </h1>

                <p class="mt-4 text-blue-100/90 text-sm xl:text-base leading-relaxed max-w-2xl font-light">
                    Mewujudkan tata kelola kepegawaian Fakultas Keperawatan Universitas Riau yang transparan, akuntabel, dan berbasis digital untuk seluruh Dosen dan Tenaga Kependidikan.
                </p>

                <!-- Box Peringatan Keamanan & Pembaruan -->
                <div class="mt-8 bg-[#061e47]/70 backdrop-blur-md border border-[#d4af37]/45 rounded-2xl p-5 shadow-xl max-w-2xl text-left">
                    <div class="flex items-center gap-2.5 text-amber-300 font-bold text-xs uppercase tracking-wider mb-3">
                        <span class="text-sm">⚠️</span>
                        KEAMANAN AKUN & FITUR RESMI SIKAP
                    </div>
                    <ul class="space-y-2 text-xs text-blue-50/95 leading-relaxed">
                        <li class="flex items-start gap-2">
                            <span class="text-[#e2bb69] font-bold">•</span>
                            <div>
                                <span><strong>Jaga Kerahasiaan Akun:</strong> Jangan bagikan NIP dan kata sandi Anda kepada pihak yang tidak berwenang.</span>
                                <span class="block mt-0.5 text-amber-200 font-medium">Segera ganti Password login pertama Anda.</span>
                            </div>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#e2bb69] font-bold">•</span>
                            <span><strong>Watermark & Tanda Tangan Digital:</strong> Seluruh kelola SK dan Formulir Cuti yang diunduh kini otomatis dilindungi tanda tangan digital terverifikasi.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="text-[#e2bb69] font-bold">•</span>
                            <span><strong>Dukungan PWA & Offline:</strong> Aplikasi dapat diinstal ke layar utama HP Anda dengan fitur penyimpanan draf logbook saat tanpa internet.</span>
                        </li>
                    </ul>
                </div>

                <!-- Tautan Cepat Layanan Publik -->
                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <button type="button"
                            @click="openExecutiveModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-[#dca34d] via-[#e5b869] to-[#c49335] text-slate-950 text-xs font-bold shadow-md hover:brightness-110 transition cursor-pointer">
                        <span>🎨</span>
                        <span>Paparan Eksekutif &amp; Ringkasan SIKAP</span>
                    </button>
                    <button type="button"
                            @click="openVerifyModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#0a2754]/80 hover:bg-[#0f346e] border border-[#d4af37]/60 text-xs font-semibold text-white transition shadow-xs cursor-pointer">
                        <span>🛡️</span>
                        <span>Verifikasi Dokumen SK Publik</span>
                    </button>
                    <a href="http://keperawatan.unri.ac.id" target="_blank"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#0a2754]/80 hover:bg-[#0f346e] border border-[#d4af37]/60 text-xs font-semibold text-white transition shadow-xs">
                        <span>🌐</span>
                        <span>Portal Resmi FKP UNRI</span>
                    </a>
                </div>
            </div>

            <!-- 3. Footer Kiri -->
            <div class="relative z-10 pt-4 border-t border-white/15 flex flex-col sm:flex-row items-start sm:items-center justify-between text-xs text-blue-200/80 gap-2">
                <div>
                    <span>&copy; {{ date('Y') }} Fakultas Keperawatan Universitas Riau</span>
                    <span class="block text-[11px] text-blue-200/70 mt-0.5">Pengembang Sistem: Rahmad Hidayat Majlan, S.T. (RHM)</span>
                </div>
                <span class="text-right text-[11px] text-blue-200/75">Kampus Bina Widya Gedung HSC Km.12,5 Pekanbaru</span>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- SISI KANAN: FORMULIR LOGIN CALACATTA MARBLE             -->
        <!-- ======================================================== -->
        <div class="w-full lg:w-5/12 xl:w-2/5 flex flex-col justify-between p-6 sm:p-10 xl:p-12 relative z-10 min-h-screen lg:min-h-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/bg-marble.jpg') }}'); background-color: #faf8f5;">
            
            <!-- Lapisan Translusen Sangat Halus -->
            <div class="absolute inset-0 bg-white/10 pointer-events-none"></div>

            <!-- Ornamen Sudut Kristal Biru-Emas (Khusus Tampilan Mobile di Pojok Atas) -->
            <img src="{{ asset('images/facet-top-left-trans.png') }}?v={{ file_exists(public_path('images/facet-top-left-trans.png')) ? filemtime(public_path('images/facet-top-left-trans.png')) : 2 }}" alt="" class="lg:hidden absolute top-0 left-0 w-28 sm:w-36 pointer-events-none z-0">
            <img src="{{ asset('images/facet-top-right-trans.png') }}?v={{ file_exists(public_path('images/facet-top-right-trans.png')) ? filemtime(public_path('images/facet-top-right-trans.png')) : 2 }}" alt="" class="lg:hidden absolute top-0 right-0 w-28 sm:w-36 pointer-events-none z-0">

            <!-- Mobile Header (Muncul Ketika Layar Browser Dikecilkan / Mobile Screen) -->
            <div class="flex lg:hidden flex-col items-center text-center pt-2 pb-5 relative z-10">
                <a href="/" class="shrink-0 transition transform hover:scale-105 duration-200">
                    <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-16 w-auto object-contain drop-shadow-md mx-auto mb-2">
                </a>
                <div class="notranslate" translate="no">
                    <p class="text-[9px] sm:text-[10px] font-bold text-slate-700 tracking-wider uppercase">
                        KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI
                    </p>
                    <h2 class="text-sm sm:text-base font-black tracking-wide uppercase text-[#0b3b7c] mt-0.5">
                        UNIVERSITAS RIAU
                    </h2>
                    <h3 class="text-xs sm:text-sm font-bold tracking-wider uppercase text-slate-800 mt-0.5">
                        FAKULTAS KEPERAWATAN
                    </h3>
                    <h1 class="text-sm sm:text-base font-black text-[#0b3b7c] tracking-tight mt-1.5">
                        Sistem Informasi Kepegawaian (SIKAP)
                    </h1>
                    <div class="mt-2.5">
                        <button type="button"
                                @click="openExecutiveModal = true"
                                class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-gradient-to-r from-[#dca34d] via-[#e5b869] to-[#c49335] text-slate-950 text-xs font-bold shadow-md hover:brightness-110 transition cursor-pointer">
                            <span>🎨</span>
                            <span>Paparan Eksekutif &amp; Ringkasan SIKAP</span>
                        </button>
                    </div>
                </div>
                <!-- Garis Pemisah Elegan Mobile -->
                <div class="w-full max-w-sm mx-auto h-[1px] bg-gradient-to-r from-transparent via-slate-300 to-transparent mt-5 mb-1"></div>
            </div>

            <!-- Bagian Tengah: Kartu Formulir Login -->
            <div class="my-auto py-2 max-w-md w-full mx-auto relative z-10">
                <div class="mb-7">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md bg-[#fdfaf2]/95 border border-[#c59b27]/80 text-[#5c4314] text-[11px] font-bold uppercase tracking-wider mb-2.5 shadow-xs">
                        <span>🔒</span> PORTAL AKSES PEGAWAI
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">
                        <span class="text-slate-900">Selamat </span><span class="text-[#b8860b]">Datang</span>
                    </h2>
                    <p class="text-slate-600 text-xs sm:text-sm mt-1 leading-relaxed">
                        Silakan masukkan identitas NIP atau Username untuk mengakses sistem kepegawaian Anda.
                    </p>
                </div>

                <!-- Notifikasi Status Sesi -->
                <x-auth-session-status class="mb-4" :status="session('status')" />

                <!-- Formulir Login -->
                <form method="POST" action="{{ route('login') }}" class="space-y-4">
                    @csrf

                    <!-- Field NIP / Username (Input Pill Berbingkai Emas Mewah) -->
                    <div>
                        <label for="login" class="block font-bold text-xs text-slate-800 uppercase tracking-wider mb-1.5">
                            NIP / USERNAME
                        </label>
                        <div class="relative rounded-full shadow-xs focus-within:ring-2 focus-within:ring-[#0b3b7c]/20 transition-all"
                             style="border: 1.8px solid #a3824c; background-color: #ffffff;">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input id="login"
                                   type="text"
                                   name="login"
                                   value="{{ old('login') }}"
                                   placeholder="Contoh: 198501012010121001"
                                   class="block w-full pl-11 pr-4 py-3 text-sm bg-transparent rounded-full focus:outline-none font-medium text-slate-800 placeholder-slate-400"
                                   required
                                   autofocus
                                   autocomplete="username" />
                        </div>
                        <x-input-error :messages="$errors->get('login')" class="mt-1" />
                    </div>

                    <!-- Field Password (Input Pill Berbingkai Emas Mewah dengan Toggle Show/Hide) -->
                    <div>
                        <label for="password" class="block font-bold text-xs text-slate-800 uppercase tracking-wider mb-1.5">
                            KATA SANDI
                        </label>
                        <div class="relative rounded-full shadow-xs focus-within:ring-2 focus-within:ring-[#0b3b7c]/20 transition-all"
                             style="border: 1.8px solid #a3824c; background-color: #ffffff;">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-500">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input id="password"
                                   :type="showPassword ? 'text' : 'password'"
                                   name="password"
                                   placeholder="Masukkan kata sandi akun Anda"
                                   class="block w-full pl-11 pr-11 py-3 text-sm bg-transparent rounded-full focus:outline-none font-medium text-slate-800 placeholder-slate-400"
                                   required
                                   autocomplete="current-password" />

                            <!-- Tombol Show/Hide Password -->
                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
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

                    <!-- Petunjuk Akun Baru (Box Bernuansa Soft Blue & Gold) -->
                    <div class="p-3 rounded-2xl text-slate-800 text-xs leading-relaxed flex items-center gap-2.5 shadow-xs"
                         style="background: linear-gradient(90deg, rgba(219, 234, 254, 0.9) 0%, rgba(240, 249, 255, 0.85) 50%, rgba(254, 243, 199, 0.9) 100%); border: 1px solid rgba(197, 155, 39, 0.7);">
                        <span class="text-base select-none shrink-0">🔑</span>
                        <div class="text-[11px] text-slate-800 leading-normal">
                            <strong class="text-slate-900 font-bold">Login Pertama Kali:</strong> Masukkan 18 digit NIP Anda dan kata sandi default <span class="bg-[#bfdbfe] text-[#1e3a8a] font-bold px-2 py-0.5 rounded-md font-mono text-[11px] border border-[#93c5fd]">Password.</span>
                        </div>
                    </div>

                    <!-- Ingat Saya & Lupa Sandi -->
                    <div class="flex items-center justify-between pt-1">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                            <input id="remember_me" type="checkbox" class="w-4 h-4 rounded border-slate-400 text-[#0b3b7c] focus:ring-[#0b3b7c] cursor-pointer" name="remember">
                            <span class="ms-2 text-xs text-slate-700 font-medium">{{ __('Ingat saya') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-xs font-semibold text-slate-600 hover:text-[#0b3b7c] hover:underline focus:outline-none" href="{{ route('password.request') }}">
                                {{ __('Lupa password?') }}
                            </a>
                        @endif
                    </div>

                    <!-- Tombol Masuk ke SIKAP (Pill Mewah Gradasi Royal Blue & Glow Emas) -->
                    <div class="pt-2">
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center gap-2 py-3.5 px-6 rounded-full text-white font-extrabold text-sm uppercase tracking-wider transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0b3b7c]"
                                style="background: linear-gradient(90deg, #072e66 0%, #0c4384 50%, #062654 100%); border: 1.5px solid #d4af37; box-shadow: 0 4px 18px rgba(8,41,89,0.45), 0 0 14px rgba(212,175,55,0.35);">
                            <span>MASUK KE SIKAP</span>
                            <span class="text-base font-bold">&rarr;</span>
                        </button>
                    </div>
                </form>

                <!-- Layanan Cepat untuk Layar Mobile -->
                <div class="mt-8 pt-5 border-t border-slate-300/80 flex flex-col gap-2.5 lg:hidden text-center">
                    <span class="text-[11px] font-extrabold uppercase tracking-widest text-slate-600">LAYANAN PUBLIK</span>
                    <div class="flex items-center justify-center gap-3 text-xs font-semibold text-slate-800">
                        <button type="button" @click="openVerifyModal = true" class="hover:text-[#0b3b7c] flex items-center gap-1.5 cursor-pointer">
                            <span>🔍</span> Cek SK Publik
                        </button>
                        <span class="text-slate-400">•</span>
                        <a href="http://keperawatan.unri.ac.id" target="_blank" class="hover:text-[#0b3b7c] flex items-center gap-1.5">
                            <span>🌐</span> Web Fakultas
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bagian Bawah Form (Footer Status & Info Pengembang) -->
            <div class="pt-6 border-t border-slate-300/80 flex flex-col gap-2 text-[11px] text-slate-600 relative z-10">
                <div class="flex items-center justify-between font-medium">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-xs"></span>
                        Server SIKAP Online
                    </span>
                    <span>Enkripsi TLS/SSL 256-Bit</span>
                </div>
                <div class="text-left text-slate-500 pt-1 border-t border-slate-200/60 font-normal">
                    Pengembang: Rahmad Hidayat Majlan, S.T. (RHM)
                </div>
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
                            <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0b3b7c] flex items-center justify-center font-bold text-lg">
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
                               class="w-full px-3.5 py-2.5 text-sm bg-slate-50 rounded-xl border border-slate-300 focus:border-[#0b3b7c] focus:ring-2 focus:ring-[#0b3b7c]/20 font-mono text-slate-800"
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
                                class="px-4 py-2 text-xs font-bold text-white bg-[#0b3b7c] hover:bg-[#082d62] rounded-xl shadow-xs transition cursor-pointer">
                            Periksa Dokumen →
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- MODAL PAPARAN EKSEKUTIF & RINGKASAN SISTEM (LOGIN)      -->
        <!-- ======================================================== -->
        <div x-show="openExecutiveModal" 
             class="fixed inset-0 z-50 overflow-y-auto" 
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-slate-900/60 backdrop-blur-xs" @click="openExecutiveModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-slate-100 sm:align-middle relative z-10 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 text-[#0b3b7c] flex items-center justify-center font-bold text-xl">
                                🎯
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 leading-tight">Paparan Eksekutif &amp; Transformasi Digital Kepegawaian</h3>
                                <p class="text-xs text-slate-500">Sistem Informasi Kepegawaian (SIKAP) • Fakultas Keperawatan UNRI</p>
                            </div>
                        </div>
                        <button type="button" @click="openExecutiveModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-3">
                        <div class="p-3.5 rounded-xl bg-blue-50/80 border border-blue-200 text-xs text-blue-950 leading-relaxed">
                            <strong class="text-[#0b3b7c] block font-bold mb-1 flex items-center gap-1.5">
                                <span>🏛️</span> Mengapa Fakultas Butuh Aplikasi SIKAP?
                            </strong>
                            Selama ini data kepegawaian dikelola manual (berkas fisik &amp; Excel terpisah). Meskipun sudah ada sistem yang dibuat oleh Universitas, <strong>SIKAP hadir sebagai sistem mandiri fakultas sekaligus penopang darurat (Back-up) disaat sistem milik Universitas mengalami kendala teknis</strong>, sehingga layanan presensi dan tata kelola SDM di Fakultas Keperawatan tetap berjalan stabil tanpa terhenti.
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2.5 text-xs">
                            <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-200">
                                <strong class="text-blue-900 block font-bold text-[11px] uppercase mb-1">1. Layanan Mandiri di HP</strong>
                                <p class="text-slate-600 text-[10px] leading-relaxed">
                                    Presensi GPS selfie, pelaporan E-Logbook 7.5 jam/hari, dan pengajuan cuti paperless langsung dari smartphone.
                                </p>
                            </div>

                            <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200">
                                <strong class="text-amber-900 block font-bold text-[11px] uppercase mb-1">2. Supervisi Atasan</strong>
                                <p class="text-slate-600 text-[10px] leading-relaxed">
                                    Verifikasi tugas logbook staf harian, pemberian catatan pembinaan, dan pertimbangan cuti dalam 1 klik.
                                </p>
                            </div>

                            <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-200">
                                <strong class="text-emerald-900 block font-bold text-[11px] uppercase mb-1">3. Kokpit Dekanat</strong>
                                <p class="text-slate-600 text-[10px] leading-relaxed">
                                    Peta Jabatan ABK (posisi defisit/surplus), radar pensiun 1-3 tahun, dan Matriks Talenta 9-Kotak untuk dasar usulan ke Rektorat.
                                </p>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1">
                            <strong class="text-slate-800 block font-bold text-[11px]">Kepatuhan Regulasi Nasional:</strong>
                            <p class="text-slate-600 text-[11px] leading-relaxed">
                                Dibangun mengacu pada <strong>PermenPAN-RB No. 1/2020</strong> (Anjab-ABK 1.250 jam/tahun), <strong>PermenPAN-RB No. 3/2020</strong> (Manajemen Talenta), dan <strong>Perka BKN No. 24/2017</strong> (Cuti ASN).
                            </p>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-900 flex items-center gap-2">
                        <span class="text-amber-700 font-bold shrink-0">ℹ️</span>
                        <span><strong>Akses Lengkap:</strong> Silakan login menggunakan akun <strong>Pimpinan (Dekan/Wadek)</strong> atau <strong>Administrator</strong> untuk melihat statistik live, peta interaktif, dan seluruh fitur operasional.</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" 
                                @click="openExecutiveModal = false"
                                class="px-4 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                            Tutup
                        </button>
                        <button type="button" 
                                @click="openExecutiveModal = false; setTimeout(() => document.getElementById('login')?.focus(), 100);"
                                class="px-4 py-2 text-xs font-bold text-white bg-[#0b3b7c] hover:bg-[#082d62] rounded-xl shadow-xs transition cursor-pointer">
                            Login ke Dashboard &rarr;
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-guest-layout>
