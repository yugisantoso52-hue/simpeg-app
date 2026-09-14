<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Penghargaan Satyalancana"
            subtitle="Daftar pegawai yang berhak menerima penghargaan Satyalancana Karya Satya (10, 20, 30 Tahun)"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            <x-enterprise.card>
                <div class="p-6">
                    <x-enterprise.data-table title="Daftar Nominasi Satyalancana">
                        <x-slot name="head">
                            <tr>
                                <th class="w-16 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">NIP & Nama</th>
                                <th class="px-4 py-3 text-left">Unit Kerja</th>
                                <th class="px-4 py-3 text-center">TMT Masuk / SK</th>
                                <th class="px-4 py-3 text-center">Kategori Kelayakan</th>
                            </tr>
                        </x-slot>

                        @forelse($layakSatyalancana as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-xs text-slate-500">NIP: {{ $row->nip }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ $row->tmt_sk_pertama ? \Carbon\Carbon::parse($row->tmt_sk_pertama)->format('d-m-Y') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.badge 
                                        color="{{ $row->kategori_satyalancana == '30 Tahun' ? 'amber' : ($row->kategori_satyalancana == '20 Tahun' ? 'blue' : 'green') }}">
                                        Satyalancana {{ $row->kategori_satyalancana }}
                                    </x-enterprise.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-enterprise.empty-state
                                        title="Tidak Ada Usulan Satyalancana"
                                        description="Belum ada pegawai yang memenuhi kriteria pengabdian 10, 20, atau 30 tahun."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-enterprise.data-table>
                </div>
            </x-enterprise.card>
        </div>
    </div>
</x-app-layout>