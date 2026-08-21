<x-guest-layout>
    <!-- ========================================== -->
    <!-- 1. HEADER & BRANDING                       -->
    <!-- ========================================== -->
    <div class="text-center mb-6">
        <a href="/" class="inline-block transition transform hover:scale-105 duration-200">
            <img src="{{ asset('logo-unri.png') }}" alt="Logo UNRI" class="h-[65px] w-auto mx-auto object-contain">
        </a>
        <h1 class="text-2xl font-black text-slate-900 tracking-wide mt-3 uppercase">
            SIKAP
        </h1>
        <p class="text-xs font-semibold text-slate-600 tracking-wider uppercase">
            Sistem Informasi Kepegawaian
        </p>
        <p class="text-xs font-extrabold text-[#007a3d] uppercase mt-0.5">
            Fakultas Keperawatan Universitas Riau
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- ========================================== -->
    <!-- 2. FORM LOGIN                              -->
    <!-- ========================================== -->
    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Field NIP -->
        <div>
            <label for="login" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                NIP
            </label>
            <div class="relative">
                <input id="login"
                       type="text"
                       name="login"
                       value="{{ old('login') }}"
                       placeholder="Masukkan 18 digit NIP"
                       inputmode="numeric"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                       class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#007a3d] focus:ring focus:ring-[#007a3d]/20 text-sm py-2.5 px-3"
                       required
                       autofocus
                       autocomplete="username" />
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-1" />
        </div>

        <!-- Field Password dengan Toggle Show/Hide -->
        <div x-data="{ showPassword: false }">
            <label for="password" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                Password
            </label>
            <div class="relative">
                <input id="password"
                       :type="showPassword ? 'text' : 'password'"
                       name="password"
                       placeholder="Masukkan password (default: YYYYMMDD)"
                       class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#007a3d] focus:ring focus:ring-[#007a3d]/20 text-sm py-2.5 pl-3 pr-10"
                       required
                       autocomplete="current-password" />

                <!-- Tombol Toggle Show/Hide Password -->
                <button type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none"
                        title="Tampilkan / Sembunyikan Password">
                    <!-- Icon Mata Terbuka (Show) -->
                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <!-- Icon Mata Tertutup (Hide) -->
                    <svg x-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Helper Card / Alert Info Login Pertama -->
        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-900 text-xs leading-relaxed flex items-start gap-2">
            <span class="text-base leading-none select-none">💡</span>
            <div>
                <span class="font-bold">Petunjuk:</span> Untuk login pertama kali, gunakan 18 digit NIP Anda dan password default format tanggal lahir (<span class="font-semibold font-mono">YYYYMMDD</span>, contoh: <span class="font-semibold font-mono">19800615</span>) atau <span class="font-semibold font-mono">Password</span>.
            </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-[#007a3d] shadow-sm focus:ring-[#007a3d]" name="remember">
                <span class="ms-2 text-xs text-slate-600 font-medium">{{ __('Ingat saya') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a class="text-xs font-semibold text-[#007a3d] hover:text-[#006030] hover:underline focus:outline-none" href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            @endif
        </div>

        <!-- Tombol Log In Full Width -->
        <div class="pt-2">
            <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#007a3d] hover:bg-[#006030] active:bg-[#004d26] border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-wider shadow-md hover:shadow-lg transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#007a3d]">
                {{ __('LOG IN') }}
            </button>
        </div>
    </form>
</x-guest-layout>
