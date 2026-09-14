<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Master Unit Kerja"
            subtitle="Kelola data master unit kerja pegawai">
            <a href="{{ route('unit-kerja.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Unit Kerja
            </a>
        </x-enterprise.page-header>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Message --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="font-medium text-green-700">{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 hover:text-green-900">✕</button>
                    </div>
                </div>
            @endif

            {{-- Card Tabel Utama --}}
            <x-enterprise.card>
                <div class="p-6">
                    <x-enterprise.toolbar
                        :create="route('unit-kerja.create')"
                        :searchAction="route('unit-kerja.index')"
                        :searchValue="request('search')"
                        placeholder="Cari Nama Unit Kerja..."
                        createLabel="Tambah Unit Kerja"
                    />

                    <div class="mt-6">
                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-16 px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3 text-left">Nama Unit</th>
                                    <th class="px-4 py-3 text-left">Keterangan</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">Aksi</th>
                                </tr>
                            </x-slot>

                            @forelse($unitKerja as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-medium">
                                        @if(method_exists($unitKerja, 'currentPage'))
                                             {{ ($unitKerja->currentPage() - 1) * $unitKerja->perPage() + $loop->iteration }}
                                        @else
                                             {{ $loop->iteration }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-900">
                                        {{ $row->nama_unit }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $row->keterangan ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
    <div class="flex items-center justify-center gap-2">
        <a href="{{ route('unit-kerja.edit', $row->id) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-all duration-150">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit
        </a>

        <form action="{{ route('unit-kerja.destroy', $row->id) }}" method="POST" class="inline-block m-0 p-0">
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
                                    <td colspan="4">
                                        <x-enterprise.empty-state
                                            title="Belum ada data unit kerja"
                                            description="Silakan tambahkan data unit kerja terlebih dahulu."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </x-enterprise.data-table>

                        @if(method_exists($unitKerja, 'links'))
                            <div class="mt-4">
                                {{ $unitKerja->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>