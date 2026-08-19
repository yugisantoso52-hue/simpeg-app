<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Riwayat Diklat
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Manajemen Riwayat Pendidikan dan Pelatihan Pegawai
                </p>
            </div>

            <a href="{{ route('riwayat-diklat.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                + Tambah Diklat
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div id="success-alert" class="mb-5 rounded-lg border border-green-300 bg-green-50 px-5 py-4 text-green-700 flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                    <button type="button" class="text-green-700 font-bold hover:text-green-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-xl shadow p-5 border hover:shadow-lg transition">
                    <div class="text-sm text-gray-500">Total Diklat</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ $statistics['total'] }}</div>
                </div>

                <div class="bg-white rounded-xl shadow p-5 border hover:shadow-lg transition">
                    <div class="text-sm text-gray-500">Diklat Aktif</div>
                    <div class="mt-2 text-3xl font-bold text-green-600">{{ $statistics['aktif'] }}</div>
                </div>

                <div class="bg-white rounded-xl shadow p-5 border hover:shadow-lg transition">
                    <div class="text-sm text-gray-500">Tidak Aktif</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ $statistics['nonaktif'] }}</div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow border">
                <div class="p-5 border-b border-gray-100">
                    <form method="GET">
                        <div class="grid md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <input type="text" name="search" value="{{ request('search') }}"
                                       placeholder="Cari Nama Pegawai / Nama Diklat..."
                                       class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 text-sm">
                            </div>

                            <div>
                                <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">Semua Status</option>
                                    <option value="Aktif" {{ request('status')=='Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Tidak Aktif" {{ request('status')=='Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="flex gap-2">
                                <button class="px-5 py-2 bg-blue-600 rounded-lg text-white hover:bg-blue-700 text-sm font-medium transition">
                                    Cari
                                </button>
                                <a href="{{ route('riwayat-diklat.index') }}"
                                   class="px-5 py-2 bg-gray-500 rounded-lg text-white hover:bg-gray-600 text-sm font-medium transition">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700 font-semibold">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3 text-left">Pegawai</th>
                                <th class="px-4 py-3 text-left">Nama Diklat</th>
                                <th class="px-4 py-3 text-left">Penyelenggara</th>
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Sertifikat</th>
                                <th class="px-4 py-3 text-center w-40">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 text-gray-600">
                        @forelse($data as $row)
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-4 py-3 text-center font-medium">
                                    {{ $data->firstItem() + $loop->index }}
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-semibold text-gray-800">{{ $row->pegawai->nama ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->pegawai->nip ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $row->nama_diklat }}</div>
                                    @if($row->jenis_diklat)
                                        <div class="text-xs text-gray-500">{{ $row->jenis_diklat }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-3">{{ $row->penyelenggara }}</td>

                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($row->tanggal_mulai)->format('d-m-Y') }}
                                    <span class="mx-1">-</span>
                                    {{ \Carbon\Carbon::parse($row->tanggal_selesai)->format('d-m-Y') }}
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($row->status=='Aktif')
                                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Aktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-700">Tidak Aktif</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @if($row->file_sertifikat)
                                        <a href="{{ route('document.preview', ['path' => $row->file_sertifikat]) }}" target="_blank"
                                           class="inline-flex items-center rounded-md bg-sky-100 px-2.5 py-1 text-xs font-medium text-sky-700 hover:bg-sky-200 transition">
                                            📄 Lihat
                                        </a>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                {{-- Kolom Aksi Seragam --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
    <div class="flex items-center justify-center gap-2">
        <a href="{{ route('riwayat-diklat.edit', $row->id) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit
        </a>

        <form action="{{ route('riwayat-diklat.destroy', $row->id) }}" method="POST" class="inline-block m-0 p-0">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150 border-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Hapus
            </button>
        </form>
    </div>
</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-6h13M9 5v6h13M5 5h.01M5 12h.01M5 19h.01"/>
                                        </svg>
                                        <h3 class="text-lg font-semibold text-gray-700">Belum ada Riwayat Diklat</h3>
                                        <p class="text-gray-500 mt-1 text-sm">Silakan tambahkan data diklat terlebih dahulu.</p>
                                        <a href="{{ route('riwayat-diklat.create') }}"
                                           class="mt-4 inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition">
                                            + Tambah Riwayat Diklat
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t bg-gray-50 px-6 py-4">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div class="text-sm text-gray-600">
                            Menampilkan <span class="font-semibold">{{ $data->firstItem() ?? 0 }}</span> - <span class="font-semibold">{{ $data->lastItem() ?? 0 }}</span> dari <span class="font-semibold">{{ $data->total() }}</span> data.
                        </div>
                        <div>
                            {{ $data->withQueryString()->links() }}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const alertBox = document.getElementById('success-alert');
            if (alertBox) {
                setTimeout(() => {
                    alertBox.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                    setTimeout(() => { alertBox.remove(); }, 500);
                }, 3000);
            }
        });
    </script>
    @endif
</x-app-layout>