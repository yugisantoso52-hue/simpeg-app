<x-guest-layout>
    <div class="text-center py-4">
        <div class="w-16 h-16 bg-blue-50 text-[#0b3b7c] rounded-2xl flex items-center justify-center mx-auto text-2xl font-black mb-4 border border-blue-200">
            419
        </div>
        <h1 class="text-lg font-extrabold text-slate-900 mb-1">Sesi Telah Berakhir</h1>
        <p class="text-xs text-slate-500 leading-relaxed mb-6">
            Sesi keamanan halaman Anda telah kedaluwarsa. Silakan muat ulang halaman dan masuk kembali.
        </p>
        <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-[#0b3b7c] hover:bg-[#082d62] text-white text-xs font-bold transition shadow-xs">
            &larr; Ke Halaman Login
        </a>
    </div>
</x-guest-layout>
