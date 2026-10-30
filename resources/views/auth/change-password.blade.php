<x-guest-layout>
    {{-- ========================================== --}}
    {{-- 1. HEADER & BRANDING --}}
    {{-- ========================================== --}}
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
        <h2 class="text-lg font-bold text-slate-800">Wajib Ganti Password Bawaan</h2>
        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            Demi menjaga keamanan akun dan data kepegawaian Anda di SIKAP FKP UNRI, Anda diwajibkan membuat kata sandi baru pribadi sebelum melanjutkan.
        </p>
    </div>

    {{-- Alert / Warning Message --}}
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

        {{-- Password Saat Ini --}}
        <div>
            <label for="current_password" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                Kata Sandi Bawaan Saat Ini
            </label>
            <input id="current_password" 
                   class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#0b3b7c] focus:ring focus:ring-[#0b3b7c]/20 text-sm py-2 px-3" 
                   type="password" 
                   name="current_password" 
                   placeholder="Masukkan sandi saat ini (contoh: Password)"
                   required 
                   autofocus />
            <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
        </div>

        {{-- Password Baru --}}
        <div>
            <label for="password" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                Kata Sandi Baru
            </label>
            <input id="password" 
                   class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#0b3b7c] focus:ring focus:ring-[#0b3b7c]/20 text-sm py-2 px-3" 
                   type="password" 
                   name="password" 
                   placeholder="Minimal 8 karakter kombinasi huruf & angka"
                   required />
            <p class="text-[11px] text-slate-500 mt-1">Minimal 8 karakter, wajib memuat kombinasi huruf dan angka.</p>
            <x-input-error :messages="$errors->get('password')" class="mt-1" />
        </div>

        {{-- Konfirmasi Password Baru --}}
        <div>
            <label for="password_confirmation" class="block font-semibold text-xs text-slate-700 uppercase tracking-wider mb-1">
                Ulangi Kata Sandi Baru
            </label>
            <input id="password_confirmation" 
                   class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-[#0b3b7c] focus:ring focus:ring-[#0b3b7c]/20 text-sm py-2 px-3" 
                   type="password" 
                   name="password_confirmation" 
                   placeholder="Ketik ulang kata sandi baru"
                   required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1" />
        </div>

        <div class="pt-2 flex flex-col gap-2.5">
            <button type="submit" 
                    class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-[#0b3b7c] hover:bg-[#082a5a] text-white rounded-lg font-bold text-sm uppercase tracking-wider shadow-md hover:shadow-lg transition focus:outline-none">
                {{ __('Simpan & Aktifkan Akun') }}
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

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</x-guest-layout>
