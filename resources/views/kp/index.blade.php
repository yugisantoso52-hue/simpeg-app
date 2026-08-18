<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Monitoring Kenaikan Pangkat (KP)"
            subtitle="Daftar pegawai yang telah memenuhi masa kerja pangkat (>= 4 Tahun)"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4 flex items-center justify-between">
                    <span class="font-medium text-green-700">{{ session('success') }}</span>
                    <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900">✕</button>
                </div>
            @endif

            <x-enterprise.card>
                <div class="p-6">
                    <x-enterprise.data-table title="Pegawai Layak Naik Pangkat">
                        <x-slot name="head">
                            <tr>
                                <th class="w-16 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">Pegawai</th>
                                <th class="px-4 py-3 text-left">Golongan Sekarang</th>
                                <th class="px-4 py-3 text-center">TMT KP Terakhir</th>
                                <th class="w-48 px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </x-slot>

                        @forelse($layakKp as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-xs text-slate-500">NIP: {{ $row->nip }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $row->golongan->nama_golongan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.badge color="blue">
                                        {{ $row->tmt_kp_terakhir ? \Carbon\Carbon::parse($row->tmt_kp_terakhir)->format('d-m-Y') : '-' }}
                                    </x-enterprise.badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button 
                                        onclick="openKpModal('{{ $row->id }}', '{{ $row->nama_lengkap ?? $row->nama }}')"
                                        class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-green-700 transition">
                                        Proses Naik Pangkat
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <x-enterprise.empty-state
                                        title="Belum Ada Usulan Kenaikan Pangkat"
                                        description="Tidak ada pegawai yang memenuhi kriteria 4 tahun masa pangkat."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-enterprise.data-table>
                </div>
            </x-enterprise.card>
        </div>
    </div>

    <!-- Modal Form Naik Pangkat -->
    <div id="kpModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center">
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-lg">
            <h3 class="text-lg font-bold text-gray-900" id="modalKpNama">Naik Pangkat</h3>
            <form id="kpForm" method="POST" action="" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Golongan Baru</label>
                    <select name="golongan_baru_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">-- Pilih Golongan --</option>
                        @foreach($golongans as $g)
                            <option value="{{ $g->id }}">{{ $g->nama_golongan }} - {{ $g->nama_pangkat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">TMT Pangkat Baru</label>
                    <input type="date" name="tmt_pangkat_baru" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeKpModal()" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Batal</button>
                    <button type="submit" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">Update Pangkat</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openKpModal(id, nama) {
            document.getElementById('modalKpNama').innerText = 'Naik Pangkat: ' + nama;
            document.getElementById('kpForm').action = '/kp/proses/' + id;
            document.getElementById('kpModal').classList.remove('hidden');
        }
        function closeKpModal() {
            document.getElementById('kpModal').classList.add('hidden');
        }
    </script>
</x-app-layout>