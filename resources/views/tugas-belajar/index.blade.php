<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🎓</span> Tugas Belajar & Izin Belajar (Studi Lanjut)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pengelolaan dan Monitoring Studi Lanjut S2, S3, dan Spesialis Dosen & Tendik
                </p>
            </div>

            @if(Auth::user()->hasRole('admin'))
                <a href="{{ route('tugas-belajar.create') }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-indigo-700 transition">
                    + Tambah Data Tubel / Ibel
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
                    <div class="text-xs font-semibold text-gray-500 uppercase">Total Studi Lanjut</div>
                    <div class="mt-1 text-2xl font-bold text-gray-900">{{ $statistics['total'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-blue-200 bg-blue-50/50">
                    <div class="text-xs font-semibold text-blue-700 uppercase">Sedang Studi</div>
                    <div class="mt-1 text-2xl font-bold text-blue-600">{{ $statistics['sedang_studi'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-amber-200 bg-amber-50/50">
                    <div class="text-xs font-semibold text-amber-700 uppercase">Masa Perpanjangan</div>
                    <div class="mt-1 text-2xl font-bold text-amber-600">{{ $statistics['perpanjangan'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-emerald-200 bg-emerald-50/50">
                    <div class="text-xs font-semibold text-emerald-700 uppercase">Lulus / Selesai</div>
                    <div class="mt-1 text-2xl font-bold text-emerald-600">{{ $statistics['lulus'] ?? 0 }}</div>
                </div>

                <div class="bg-white rounded-xl shadow-sm p-4 border border-purple-200 bg-purple-50/50">
                    <div class="text-xs font-semibold text-purple-700 uppercase">Studi Luar Negeri</div>
                    <div class="mt-1 text-2xl font-bold text-purple-600">{{ $statistics['luar_negeri'] ?? 0 }}</div>
                </div>
            </div>

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-5 border-b border-gray-100">
                    <form method="GET" action="{{ route('tugas-belajar.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-5">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari Nama Pegawai / Program Studi / Universitas / Sponsor..."
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            </div>

                            <div class="md:col-span-3">
                                <select name="jenjang" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Semua Jenjang Studi</option>
                                    <option value="S2" @selected(request('jenjang') == 'S2')>S2 (Magister)</option>
                                    <option value="S3" @selected(request('jenjang') == 'S3')>S3 (Doktor)</option>
                                    <option value="Spesialis" @selected(request('jenjang') == 'Spesialis')>Spesialis Keperawatan</option>
                                    <option value="Subspesialis" @selected(request('jenjang') == 'Subspesialis')>Subspesialis</option>
                                    <option value="Post Doctoral" @selected(request('jenjang') == 'Post Doctoral')>Post Doctoral</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Semua Status</option>
                                    <option value="Sedang Studi" @selected(request('status') == 'Sedang Studi')>Sedang Studi</option>
                                    <option value="Perpanjangan" @selected(request('status') == 'Perpanjangan')>Perpanjangan</option>
                                    <option value="Lulus" @selected(request('status') == 'Lulus')>Lulus</option>
                                    <option value="Dibatalkan / DO" @selected(request('status') == 'Dibatalkan / DO')>Dibatalkan / DO</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold py-2 transition">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'jenjang', 'status']))
                                    <a href="{{ route('tugas-belajar.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABEL DATA TUGAS BELAJAR --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                <th class="py-3.5 px-4">Pegawai</th>
                                <th class="py-3.5 px-4">Jenis & Jenjang</th>
                                <th class="py-3.5 px-4">Perguruan Tinggi & Prodi</th>
                                <th class="py-3.5 px-4">Sumber Beasiswa</th>
                                <th class="py-3.5 px-4 text-center">Masa Studi & Smt</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center">Berkas</th>
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
                                            <a href="{{ route('pegawai.show', $row->pegawai_id) }}" class="hover:text-indigo-600">
                                                {{ $row->pegawai->nama_lengkap ?? $row->pegawai->nama }}
                                            </a>
                                        </div>
                                        <div class="text-xs text-gray-500 font-mono">NIP. {{ $row->pegawai->nip ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold {{ $row->jenis_pengembangan === 'Tugas Belajar' ? 'bg-indigo-100 text-indigo-800' : 'bg-teal-100 text-teal-800' }}">
                                            {{ $row->jenis_pengembangan }}
                                        </span>
                                        <div class="font-bold text-gray-800 mt-1">{{ $row->jenjang_studi }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-semibold text-gray-900">{{ $row->program_studi }}</div>
                                        <div class="text-xs text-gray-600">{{ $row->perguruan_tinggi }} ({{ $row->negara }})</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-gray-800 font-medium">{{ $row->sumber_pembiayaan }}</div>
                                        @if($row->nama_sponsor)
                                            <div class="text-xs text-gray-500">Sponsor: {{ $row->nama_sponsor }}</div>
                                        @endif
                                        <div class="text-[11px] text-gray-400 font-mono">SK: {{ $row->nomor_sk }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="text-xs font-semibold text-gray-800">
                                            Semester {{ $row->semester_berjalan }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5">
                                            {{ $row->tanggal_mulai ? $row->tanggal_mulai->translatedFormat('d/m/y') : '-' }} s.d. 
                                            {{ $row->tanggal_selesai ? $row->tanggal_selesai->translatedFormat('d/m/y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border {{ $row->status_badge_class }}">
                                            {{ $row->status_studi }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            @if($row->file_sk_url)
                                                <a href="{{ $row->file_sk_url }}" target="_blank"
                                                   class="inline-flex items-center text-xs text-indigo-600 hover:text-indigo-800 font-semibold gap-1">
                                                    📄 SK Tubel
                                                </a>
                                            @endif
                                            @if($row->file_laporan_progress_url)
                                                <a href="{{ $row->file_laporan_progress_url }}" target="_blank"
                                                   class="inline-flex items-center text-xs text-teal-600 hover:text-teal-800 font-semibold gap-1">
                                                    📊 KHS/Progres
                                                </a>
                                            @endif
                                            @if(!$row->file_sk_url && !$row->file_laporan_progress_url)
                                                <span class="text-xs text-gray-400 italic">-</span>
                                            @endif
                                        </div>
                                    </td>
                                    @if(Auth::user()->hasRole('admin'))
                                        <td class="py-3 px-4 text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('tugas-belajar.edit', $row->id) }}"
                                                   class="p-1.5 text-yellow-600 hover:bg-yellow-50 rounded transition" title="Edit">
                                                    ✏️
                                                </a>
                                                <form action="{{ route('tugas-belajar.destroy', $row->id) }}" method="POST"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data studi lanjut ini?');">
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
                                    <td colspan="{{ Auth::user()->hasRole('admin') ? 9 : 8 }}" class="py-8 text-center text-gray-500 italic">
                                        Belum ada data tugas belajar atau izin belajar yang tercatat.
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
