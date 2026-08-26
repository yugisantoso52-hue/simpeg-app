<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🏖️</span> E-Cuti Pegawai
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Layanan Mandiri Pengajuan Cuti Online & Verifikasi Persetujuan Pimpinan
                </p>
            </div>

            <a href="{{ route('pengajuan-cuti.create') }}"
               class="inline-flex items-center rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                + Ajukan Cuti Baru
            </a>
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

            @if(session('error'))
                <div class="rounded-lg border border-red-300 bg-red-50 px-5 py-4 text-red-700 flex items-center justify-between shadow-sm">
                    <span>{{ session('error') }}</span>
                    <button type="button" class="text-red-700 font-bold hover:text-red-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- JIKA PEGAWAI BIASA: TAMPILKAN KARTU KUOTA CUTI TAHUNAN --}}
            @if($isPegawaiOnly && $pegawai)
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-xl shadow-md p-6 text-white flex flex-col md:flex-row items-center justify-between gap-6">
                    <div>
                        <div class="text-emerald-100 text-xs uppercase tracking-wider font-semibold">Sisa Kuota Cuti Tahunan (Tahun {{ now()->year }})</div>
                        <div class="text-3xl md:text-4xl font-extrabold mt-1">
                            {{ $pegawai->sisa_cuti_tahunan }} <span class="text-lg font-normal text-emerald-200">/ 12 Hari Kerja</span>
                        </div>
                        <p class="text-xs text-emerald-100 mt-2">
                            Setiap PNS/PPPK berhak atas 12 hari kerja cuti tahunan sesuai Peraturan BKN No. 24/2017.
                        </p>
                    </div>
                    <div class="flex items-center gap-4 border-t md:border-t-0 md:border-l border-emerald-400/40 pt-4 md:pt-0 md:ps-6 text-center">
                        <div>
                            <div class="text-xs text-emerald-200 uppercase font-semibold">Total Cuti Diajukan</div>
                            <div class="text-2xl font-bold mt-0.5">{{ $statistics['total'] ?? 0 }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-emerald-200 uppercase font-semibold">Sedang Diproses</div>
                            <div class="text-2xl font-bold mt-0.5">{{ $statistics['menunggu'] ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            @else
                {{-- STATISTIK UNTUK ADMIN & PIMPINAN --}}
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="bg-white rounded-xl shadow-sm p-4 border border-gray-200">
                        <div class="text-xs font-semibold text-gray-500 uppercase">Total Pengajuan</div>
                        <div class="mt-1 text-2xl font-bold text-gray-900">{{ $statistics['total'] ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4 border border-amber-200 bg-amber-50/50">
                        <div class="text-xs font-semibold text-amber-700 uppercase">Menunggu Persetujuan</div>
                        <div class="mt-1 text-2xl font-bold text-amber-600">{{ $statistics['menunggu'] ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4 border border-emerald-200 bg-emerald-50/50">
                        <div class="text-xs font-semibold text-emerald-700 uppercase">Disetujui</div>
                        <div class="mt-1 text-2xl font-bold text-emerald-600">{{ $statistics['disetujui'] ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4 border border-rose-200 bg-rose-50/50">
                        <div class="text-xs font-semibold text-rose-700 uppercase">Ditolak</div>
                        <div class="mt-1 text-2xl font-bold text-rose-600">{{ $statistics['ditolak'] ?? 0 }}</div>
                    </div>

                    <div class="bg-white rounded-xl shadow-sm p-4 border border-blue-200 bg-blue-50/50">
                        <div class="text-xs font-semibold text-blue-700 uppercase">Cuti Hari Ini</div>
                        <div class="mt-1 text-2xl font-bold text-blue-600">{{ $statistics['hari_ini'] ?? 0 }}</div>
                    </div>
                </div>
            @endif

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="p-5 border-b border-gray-100">
                    <form method="GET" action="{{ route('pengajuan-cuti.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-5">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari Alasan / Nomor Surat / Nama Pegawai..."
                                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                            </div>

                            <div class="md:col-span-3">
                                <select name="jenis" class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Semua Jenis Cuti</option>
                                    <option value="Cuti Tahunan" @selected(request('jenis') == 'Cuti Tahunan')>Cuti Tahunan</option>
                                    <option value="Cuti Sakit" @selected(request('jenis') == 'Cuti Sakit')>Cuti Sakit</option>
                                    <option value="Cuti Melahirkan" @selected(request('jenis') == 'Cuti Melahirkan')>Cuti Melahirkan</option>
                                    <option value="Cuti Alasan Penting" @selected(request('jenis') == 'Cuti Alasan Penting')>Cuti Alasan Penting</option>
                                    <option value="Cuti Besar" @selected(request('jenis') == 'Cuti Besar')>Cuti Besar</option>
                                    <option value="Cuti di Luar Tanggungan Negara" @selected(request('jenis') == 'Cuti di Luar Tanggungan Negara')>CLTN</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <select name="status" class="w-full rounded-lg border-gray-300 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    <option value="">Semua Status</option>
                                    <option value="Menunggu Persetujuan" @selected(request('status') == 'Menunggu Persetujuan')>Menunggu</option>
                                    <option value="Disetujui" @selected(request('status') == 'Disetujui')>Disetujui</option>
                                    <option value="Ditolak" @selected(request('status') == 'Ditolak')>Ditolak</option>
                                    <option value="Dibatalkan" @selected(request('status') == 'Dibatalkan')>Dibatalkan</option>
                                </select>
                            </div>

                            <div class="md:col-span-2 flex gap-2">
                                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold py-2 transition">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'jenis', 'status']))
                                    <a href="{{ route('pengajuan-cuti.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition">
                                        Reset
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- TABEL DATA PENGAJUAN CUTI --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">No</th>
                                @if(!$isPegawaiOnly)
                                    <th class="py-3.5 px-4">Pegawai</th>
                                @endif
                                <th class="py-3.5 px-4">Jenis Cuti</th>
                                <th class="py-3.5 px-4">Tanggal Pelaksanaan</th>
                                <th class="py-3.5 px-4 text-center">Durasi</th>
                                <th class="py-3.5 px-4">Alasan</th>
                                <th class="py-3.5 px-4 text-center">Status</th>
                                <th class="py-3.5 px-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($data as $row)
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="py-3 px-4 text-center text-gray-400 font-mono">
                                        {{ ($data->currentPage() - 1) * $data->perPage() + $loop->iteration }}
                                    </td>
                                    @if(!$isPegawaiOnly)
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-gray-900">
                                                <a href="{{ route('pegawai.show', $row->pegawai_id) }}" class="hover:text-emerald-600">
                                                    {{ $row->pegawai->nama_lengkap ?? $row->pegawai->nama }}
                                                </a>
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono">NIP. {{ $row->pegawai->nip ?? '-' }}</div>
                                        </td>
                                    @endif
                                    <td class="py-3 px-4 font-semibold text-gray-800">
                                        {{ $row->jenis_cuti }}
                                        @if($row->nomor_surat)
                                            <div class="text-[11px] text-gray-500 font-mono">No: {{ $row->nomor_surat }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-xs font-semibold text-gray-900">
                                            {{ $row->tanggal_mulai ? $row->tanggal_mulai->translatedFormat('d M Y') : '-' }} s.d.
                                        </div>
                                        <div class="text-xs text-gray-700">
                                            {{ $row->tanggal_selesai ? $row->tanggal_selesai->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-block px-2 py-1 rounded bg-gray-100 text-gray-800 text-xs font-bold font-mono">
                                            {{ $row->jumlah_hari }} Hari
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 max-w-xs truncate" title="{{ $row->alasan }}">
                                        {{ $row->alasan }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border {{ $row->status_badge_class }}">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <div class="flex items-center justify-center gap-1.5">
                                            <a href="{{ route('pengajuan-cuti.show', $row->id) }}"
                                               class="p-1.5 text-blue-600 hover:bg-blue-50 rounded transition" title="Lihat Detail & Persetujuan">
                                                🔍
                                            </a>
                                            <a href="{{ route('pengajuan-cuti.cetak-pdf', $row->id) }}" target="_blank"
                                               class="p-1.5 text-emerald-600 hover:bg-emerald-50 rounded transition" title="Cetak Formulir Cuti BKN">
                                                📄
                                            </a>
                                            @if(Auth::user()->hasRole('admin'))
                                                <form action="{{ route('pengajuan-cuti.destroy', $row->id) }}" method="POST"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data cuti ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded transition" title="Hapus">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isPegawaiOnly ? 7 : 8 }}" class="py-8 text-center text-gray-500 italic">
                                        Belum ada riwayat permohonan cuti.
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
