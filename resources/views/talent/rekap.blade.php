<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.index', ['tahun' => $tahun]) }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
                        &larr; Kembali ke Matriks 9-Kotak
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>📋</span> Rekap Data Pemetaan Talenta ASN
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Daftar komprehensif seluruh ASN Fakultas Keperawatan UNRI beserta evaluasi Sumbu Kinerja, Sumbu Potensi, dan Status Validasi
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('manajemen-talenta.index', ['tahun' => $tahun]) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>📊</span> Tampilan Matriks 9-Box
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- Filter Bar --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5">
            <form method="GET" action="{{ route('manajemen-talenta.rekap') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                <div>
                    <label for="tahun" class="block text-xs font-bold text-gray-700 mb-1">Tahun Evaluasi</label>
                    <select name="tahun" id="tahun" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                        @foreach($availableYears as $y)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="unit_kerja_id" class="block text-xs font-bold text-gray-700 mb-1">Unit Kerja / Bagian</label>
                    <select name="unit_kerja_id" id="unit_kerja_id" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                        <option value="">Semua Unit Kerja</option>
                        @foreach($unitKerjas as $uk)
                            <option value="{{ $uk->id }}" {{ $unitKerjaId == $uk->id ? 'selected' : '' }}>{{ $uk->nama_unit }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="kuadran" class="block text-xs font-bold text-gray-700 mb-1">Kuadran 9-Kotak</label>
                    <select name="kuadran" id="kuadran" class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                        <option value="">Semua Kuadran (1-9)</option>
                        <option value="9" {{ $kuadran == '9' ? 'selected' : '' }}>Kotak IX (Talenta Unggul / Star)</option>
                        <option value="8" {{ $kuadran == '8' ? 'selected' : '' }}>Kotak VIII (Kinerja Tinggi, Potensi Sedang)</option>
                        <option value="7" {{ $kuadran == '7' ? 'selected' : '' }}>Kotak VII (Kinerja Tinggi, Potensi Rendah)</option>
                        <option value="6" {{ $kuadran == '6' ? 'selected' : '' }}>Kotak VI (Kinerja Sedang, Potensi Tinggi)</option>
                        <option value="5" {{ $kuadran == '5' ? 'selected' : '' }}>Kotak V (Pekerja Inti / Core)</option>
                        <option value="4" {{ $kuadran == '4' ? 'selected' : '' }}>Kotak IV (Pekerja Efektif)</option>
                        <option value="3" {{ $kuadran == '3' ? 'selected' : '' }}>Kotak III (Kurang Selaras)</option>
                        <option value="2" {{ $kuadran == '2' ? 'selected' : '' }}>Kotak II (Kurang Efektif)</option>
                        <option value="1" {{ $kuadran == '1' ? 'selected' : '' }}>Kotak I (Pembinaan Khusus)</option>
                    </select>
                </div>

                <div>
                    <label for="search" class="block text-xs font-bold text-gray-700 mb-1">Cari ASN (Nama / NIP)</label>
                    <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama atau NIP..." class="w-full text-xs rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 py-2">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-xs">
                        🔍 Filter Data
                    </button>
                    <a href="{{ route('manajemen-talenta.rekap', ['tahun' => $tahun]) }}" class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition" title="Reset Filter">
                        🔄
                    </a>
                </div>
            </form>

            <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between">
                <label class="inline-flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="only_suksesi" value="1" {{ $onlySuksesi ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <span>Hanya Tampilkan Talent Pool Suksesi (Kotak 9 &amp; 8)</span>
                </label>
                <span class="text-xs text-gray-500">Ditemukan <b>{{ $mappings->total() }}</b> pegawai</span>
            </div>
        </div>

        {{-- Tabel Rekap Data --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-gray-200 text-slate-700 font-extrabold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Pegawai</th>
                            <th class="py-3 px-4">Jabatan &amp; Unit</th>
                            <th class="py-3 px-4 text-center">Kinerja (X)</th>
                            <th class="py-3 px-4 text-center">Potensi (Y)</th>
                            <th class="py-3 px-4 text-center">Kuadran Box</th>
                            <th class="py-3 px-4 text-center">Talent Pool</th>
                            <th class="py-3 px-4 text-center">Validasi Komite</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($mappings as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-center font-mono text-gray-500">
                                    {{ $mappings->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-gray-900">
                                        {{ $item->pegawai->nama_lengkap ?? $item->pegawai->nama }}
                                    </div>
                                    <div class="text-[10px] font-mono text-gray-500">
                                        NIP. {{ $item->pegawai->nip }} &bull; Gol. {{ $item->pegawai->golongan->kode ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-gray-800">
                                        {{ $item->pegawai->jabatan->nama_jabatan ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">
                                        {{ $item->pegawai->unitKerja->nama_unit ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="font-mono font-bold text-gray-900 block text-xs">
                                        {{ $item->sumbu_kinerja_nilai }}
                                    </span>
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[10px] font-semibold {{ $item->sumbu_kinerja_kategori === 'Tinggi' ? 'bg-emerald-100 text-emerald-800' : ($item->sumbu_kinerja_kategori === 'Sedang' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $item->sumbu_kinerja_kategori }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="font-mono font-bold text-gray-900 block text-xs">
                                        {{ $item->sumbu_potensi_nilai }}
                                    </span>
                                    <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full text-[10px] font-semibold {{ $item->sumbu_potensi_kategori === 'Tinggi' ? 'bg-indigo-100 text-indigo-800' : ($item->sumbu_potensi_kategori === 'Sedang' ? 'bg-blue-100 text-blue-800' : 'bg-rose-100 text-rose-800') }}">
                                        {{ $item->sumbu_potensi_kategori }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black shadow-2xs {{ $item->box_badge_color }}">
                                        Kotak {{ $item->kuadran_box }}
                                    </span>
                                    <span class="block text-[10px] text-gray-500 mt-1">
                                        {{ $item->status_talenta }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($item->is_suksesi_eligible)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            <span>⭐</span> Prioritas
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold {{ $item->status_validasi === 'Ditetapkan PPK' ? 'bg-emerald-100 text-emerald-800' : ($item->status_validasi === 'Ditinjau Komite' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-700') }}">
                                        {{ $item->status_validasi }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('manajemen-talenta.show', ['pegawai' => $item->pegawai_id, 'tahun' => $tahun]) }}" class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                                        <span>👁️</span> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-8 text-center text-gray-500">
                                    <span class="text-2xl block mb-1">🔍</span>
                                    Tidak ditemukan data pemetaan talenta yang sesuai filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($mappings->hasPages())
                <div class="p-4 border-t border-gray-200">
                    {{ $mappings->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
