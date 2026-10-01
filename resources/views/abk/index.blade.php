<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <span>🧮</span> Analisis Beban Kerja & Formasi Pegawai
                </h1>
                <p class="text-sm text-gray-600">
                    Kalkulasi Kebutuhan Formasi Pegawai Berdasarkan Standar Waktu Kerja Efektif (WKE: 1.250 Jam / 75.000 Menit/Tahun)
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow hover:bg-indigo-700 transition">
                    <span>📊</span> Peta Jabatan
                </a>
                <a href="{{ route('abk.print.rekap', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow hover:bg-emerald-700 transition">
                    <span>🖨️</span> Cetak Rekap Formasi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- 4 Kartu Statistik Agregat Formasi --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kebutuhan Formasi (ABK)</div>
                        <div class="text-3xl font-black text-blue-600 mt-1">{{ $totalKebutuhan }} <span class="text-sm font-normal text-gray-500">Pegawai</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        📋
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Bezetting (Pegawai Riil)</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">{{ $totalBezetting }} <span class="text-sm font-normal text-gray-500">Pegawai</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gray-50 text-gray-700 flex items-center justify-center text-xl font-bold">
                        👥
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Kekurangan Pegawai</div>
                        <div class="text-3xl font-black text-rose-600 mt-1">{{ $totalKurang }} <span class="text-sm font-normal text-rose-400">Posisi</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        ⚠️
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Kelebihan Pegawai</div>
                        <div class="text-3xl font-black text-amber-600 mt-1">{{ $totalLebih }} <span class="text-sm font-normal text-amber-400">Posisi</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        ℹ️
                    </div>
                </div>
            </div>

            {{-- Filter Unit Kerja --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <form method="GET" action="{{ route('abk.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-1 max-w-md">
                        <label class="text-xs font-bold text-gray-600 uppercase whitespace-nowrap">Filter Unit Kerja:</label>
                        <select name="unit_kerja_id" onchange="this.form.submit()" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Seluruh Unit Kerja Fakultas --</option>
                            @foreach($unitKerjas as $u)
                                <option value="{{ $u->id }}" {{ request('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->kode_unit }} - {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('unit_kerja_id'))
                        <a href="{{ route('abk.index') }}" class="text-xs text-blue-600 hover:underline">
                            ✕ Hapus Filter
                        </a>
                    @endif
                </form>
            </div>

            {{-- Tabel Matrix Formasi ABK --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h2 class="font-bold text-gray-800 text-base">TABEL PERHITUNGAN FORMASI & BEBAN KERJA JABATAN</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Rumus: Kebutuhan Pegawai = Total Waktu Beban (Menit) / 75.000 Menit (1.250 Jam WKE)</p>
                    </div>
                    <span class="text-xs font-bold text-blue-700 bg-blue-100 px-3 py-1 rounded-full">
                        {{ $anjabs->count() }} Jabatan Terdata
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-100 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase">
                            <tr>
                                <th class="px-4 py-3.5 text-center">No</th>
                                <th class="px-4 py-3.5">Nama Jabatan & Unit Kerja</th>
                                <th class="px-4 py-3.5 text-center">Grade</th>
                                <th class="px-4 py-3.5 text-center">Butir Tugas</th>
                                <th class="px-4 py-3.5 text-center">Total JKE (Jam)</th>
                                <th class="px-4 py-3.5 text-center">Kebutuhan (ABK)</th>
                                <th class="px-4 py-3.5 text-center bg-blue-50/60 text-blue-900">Formasi</th>
                                <th class="px-4 py-3.5 text-center bg-gray-50 text-gray-900">Bezetting</th>
                                <th class="px-4 py-3.5 text-center">Selisih</th>
                                <th class="px-4 py-3.5 text-center">Status Formasi</th>
                                <th class="px-4 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($anjabs as $i => $item)
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-4 py-3 text-center font-medium text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold text-gray-900">{{ $item->jabatan->nama_jabatan ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-amber-50 text-amber-800 border border-amber-200">
                                            {{ $item->kelas_jabatan ?? $item->jabatan->kelas_jabatan ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-700">
                                        {{ $item->uraianTugas->count() }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-medium text-gray-800">
                                        {{ number_format($item->total_jam_beban, 1) }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-semibold text-blue-700">
                                        {{ $item->kebutuhan_pegawai }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-black text-blue-900 bg-blue-50/60 text-base">
                                        {{ $item->formasi_pembulatan }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-black text-gray-900 bg-gray-50 text-base">
                                        {{ $item->bezetting }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono font-bold {{ $item->selisih_formasi < 0 ? 'text-rose-600' : ($item->selisih_formasi > 0 ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $item->selisih_formasi > 0 ? '+' : '' }}{{ $item->selisih_formasi }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($item->selisih_formasi < 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">
                                                🔴 Kurang {{ abs($item->selisih_formasi) }}
                                            </span>
                                        @elseif($item->selisih_formasi > 0)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                                🟡 Lebih +{{ $item->selisih_formasi }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                🟢 Ideal (Cukup)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right space-x-1 whitespace-nowrap">
                                        <a href="{{ route('abk.edit', $item) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition" title="Kelola Butir Tugas ABK">
                                            ✏️ Tugas
                                        </a>
                                        <a href="{{ route('anjab.show', $item) }}" class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 transition" title="Detail Anjab">
                                            👁️
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                                        Belum ada data jabatan untuk perhitungan ABK.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($anjabs->isNotEmpty())
                            <tfoot class="bg-gray-100 border-t-2 border-gray-300 font-bold text-gray-900 text-xs">
                                <tr>
                                    <td colspan="6" class="px-4 py-3 text-right uppercase">TOTAL KESELURUHAN:</td>
                                    <td class="px-4 py-3 text-center font-mono font-black text-blue-900 text-sm bg-blue-100">{{ $totalKebutuhan }}</td>
                                    <td class="px-4 py-3 text-center font-mono font-black text-gray-900 text-sm bg-gray-200">{{ $totalBezetting }}</td>
                                    <td class="px-4 py-3 text-center font-mono font-bold {{ ($totalBezetting - $totalKebutuhan) < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ ($totalBezetting - $totalKebutuhan) > 0 ? '+' : '' }}{{ $totalBezetting - $totalKebutuhan }}
                                    </td>
                                    <td colspan="2" class="px-4 py-3"></td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
