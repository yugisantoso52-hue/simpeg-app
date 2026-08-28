<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🚧</span> Modul Dalam Pengembangan
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Fitur SIKAP Enterprise Fakultas Keperawatan UNRI
                </p>
            </div>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                ← Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 md:p-12 space-y-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-amber-100 text-amber-600 rounded-full text-4xl shadow-inner">
                    ⚡
                </div>

                <div class="space-y-2">
                    <span class="inline-block px-3 py-1 bg-amber-50 border border-amber-200 text-amber-700 text-xs font-bold rounded-full uppercase tracking-wider">
                        Tahap Pengembangan
                    </span>
                    <h3 class="text-2xl md:text-3xl font-extrabold text-gray-900">
                        {{ $moduleTitle ?? 'Modul SIKAP Enterprise' }}
                    </h3>
                    <p class="text-gray-500 max-w-md mx-auto text-sm leading-relaxed">
                        Halaman ini sedang dipersiapkan dan akan segera terintegrasi dengan sistem kepegawaian Universitas Riau.
                    </p>
                </div>

                <div class="pt-6 border-t border-gray-100 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow transition">
                        <span>📊</span> Menuju Dashboard
                    </a>
                    @if(Auth::user()->hasRole(['admin', 'pimpinan']))
                        <a href="{{ route('pegawai.index') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition">
                            <span>📄</span> Data Pegawai
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
