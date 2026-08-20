<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Riwayat Pangkat"
            subtitle="Kelola seluruh riwayat kenaikan pangkat pegawai">
            <a href="{{ route('riwayat-pangkat.create') }}"
               class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Riwayat Pangkat
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

            {{-- Card Statistik (Perbaikan Filter Case-Insensitive) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <x-enterprise.stat-card title="Total Riwayat" :value="$data->total()" color="blue" icon="briefcase" />
                <x-enterprise.stat-card title="Data Aktif" :value="$data->filter(fn($i) => strtolower($i->status) === 'aktif')->count()" color="green" icon="check-circle" />
                <x-enterprise.stat-card title="Tidak Aktif" :value="$data->filter(fn($i) => strtolower($i->status) !== 'aktif')->count()" color="red" icon="user-group" />
            </div>

            {{-- Card & Form Search --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                        <form method="GET" action="{{ route('riwayat-pangkat.index') }}" class="flex flex-wrap items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Pegawai / NIP / Golongan..." class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500 w-72">
                            <select name="status" class="rounded-lg border-slate-300 text-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ strtolower(request('status'))=='aktif' ? 'selected':'' }}>Aktif</option>
                                <option value="nonaktif" {{ strtolower(request('status'))=='nonaktif' ? 'selected':'' }}>Tidak Aktif</option>
                            </select>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium hover:bg-slate-700 transition">Cari</button>
                            @if(request()->filled('search') || request()->filled('status'))
                                <a href="{{ route('riwayat-pangkat.index') }}" class="px-4 py-2 rounded-lg bg-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-300 transition">Reset</a>
                            @endif
                        </form>
                    </div>

                    <x-enterprise.data-table>
                        <x-slot name="head">
                            <tr>
                                <th class="w-16 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">Pegawai</th>
                                <th class="px-4 py-3 text-left">Golongan</th>
                                <th class="px-4 py-3 text-center">TMT Pangkat</th>
                                <th class="px-4 py-3 text-left">Nomor SK</th>
                                <th class="px-4 py-3 text-center">Tanggal SK</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">File SK</th>
                                <th class="w-44 px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </x-slot>

                        @forelse($data as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">
                                    {{ ($data->currentPage()-1) * $data->perPage() + $loop->iteration }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800">{{ $row->pegawai->nama_lengkap ?? $row->pegawai->nama ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 font-mono">NIP. {{ $row->pegawai->nip ?? '-' }}</div>
                                </td>
                                {{-- Perbaikan Tampilan Golongan (Bersih Tanpa Tanda Strip Melayang) --}}
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-800">
                                        {{ $row->golongan?->nama_golongan ?? $row->golongan?->golongan ?? '-' }}
                                    </div>
                                    @if(!empty($row->golongan?->nama_pangkat ?? $row->golongan?->pangkat))
                                        <div class="text-xs text-slate-500">
                                            {{ $row->golongan?->nama_pangkat ?? $row->golongan?->pangkat }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono">
                                    {{ $row->tmt ? \Carbon\Carbon::parse($row->tmt)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-slate-700 font-medium">{{ $row->nomor_sk ?? '-' }}</td>
                                <td class="px-4 py-3 text-center text-slate-700 font-mono">
                                    {{ $row->tanggal_sk ? \Carbon\Carbon::parse($row->tanggal_sk)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.badge color="{{ strtolower($row->status) == 'aktif' ? 'green' : 'red' }}">
                                        {{ strtolower($row->status) == 'aktif' ? 'Aktif' : 'Tidak Aktif' }}
                                    </x-enterprise.badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($row->file_sk_url)
                                        <a href="{{ $row->file_sk_url }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 font-medium text-xs">
                                            📄 Lihat SK
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.action-buttons
                                        :edit="route('riwayat-pangkat.edit', $row->id)"
                                        :delete="route('riwayat-pangkat.destroy', $row->id)"
                                        editLabel deleteLabel
                                    />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">
                                    <x-enterprise.empty-state
                                        title="Belum ada Riwayat Pangkat"
                                        description="Silakan tambahkan data riwayat pangkat terlebih dahulu."
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