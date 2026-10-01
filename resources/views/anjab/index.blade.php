<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <span>📑</span> Analisis Jabatan (E-Anjab)
                </h1>
                <p class="text-sm text-gray-600">
                    Katalog dan Instrumen 17 Butir Informasi Jabatan sesuai PermenPAN-RB No. 1 Tahun 2020 & Peraturan BKN No. 12 Tahun 2011
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow hover:bg-indigo-700 transition">
                    <span>📊</span> Peta Jabatan
                </a>
                <a href="{{ route('abk.index') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 transition">
                    <span>🧮</span> Rekap ABK & Formasi
                </a>
                <a href="{{ route('anjab.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow hover:bg-emerald-700 transition">
                    <span>➕</span> Tambah Anjab Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✅</span>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800">✕</button>
                </div>
            @endif

            {{-- Filter & Pencarian --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <form method="GET" action="{{ route('anjab.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Cari Jabatan / Kode</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama jabatan atau kode anjab..."
                               class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Filter Unit Kerja</label>
                        <select name="unit_kerja_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Semua Unit Kerja --</option>
                            @foreach($unitKerjas as $u)
                                <option value="{{ $u->id }}" {{ request('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->kode_unit }} - {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-gray-800 rounded-lg hover:bg-gray-900 transition">
                            🔍 Filter
                        </button>
                        <a href="{{ route('anjab.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            {{-- Tabel Daftar Dokumen Anjab --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-50 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-4">No</th>
                                <th class="px-6 py-4">Nama Jabatan & Kode</th>
                                <th class="px-6 py-4">Unit Kerja</th>
                                <th class="px-6 py-4 text-center">Kelas (Grade)</th>
                                <th class="px-6 py-4 text-center">Butir Tugas ABK</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($anjabs as $index => $item)
                                <tr class="hover:bg-blue-50/50 transition">
                                    <td class="px-6 py-4 font-medium text-gray-500">
                                        {{ $anjabs->firstItem() + $index }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-bold text-gray-900">{{ $item->jabatan->nama_jabatan ?? '-' }}</div>
                                        <div class="text-xs text-blue-600 font-mono font-medium">{{ $item->kode_anjab ?? $item->jabatan->kode_jabatan ?? 'DRAFT' }}</div>
                                        <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">{{ $item->ikhtisar_jabatan ?? 'Belum ada ikhtisar jabatan.' }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $item->unitKerja->nama_unit ?? 'Unit Kerja Fakultas' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            Grade {{ $item->kelas_jabatan ?? $item->jabatan->kelas_jabatan ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('abk.edit', $item) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
                                            <span>📋</span> {{ $item->uraianTugas->count() }} Tugas
                                            <span class="text-gray-400">|</span>
                                            <span>{{ $item->total_jam_beban }} Jam</span>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($item->status === 'disetujui')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                ✓ Disetujui
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                                Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-1 whitespace-nowrap">
                                        <a href="{{ route('anjab.show', $item) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 transition" title="Lihat 17 Elemen">
                                            👁️ Detail
                                        </a>
                                        <a href="{{ route('abk.edit', $item) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 transition" title="Kelola Beban Kerja ABK">
                                            🧮 ABK
                                        </a>
                                        <a href="{{ route('anjab.print', $item) }}" target="_blank" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition" title="Cetak Standar PermenPAN-RB">
                                            🖨️ Cetak
                                        </a>
                                        <a href="{{ route('anjab.edit', $item) }}" class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded-md text-amber-700 bg-amber-50 border border-amber-200 hover:bg-amber-100 transition" title="Edit">
                                            ✏️
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <p class="text-base font-semibold text-gray-700">Belum ada dokumen Analisis Jabatan.</p>
                                        <p class="text-xs text-gray-500 mt-1">Silakan klik "Tambah Anjab Baru" untuk mulai menyusun form 17 butir jabatan.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($anjabs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $anjabs->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
