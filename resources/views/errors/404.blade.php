<x-guest-layout>
    <div class="text-center py-4">
        <div class="w-16 h-16 bg-slate-100 text-[#0b3b7c] rounded-2xl flex items-center justify-center mx-auto text-2xl font-black mb-4 border border-slate-200">
            404
        </div>
        <h1 class="text-lg font-extrabold text-slate-900 mb-1">Halaman Tidak Ditemukan</h1>
        <p class="text-xs text-slate-500 leading-relaxed mb-6">
            Tautan atau halaman yang Anda tuju tidak tersedia di Sistem Informasi Kepegawaian (SIKAP) Fakultas Keperawatan UNRI.
        </p>
        <a href="{{ url('/') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-[#0b3b7c] hover:bg-[#082d62] text-white text-xs font-bold transition shadow-xs">
            &larr; Kembali ke Beranda SIKAP
        </a>
    </div>
</x-guest-layout>
