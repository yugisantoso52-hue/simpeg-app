<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Master Jabatan"
            subtitle="Kelola katalog master jabatan, pemetaan unit kerja, kelas jabatan (grade), dan kelompok jabatan sesuai struktur FKp UNRI">
            <div class="flex items-center gap-2">
                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-indigo-700 transition">
                    <span>📊</span> Peta Jabatan
                </a>
                <a href="{{ route('jabatan.create') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow hover:bg-blue-700 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Jabatan Baru
                </a>
            </div>
        </x-enterprise.page-header>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>✅</span>
                        <span class="font-medium text-sm">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 font-bold">✕</button>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span>
                        <span class="font-medium text-sm">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800 font-bold">✕</button>
                </div>
            @endif

            {{-- Filter & Pencarian Cepat --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <form method="GET" action="{{ route('jabatan.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Cari Jabatan / Kode</label>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama jabatan atau kode..."
                               class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Kelompok Jabatan</label>
                        <select name="kelompok_jabatan" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Semua Kelompok --</option>
                            @foreach($kelompokOptions as $kel)
                                <option value="{{ $kel }}" {{ request('kelompok_jabatan') == $kel ? 'selected' : '' }}>
                                    {{ $kel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Unit Kerja Terhubung</label>
                        <select name="unit_kerja_id" onchange="this.form.submit()" class="w-full text-xs rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Semua Unit Kerja --</option>
                            @foreach($unitKerjas as $u)
                                <option value="{{ $u->id }}" {{ request('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            Filter
                        </button>
                        @if(request()->hasAny(['search', 'kelompok_jabatan', 'unit_kerja_id']))
                            <a href="{{ route('jabatan.index') }}" class="px-3 py-2 text-xs font-semibold text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Card Tabel --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200 text-gray-700 font-bold uppercase tracking-wider">
                                    <th class="w-12 px-3 py-3 text-center">No</th>
                                    <th class="px-4 py-3">Nama Jabatan</th>
                                    <th class="px-4 py-3">Kelompok Jabatan</th>
                                    <th class="px-4 py-3">Unit Kerja Terhubung</th>
                                    <th class="px-3 py-3 text-center">Grade</th>
                                    <th class="px-3 py-3 text-center">Pegawai</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($jabatan as $row)
                                    <tr class="hover:bg-blue-50/40 transition">
                                        <td class="px-3 py-3 text-center font-medium text-gray-500">
                                            @if(method_exists($jabatan, 'currentPage'))
                                                {{ ($jabatan->currentPage() - 1) * $jabatan->perPage() + $loop->iteration }}
                                            @else
                                                {{ $loop->iteration }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-bold text-gray-900 text-sm">{{ $row->nama_jabatan }}</div>
                                            <div class="text-[11px] text-gray-500 font-mono">{{ $row->kode_jabatan }}</div>
                                            @if($row->ikhtisar_jabatan)
                                                <div class="text-[11px] text-gray-500 line-clamp-1 italic mt-0.5">{{ $row->ikhtisar_jabatan }}</div>
                                            @endif
                                            @if($row->analisisJabatan)
                                                <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                                                    <a href="{{ route('abk.edit', $row->analisisJabatan->id) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition" title="Kelola Butir Tugas ABK">
                                                        <span>🧮 ABK: {{ $row->analisisJabatan->formasi_pembulatan }} Formasi</span>
                                                    </a>
                                                    <a href="{{ route('anjab.show', $row->analisisJabatan->id) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition" title="Lihat Dokumen 17 Butir Anjab">
                                                        <span>📑 {{ $row->analisisJabatan->kode_anjab ?? 'E-Anjab' }}</span>
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-block px-2.5 py-0.5 rounded-full font-bold text-[10px] bg-slate-100 text-slate-800 border border-slate-200">
                                                {{ $row->kelompok_jabatan ?? 'Umum' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 font-semibold text-gray-800">
                                            @if($row->unitKerja)
                                                <span class="text-blue-700">{{ $row->unitKerja->nama_unit }}</span>
                                            @else
                                                <span class="text-gray-400 italic">Belum terhubung</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            @if($row->kelas_jabatan)
                                                <span class="px-2 py-0.5 font-black text-xs rounded bg-amber-100 text-amber-900 border border-amber-300">
                                                    {{ $row->kelas_jabatan }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <span class="px-2 py-0.5 font-bold rounded-full text-xs {{ $row->pegawai_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                                {{ $row->pegawai_count }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center whitespace-nowrap">
                                            <div class="flex items-center justify-center gap-1.5">
                                                @if($row->analisisJabatan)
                                                    <a href="{{ route('abk.edit', $row->analisisJabatan->id) }}"
                                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded shadow-2xs transition"
                                                       title="Kelola Beban Kerja ABK">
                                                        🧮 ABK
                                                    </a>
                                                @endif
                                                <a href="{{ route('jabatan.edit', $row->id) }}"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded shadow-2xs transition">
                                                    ✏️ Edit
                                                </a>

                                                <form action="{{ route('jabatan.destroy', $row->id) }}" method="POST" class="inline-block m-0 p-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jabatan ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded shadow-2xs transition border-0 cursor-pointer">
                                                        🗑️ Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-8 text-gray-500 italic">
                                            Tidak ditemukan data jabatan sesuai kriteria filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($jabatan, 'hasPages') && $jabatan->hasPages())
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            {{ $jabatan->links() }}
                        </div>
                    @endif
                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>