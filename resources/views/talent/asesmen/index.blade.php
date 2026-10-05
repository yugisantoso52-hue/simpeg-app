<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        &larr; Kembali ke Matriks 9-Kotak
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>📝</span> Katalog Hasil Uji Kompetensi &amp; Asesmen ASN
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Data Assessment Center BKN, LPPM, atau Puslatbang LAN sebagai bobot utama Sumbu Potensi Manajemen Talenta
                </p>
            </div>
            <div class="flex items-center gap-2">
                @if(Auth::user()->canManageTalentManagement())
                    <a href="{{ route('manajemen-talenta.asesmen.create') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition">
                        <span>➕</span> Tambah Hasil Asesmen
                    </a>
                @endif
                <a href="{{ route('manajemen-talenta.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>📊</span> Matriks 9-Kotak
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Filter & Search --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <form method="GET" action="{{ route('manajemen-talenta.asesmen.index') }}" class="flex items-center gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama pegawai, NIP, lembaga penyelenggara, nomor surat..." class="w-full text-xs rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 py-2">
                </div>
                <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-xs">
                    🔍 Cari
                </button>
                @if($search)
                    <a href="{{ route('manajemen-talenta.asesmen.index') }}" class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Hasil Asesmen --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-200 text-slate-700 font-extrabold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Pegawai</th>
                            <th class="py-3 px-4">Tanggal &amp; Penyelenggara</th>
                            <th class="py-3 px-4">Metode Asesmen</th>
                            <th class="py-3 px-4 text-center">Manajerial</th>
                            <th class="py-3 px-4 text-center">Sosio Kultural</th>
                            <th class="py-3 px-4 text-center">Teknis</th>
                            <th class="py-3 px-4 text-center">Total Skor</th>
                            <th class="py-3 px-4 text-center">Kelayakan</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($assessments as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-center font-mono text-gray-500">
                                    {{ $assessments->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900">
                                        {{ $item->pegawai->nama_lengkap ?? $item->pegawai->nama }}
                                    </div>
                                    <div class="text-[10px] font-mono text-gray-500">
                                        NIP. {{ $item->pegawai->nip }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-gray-800">
                                        {{ $item->tanggal_asesmen->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        {{ $item->lembaga_penyelenggara }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-gray-800 font-medium">{{ $item->metode_asesmen }}</span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono">
                                    {{ $item->skor_manajerial ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono">
                                    {{ $item->skor_sosio_kultural ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono">
                                    {{ $item->skor_teknis ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-900 text-sm">
                                    {{ $item->skor_total }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        {{ $item->kategori_kelayakan }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($item->file_laporan_url)
                                            <a href="{{ $item->file_laporan_url }}" target="_blank" class="p-1.5 text-blue-600 hover:text-blue-800 rounded bg-blue-50" title="Unduh Berkas Laporan">
                                                📄
                                            </a>
                                        @endif
                                        <a href="{{ route('manajemen-talenta.show', $item->pegawai_id) }}" class="p-1.5 text-emerald-600 hover:text-emerald-800 rounded bg-emerald-50" title="Profil Talenta">
                                            🎯
                                        </a>
                                        @if(Auth::user()->canManageTalentManagement())
                                            <form action="{{ route('manajemen-talenta.asesmen.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus data asesmen ini? Skor talenta pegawai akan dihitung ulang secara otomatis.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-rose-600 hover:text-rose-800 rounded bg-rose-50" title="Hapus Data">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-8 text-center text-gray-500">
                                    <span class="text-2xl block mb-1">📝</span>
                                    Belum ada catatan hasil uji kompetensi / asesmen.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($assessments->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $assessments->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
