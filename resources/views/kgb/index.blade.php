<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Monitoring Kenaikan Gaji Berkala (KGB)"
            subtitle="Daftar pegawai yang telah memenuhi syarat KGB (Kelipatan 2 Tahun)"
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

            {{-- Tabs Filter Navigasi --}}
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-gray-200 shadow-sm">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('kgb.index', ['filter' => 'reminder']) }}" 
                       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ ($filter ?? '') === 'reminder' ? 'bg-emerald-600 text-white shadow' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-300' }}">
                        <span>🎯 Jatuh Tempo 3 Bulan (Sesuai Dashboard)</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ ($filter ?? '') === 'reminder' ? 'bg-emerald-700 text-white' : 'bg-emerald-200 text-emerald-900' }} font-mono font-bold">{{ count($reminderKgb ?? []) }}</span>
                    </a>
                    <a href="{{ route('kgb.index', ['filter' => 'all']) }}" 
                       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ ($filter ?? 'all') === 'all' ? 'bg-blue-600 text-white shadow' : 'bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-300' }}">
                        <span>Semua Layak KGB</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ ($filter ?? 'all') === 'all' ? 'bg-blue-700 text-white' : 'bg-gray-200 text-gray-800' }} font-mono font-bold">{{ count($allLayakKgb ?? []) }}</span>
                    </a>
                    <a href="{{ route('kgb.index', ['filter' => 'overdue']) }}" 
                       class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-1.5 {{ ($filter ?? '') === 'overdue' ? 'bg-rose-600 text-white shadow' : 'bg-rose-50 text-rose-800 hover:bg-rose-100 border border-rose-300' }}">
                        <span>⚠️ Sudah Lewat Waktu (Tertunggak)</span>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ ($filter ?? '') === 'overdue' ? 'bg-rose-700 text-white' : 'bg-rose-200 text-rose-900' }} font-mono font-bold">{{ count($overdueKgb ?? []) }}</span>
                    </a>
                </div>
            </div>

            <x-enterprise.card>
                <div class="p-6">
                    <x-enterprise.data-table title="{{ ($filter ?? '') === 'reminder' ? 'Pegawai Jatuh Tempo 3 Bulan ke Depan (Sesuai Dashboard)' : (($filter ?? '') === 'overdue' ? 'Pegawai Belum Diproses KGB (Sudah Lewat Waktu)' : 'Semua Pegawai Layak KGB') }}">
                        <x-slot name="head">
                            <tr>
                                <th class="w-16 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">NIP & Nama</th>
                                <th class="px-4 py-3 text-left">Unit Kerja</th>
                                <th class="px-4 py-3 text-left">Golongan</th>
                                <th class="px-4 py-3 text-center">TMT KGB Terakhir</th>
                                <th class="w-48 px-4 py-3 text-center">Aksi Modal</th>
                            </tr>
                        </x-slot>

                        @forelse($layakKgb as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->nip }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $row->golongan->nama_golongan ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.badge color="amber">
                                        {{ $row->tmt_kgb_terakhir ? \Carbon\Carbon::parse($row->tmt_kgb_terakhir)->format('d-m-Y') : '-' }}
                                    </x-enterprise.badge>
                                    @if($row->kgb_berikutnya)
                                        <div class="mt-1">
                                            <span class="text-[10px] font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 inline-flex items-center gap-1">
                                                🎯 Jatuh Tempo: {{ \Carbon\Carbon::parse($row->kgb_berikutnya)->format('d-m-Y') }}
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button 
                                        onclick="openKgbModal('{{ $row->id }}', '{{ $row->nama_lengkap ?? $row->nama }}', '{{ $row->golongan_id }}')"
                                        class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-blue-700 transition">
                                        ⚡ Proses KGB Lengkap
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-enterprise.empty-state
                                        title="Tidak Ada Usulan KGB"
                                        description="Saat ini belum ada pegawai yang memasuki periode Kenaikan Gaji Berkala."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </x-enterprise.data-table>
                </div>
            </x-enterprise.card>
        </div>
    </div>

    <!-- Modal Form Proses KGB Lengkap -->
    <div id="kgbModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="relative w-full max-w-2xl rounded-xl bg-white p-6 shadow-xl my-8">
            <div class="flex justify-between items-center border-b pb-3 mb-4">
                <h3 class="text-lg font-bold text-gray-900" id="modalNamaPegawai">Proses KGB</h3>
                <button onclick="closeKgbModal()" class="text-gray-400 hover:text-gray-600 font-bold text-xl">✕</button>
            </div>

            <form id="kgbForm" method="POST" action="" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Nomor SK KGB --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Nomor SK KGB <span class="text-red-500">*</span></label>
                        <input type="text" name="nomor_sk" required placeholder="Contoh: 800/123/SK-KGB/2026" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    {{-- Tanggal SK --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Tanggal SK <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_sk" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    {{-- TMT KGB Baru --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">TMT KGB Baru <span class="text-red-500">*</span></label>
                        <input type="date" name="tmt_kgb_baru" required value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    {{-- Gaji Pokok Baru --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Gaji Pokok Baru (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="gaji_pokok_baru" required placeholder="3500000" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    {{-- Pejabat Penetap --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Pejabat Penetap</label>
                        <input type="text" name="pejabat_penetap" placeholder="Kepala Dinas" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>

                    {{-- Masa Kerja (Thn / Bln) --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase">Masa Kerja (Thn)</label>
                            <input type="number" name="masa_kerja_tahun" value="0" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase">Masa Kerja (Bln)</label>
                            <input type="number" name="masa_kerja_bulan" value="0" min="0" max="11" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>
                </div>

                {{-- Upload File SK KGB --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase">Upload File SK KGB (PDF/JPG)</label>
                    <input type="file" name="file_sk" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-xs text-gray-500 border border-gray-300 rounded-md cursor-pointer bg-gray-50 p-2">
                </div>

                <div class="flex justify-end gap-2 border-t pt-4 mt-4">
                    <button type="button" onclick="closeKgbModal()" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">Batal</button>
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow">💾 Simpan Data KGB</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openKgbModal(id, nama) {
            document.getElementById('modalNamaPegawai').innerText = 'Proses KGB: ' + nama;
            document.getElementById('kgbForm').action = '/kgb/proses/' + id;
            document.getElementById('kgbModal').classList.remove('hidden');
        }
        function closeKgbModal() {
            document.getElementById('kgbModal').classList.add('hidden');
        }
    </script>
</x-app-layout>