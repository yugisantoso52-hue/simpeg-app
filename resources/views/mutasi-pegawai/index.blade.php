<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Mutasi Pegawai
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded-lg p-6">

                @if(session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button type="button" class="text-green-700 font-bold hover:text-green-900" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                        <button type="button" class="text-red-700 font-bold hover:text-red-900" onclick="this.parentElement.remove()">×</button>
                    </div>
                @endif

                <div class="mb-4">
                    <a href="{{ route('mutasi-pegawai.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Mutasi
                    </a>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700 font-semibold uppercase tracking-wider">
                            <tr>
                                <th class="border-b px-4 py-3 text-center w-16">No</th>
                                <th class="border-b px-4 py-3 text-left">Pegawai</th>
                                <th class="border-b px-4 py-3 text-left">Unit Lama</th>
                                <th class="border-b px-4 py-3 text-left">Unit Baru</th>
                                <th class="border-b px-4 py-3 text-left">TMT</th>
                                <th class="border-b px-4 py-3 text-left">Nomor SK</th>
                                <th class="border-b px-4 py-3 text-center w-40">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200 text-gray-600">
                        @forelse($data as $row)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-center font-medium">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-4 py-3 font-semibold text-gray-800">
                                    {{ $row->pegawai->nama_lengkap ?? $row->pegawai->nama ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $row->unitLama->nama_unit ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $row->unitBaru->nama_unit ?? '-' }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $row->tmt }}
                                </td>

                                <td class="px-4 py-3">
                                    {{ $row->nomor_sk }}
                                </td>

                                {{-- Kolom Aksi Seragam --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap align-middle">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('mutasi-pegawai.edit', $row->id) }}"
                                           class="inline-flex items-center px-2.5 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded shadow-sm transition-colors">
                                            ✏️ Edit
                                        </a>

                                        <form action="{{ route('mutasi-pegawai.destroy', $row->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus riwayat mutasi ini?')"
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded shadow-sm transition-colors border-0 cursor-pointer">
                                                🗑 Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-gray-500">
                                    Belum ada data mutasi
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $data->links() }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>