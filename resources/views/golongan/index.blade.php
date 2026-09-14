<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Golongan') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    {{-- Pesan Sukses --}}
                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg border border-green-200 flex items-center justify-between">
                            <span>{{ session('success') }}</span>
                            <button type="button" class="text-green-700 font-bold hover:text-green-900" onclick="this.parentElement.remove()">×</button>
                        </div>
                    @endif

                    {{-- Baris Tombol Tambah & Form Cari --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                        <a href="{{ route('golongan.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 transition duration-150 ease-in-out">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Golongan
                        </a>

                        <form action="{{ route('golongan.index') }}" method="GET" class="flex w-full md:w-auto gap-2">
                            <div class="relative flex-grow">
                                <input type="text"
                                       name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Cari Golongan/Pangkat..."
                                       class="w-full md:w-64 border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 px-3 py-2 text-sm text-gray-900">
                                
                                @if(request('search'))
                                    <a href="{{ route('golongan.index') }}" class="absolute right-2 top-2.5 text-gray-400 hover:text-gray-600">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                @endif
                            </div>

                            <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded-md text-sm font-medium hover:bg-gray-800 transition duration-150">
                                Cari
                            </button>
                        </form>
                    </div>

                    {{-- Tabel Data --}}
                    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50 text-gray-700 font-semibold uppercase tracking-wider">
                                <tr>
                                    <th class="border-b px-4 py-3 text-center w-16">No</th>
                                    <th class="border-b px-4 py-3 text-left w-32">Nama Golongan</th>
                                    <th class="border-b px-4 py-3 text-left">Nama Pangkat</th>
                                    <th class="border-b px-4 py-3 text-left">Keterangan</th>
                                    <th class="border-b px-4 py-3 text-center w-40">Aksi</th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-200 text-gray-600">
                            @forelse($golongan as $row)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-4 py-3 text-center font-medium">
                                        @if(method_exists($golongan, 'currentPage'))
                                            {{ ($golongan->currentPage() - 1) * $golongan->perPage() + $loop->iteration }}
                                        @else
                                            {{ $loop->iteration }}
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-gray-700">
                                        {{ $row->nama_golongan }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-normal font-medium text-gray-900">
                                        {{ $row->nama_pangkat ?? '-' }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-normal">
                                        {{ $row->keterangan ?? '-' }}
                                    </td>

                                    {{-- Kolom Aksi Seragam --}}
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
    <div class="flex items-center justify-center gap-2">
        <a href="{{ route('golongan.edit', $row->id) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit
        </a>

        <form action="{{ route('golongan.destroy', $row->id) }}" method="POST" class="inline-block m-0 p-0">
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
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span class="text-base font-medium">Belum ada data golongan</span>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Link Pagination --}}
                    @if(method_exists($golongan, 'links'))
                        <div class="mt-4">
                            {{ $golongan->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>