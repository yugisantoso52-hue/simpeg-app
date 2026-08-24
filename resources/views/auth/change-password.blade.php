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

    <div class="mb-5 text-center">
        <h2 class="text-lg font-bold text-slate-800">Ganti Password Default</h2>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Demi keamanan akun Anda, Anda disarankan untuk memperbarui password default saat login pertama kali.
        </p>
    </div>

    <!-- Alert / Warning Message -->
    @if(session('warning'))
        <div class="mb-4 bg-amber-50 border border-amber-200 p-3 rounded-lg text-amber-900 text-xs flex items-start gap-2">
            <span class="text-base leading-none select-none">⚠️</span>
            <div>{{ session('warning') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-rose-50 border border-rose-200 p-3 rounded-lg text-rose-900 text-xs flex items-start gap-2">
            <span class="text-base leading-none select-none">❌</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.change') }}" class="space-y-4">
        @csrf

        <!-- Password Saat Ini -->
        <div>
            <label for="current_password" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                Password Saat Ini (Password Default)
            </label>
            <input id="current_password" 
                   class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#007a3d] focus:ring focus:ring-[#007a3d]/20 text-sm py-2 px-3" 
                   type="password" 
                   name="current_password" 
                   placeholder="Masukkan password saat ini"
                   required 
                   autofocus />
            <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
        </div>

        <!-- Password Baru -->
        <div>
            <label for="password" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                Password Baru
            </label>
            <input id="password" 
                   class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#007a3d] focus:ring focus:ring-[#007a3d]/20 text-sm py-2 px-3" 
                   type="password" 
                   name="password" 
                   placeholder="Minimal 8 karakter"
                   required />
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        <!-- Konfirmasi Password Baru -->
        <div>
            <label for="password_confirmation" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                Konfirmasi Password Baru
            </label>
            <input id="password_confirmation" 
                   class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#007a3d] focus:ring focus:ring-[#007a3d]/20 text-sm py-2 px-3" 
                   type="password" 
                   name="password_confirmation" 
                   placeholder="Ketik ulang password baru"
                   required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2 flex flex-col gap-2.5">
            <button type="submit" 
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#007a3d] hover:bg-[#006030] text-white rounded-lg font-bold text-sm uppercase tracking-wider shadow-md hover:shadow-lg transition focus:outline-none">
                {{ __('Perbarui Password') }}
            </button>

            <!-- Opsi Lewati / Nanti Saja (Bypass untuk Pengujian) -->
            <button type="button"
                    onclick="event.preventDefault(); document.getElementById('skip-form').submit();"
                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg font-semibold text-xs transition">
                ⚡ Lewati untuk Sekarang (Masuk ke Dashboard)
            </button>
        </div>

        <div class="text-center pt-2 border-t border-slate-100">
            <button type="button" 
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="text-xs text-slate-500 hover:text-red-600 transition">
                ← Batal & Keluar (Logout)
            </button>
        </div>
    </form>

    <form id="skip-form" action="{{ route('password.skip') }}" method="POST" class="hidden">
        @csrf
    </form>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</x-guest-layout>
