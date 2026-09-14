<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📊</span> Pengarsipan SKP (Sasaran Kinerja Pegawai)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Dokumentasi Rencana SKP Awal Tahun & Evaluasi / Penilaian Akhir Kinerja Tahunan
                </p>
            </div>

            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('riwayat-skp.create') }}"
                   class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                    + Tambah Arsip SKP
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-lg border border-green-300 bg-green-50 px-5 py-4 text-green-700 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-green-700 font-bold hover:text-green-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- KARTU STATISTIK --}}
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                    <div class="text-xs font-semibold text-gray-500 uppercase">Total Arsip SKP</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ $statistics['total'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-blue-200 bg-blue-50/50">
                    <div class="text-xs font-semibold text-blue-700 uppercase">SKP Tahun Ini ({{ now()->year }})</div>
                    <div class="mt-1 text-2xl font-bold text-blue-600">{{ $statistics['tahun_n'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-indigo-200 bg-indigo-50/50">
                    <div class="text-xs font-semibold text-indigo-700 uppercase">SKP 1 Thn Lalu ({{ now()->year - 1 }})</div>
                    <div class="mt-1 text-2xl font-bold text-indigo-600">{{ $statistics['tahun_n1'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-emerald-200 bg-emerald-50/50">
                    <div class="text-xs font-semibold text-emerald-700 uppercase">Predikat Sangat Baik</div>
                    <div class="mt-1 text-2xl font-bold text-emerald-600">{{ $statistics['sangat_baik'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-teal-200 bg-teal-50/50">
                    <div class="text-xs font-semibold text-teal-700 uppercase">Berkas Lengkap (2 File)</div>
                    <div class="mt-1 text-2xl font-bold text-teal-600">{{ $statistics['berkas_lengkap'] ?? 0 }}</div>
                </div>
            </div>

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-5 border-b border-gray-100">
                    <form method="GET" action="{{ route('riwayat-skp.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-5">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari Nama Pegawai / NIP / Pejabat Penilai..."
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div class="md:col-span-3">
                                <select name="tahun" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Semua Tahun</option>
                                    @for($y = now()->year + 1; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" @selected(request('tahun') == $y)>Tahun {{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <select name="predikat" class="w-full rounded-lg border-gray-300 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Semua Predikat</option>
                                    <option value="Sangat Baik" @selected(request('predikat') == 'Sangat Baik')>Sangat Baik</option>
                                    <option value="Baik" @selected(request('predikat') == 'Baik')>Baik</option>
                                    <option value="Butuh Perbaikan" @selected(request('predikat') == 'Butuh Perbaikan')>Butuh Perbaikan</option>
                                    <option value="Kurang" @selected(request('predikat') == 'Kurang')>Kurang</option>
                                    <option value="Sangat Kurang" @selected(request('predikat') == 'Sangat Kurang')>Sangat Kurang</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold py-2 transition">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'tahun', 'predikat']))
                                    <a href="{{ route('riwayat-skp.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABEL DATA SKP --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                <th class="py-3.5 px-4">Pegawai</th>
                                <th class="py-3.5 px-4 text-center">Tahun SKP</th>
                                <th class="py-3.5 px-4 text-center">Predikat Kinerja</th>
                                <th class="py-3.5 px-4">Pejabat Penilai</th>
                                <th class="py-3.5 px-4 text-center">Dokumen Rencana</th>
                                <th class="py-3.5 px-4 text-center">Dokumen Evaluasi</th>
                                @if(Auth::user()->hasRole('admin'))
                                    <th class="py-3.5 px-4 text-center w-28">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($data as $row)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="py-3 px-4 text-center text-gray-400 font-mono">
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-gray-900">
                                            <a href="{{ route('pegawai.show', $row->pegawai_id) }}" class="hover:text-blue-600">
                                                {{ $row->pegawai->nama_lengkap ?? $row->pegawai->nama }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">NIP. {{ $row->pegawai->nip ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-gray-900 text-base">
                                        <span class="inline-block px-2.5 py-1 rounded-md {{ $row->tahun == now()->year ? 'bg-blue-100 text-blue-800' : ($row->tahun == now()->year - 1 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ $row->tahun }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row->predikat_kinerja)
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border {{ $row->predikat_badge_class }}">
                                                {{ $row->predikat_kinerja }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum Dinilai</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-gray-800">{{ $row->pejabat_penilai ?: '-' }}</div>
                                        @if($row->keterangan)
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $row->keterangan }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row->file_rencana_skp_url)
                                            <a href="{{ $row->file_rencana_skp_url }}" target="_blank"
                                               class="inline-flex items-center text-xs text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 font-semibold px-2.5 py-1 rounded-lg gap-1 transition">
                                                📄 Rencana SKP
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        @if($row->file_evaluasi_skp_url)
                                            <a href="{{ $row->file_evaluasi_skp_url }}" target="_blank"
                                               class="inline-flex items-center text-xs text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 font-semibold px-2.5 py-1 rounded-lg gap-1 transition">
                                                📑 Evaluasi SKP
                                            </a>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Belum ada</span>
                                        @endif
                                    </td>
                                    @if(Auth::user()->hasRole('admin'))
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('riwayat-skp.edit', $row->id) }}"
                                                   class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded transition" title="Edit">
                                                    ✏️
                                                </a>
                                                <form action="{{ route('riwayat-skp.destroy', $row->id) }}" method="POST"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data SKP tahun {{ $row->tahun }} ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="Hapus">
                                                        🗑️
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ Auth::user()->hasRole('admin') ? 8 : 7 }}" class="py-8 text-center text-gray-500 italic">
                                        Belum ada dokumen arsip SKP yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($data->hasPages())
                    <div class="p-4 border-t border-gray-100">
                        {{ $data->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
