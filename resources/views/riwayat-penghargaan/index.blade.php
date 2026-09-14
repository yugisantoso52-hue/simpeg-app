<x-app-layout>
    <x-slot name="header">
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">🏅 Riwayat Penghargaan & Tanda Jasa</h1>
                <p class="mt-1 text-sm text-gray-500">Data penghargaan, satyalancana, dan tanda jasa seluruh pegawai.</p>
            </div>
            @if(Auth::user()->hasRole('admin'))
            <a href="{{ route('riwayat-penghargaan.create') }}"
               class="mt-4 sm:mt-0 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                + Tambah Penghargaan
            </a>
            @endif
        </div>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        {{-- Search --}}
        <form method="GET" class="mb-4 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama penghargaan atau nama pegawai..."
                   class="block w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <button type="submit" class="rounded-lg bg-gray-700 px-4 py-2 text-sm text-white hover:bg-gray-600">Cari</button>
        </form>

        @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Pegawai</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Nama Penghargaan</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Jenis</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Instansi Pemberi</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tanggal</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($data as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('pegawai.show', $item->pegawai_id) }}" class="font-medium text-indigo-600 hover:underline">
                                {{ $item->pegawai->nama ?? '-' }}
                            </a>
                            <div class="text-xs text-gray-400">{{ $item->pegawai->nip ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->nama_penghargaan }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $item->jenis_penghargaan ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $item->instansi_pemberi ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $item->tanggal_terima ? $item->tanggal_terima->format('d/m/Y') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if(Auth::user()->hasRole('admin'))
                                <a href="{{ route('riwayat-penghargaan.edit', $item->id) }}"
                                   class="text-xs text-indigo-600 hover:underline">Edit</a>
                                <form method="POST" action="{{ route('riwayat-penghargaan.destroy', $item->id) }}"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada data penghargaan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $data->withQueryString()->links() }}</div>
    </div>
</x-app-layout>
