<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Riwayat Pendidikan"
            subtitle="Kelola seluruh riwayat pendidikan formal pegawai">
            <a href="{{ route('riwayat-pendidikan.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Riwayat Pendidikan
            </a>
        </x-enterprise.page-header>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Alert Success --}}
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

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Card Utama --}}
            <x-enterprise.card>
                <div class="p-6">
                    {{-- Form Search --}}
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                        <form method="GET" action="{{ route('riwayat-pendidikan.index') }}" class="flex flex-wrap items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Pegawai / Institusi / Jurusan..." class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 w-72">
                            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium hover:bg-slate-700 transition">Cari</button>
                            @if(request()->filled('search'))
                                <a href="{{ route('riwayat-pendidikan.index') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-300 transition">Reset</a>
                            @endif
                        </form>
                    </div>

                    <x-enterprise.data-table>
                        <x-slot name="head">
                            <tr>
                                <th class="w-16 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">Pegawai</th>
                                <th class="px-4 py-3 text-left">Jenjang</th>
                                <th class="px-4 py-3 text-left">Institusi</th>
                                <th class="px-4 py-3 text-left">Fakultas</th>
                                <th class="px-4 py-3 text-left">Jurusan</th>
                                <th class="px-4 py-3 text-center">Tahun Lulus</th>
                                <th class="px-4 py-3 text-center">Ijazah</th>
                                <th class="w-44 px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </x-slot>

                        @forelse($data as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">
                                    {{ ($data->currentPage()-1) * $data->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800">{{ $row->pegawai->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->pegawai->nip ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700">
                                    {{ $row->jenjang }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $row->institusi }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $row->fakultas ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $row->jurusan ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-slate-700">
                                    {{ $row->tahun_lulus }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($row->ijazah)
                                        <a href="{{ asset('storage/'.$row->ijazah) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium text-xs">
                                            📄 Lihat
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.action-buttons
                                        :edit="route('riwayat-pendidikan.edit', $row->id)"
                                        :delete="route('riwayat-pendidikan.destroy', $row->id)"
                                        editLabel deleteLabel
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <x-enterprise.empty-state
                                        title="Belum ada Riwayat Pendidikan"
                                        description="Silakan tambahkan data riwayat pendidikan pegawai terlebih dahulu."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-enterprise.data-table>

                    @if($data->hasPages())
                        <div class="mt-6">
                            {{ $data->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </x-enterprise.card>
        </div>
    </div>
</x-app-layout>