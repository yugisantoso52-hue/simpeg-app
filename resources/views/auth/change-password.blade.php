<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-xl font-bold text-gray-900">Ganti Password Default</h2>
        <p class="text-sm text-gray-600 mt-1">
            Demi keamanan akun Anda, Anda diwajibkan untuk mengganti password default Anda saat login pertama kali.
        </p>
    </div>

    <!-- Alert / Warning Message -->
    @if(session('warning'))
        <div class="mb-4 bg-amber-50 border-l-4 border-amber-500 p-3 rounded text-amber-800 text-xs">
            {{ session('warning') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.change') }}">
        @csrf

        <!-- Password Saat Ini -->
        <div>
            <x-input-label for="current_password" :value="__('Password Saat Ini (Password Default)')" />
            <x-text-input id="current_password" class="block mt-1 w-full" type="password" name="current_password" required autofocus />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <!-- Password Baru -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password Baru')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Password Baru -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Password Baru')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <!-- Logout Button (Alternative if they want to leave) -->
            <button type="button" 
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="text-sm text-red-600 hover:text-red-800 underline">
                Batal & Keluar
            </button>

            <x-primary-button>
                {{ __('Perbarui Password') }}
            </x-primary-button>
        </div>
    </form>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</x-guest-layout>
