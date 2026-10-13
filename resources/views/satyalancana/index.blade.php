<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Penghargaan Satyalancana"
            subtitle="Daftar pegawai yang berhak menerima penghargaan Satyalancana Karya Satya (10, 20, 30 Tahun) & Manajemen Usulan Resmi"
        />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="rounded-xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="text-emerald-700 font-bold hover:text-emerald-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="rounded-xl border border-amber-300 bg-amber-50 px-5 py-4 text-amber-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button type="button" class="text-amber-700 font-bold hover:text-amber-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- 1. DAFTAR PEGAWAI LAYAK MENDAPATKAN PENGHARGAAN --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar Nominasi Pegawai Memenuhi Kriteria</h3>
                            <p class="text-xs text-slate-500">Pegawai yang telah mengabdi tanpa cela selama 10, 20, atau 30 tahun dan berhak diusulkan.</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                            {{ count($layakSatyalancana) }} Pegawai Memenuhi Syarat
                        </span>
                    </div>

                    <x-enterprise.data-table title="Daftar Nominasi Satyalancana">
                        <x-slot name="head">
                            <tr>
                                <th class="w-12 px-4 py-3 text-center">No</th>
                                <th class="px-4 py-3 text-left">NIP & Nama Pegawai</th>
                                <th class="px-4 py-3 text-left">Unit Kerja & Golongan</th>
                                <th class="px-4 py-3 text-center">TMT CPNS / Pengangkatan</th>
                                <th class="px-4 py-3 text-center">Kategori Kelayakan</th>
                                <th class="px-4 py-3 text-center">Aksi & Dokumen DRH</th>
                            </tr>
                        </x-slot>

                        @forelse($layakSatyalancana as $row)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 text-center font-medium">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-slate-900">{{ $row->nama_lengkap ?? $row->nama }}</div>
                                    <div class="text-xs text-slate-500">NIP: {{ $row->nip }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-slate-800">{{ $row->unitKerja->nama_unit ?? '-' }}</div>
                                    <div class="text-xs text-slate-500">{{ $row->golongan->nama_golongan ?? '-' }} ({{ $row->golongan->kode_golongan ?? '-' }})</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $row->tmt_sk_pertama ? \Carbon\Carbon::parse($row->tmt_sk_pertama)->format('d-m-Y') : ($row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->format('d-m-Y') : '-') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <x-enterprise.badge 
                                        color="{{ $row->kategori_satyalancana == '30 Tahun' ? 'amber' : ($row->kategori_satyalancana == '20 Tahun' ? 'blue' : 'green') }}">
                                        Satyalancana {{ $row->kategori_satyalancana }}
                                    </x-enterprise.badge>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- CETAK DOKUMEN DRH RESMI --}}
                                        <a href="{{ route('satyalancana.cetak-drh', $row->id) }}"
                                           target="_blank"
                                           title="Cetak Daftar Riwayat Hidup Usulan Format BKN/Setmilpres"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                            Cetak DRH
                                        </a>

                                        {{-- AJUKAN USULAN RESMI --}}
                                        @if($row->usulan_aktif)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg bg-emerald-100 text-emerald-800">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                Sudah Diusulkan
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('satyalancana.ajukan', $row->id) }}" onsubmit="return confirm('Ajukan usulan resmi tanda kehormatan Satyalancana {{ $row->kategori_satyalancana }} untuk {{ $row->nama }}?')">
                                                @csrf
                                                <input type="hidden" name="kategori" value="{{ $row->kategori_satyalancana }}">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    Ajukan Usulan
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
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

            {{-- 2. RIWAYAT USULAN RESMI YANG TELAH DIAJUKAN --}}
            <x-enterprise.card>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Riwayat Usulan Resmi Satyalancana</h3>
                            <p class="text-xs text-slate-500">Tracking berkas usulan penganugerahan Satyalancana Karya Satya ke Rektorat / BKN.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200 text-xs font-bold text-slate-600 uppercase">
                                <tr>
                                    <th class="px-4 py-3">No. Usulan</th>
                                    <th class="px-4 py-3">Nama Pegawai & NIP</th>
                                    <th class="px-4 py-3 text-center">Kategori</th>
                                    <th class="px-4 py-3 text-center">Tahun</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                    <th class="px-4 py-3 text-center">Aksi Dokumen</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($daftarUsulan as $usulan)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">
                                            {{ $usulan->nomor_surat_usulan }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-semibold text-slate-900">{{ $usulan->pegawai->nama_lengkap ?? $usulan->pegawai->nama }}</div>
                                            <div class="text-xs text-slate-500">NIP: {{ $usulan->pegawai->nip }}</div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <x-enterprise.badge color="{{ $usulan->kategori == '30 Tahun' ? 'amber' : ($usulan->kategori == '20 Tahun' ? 'blue' : 'green') }}">
                                                {{ $usulan->kategori }}
                                            </x-enterprise.badge>
                                        </td>
                                        <td class="px-4 py-3 text-center font-medium">{{ $usulan->tahun_usulan }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                                ● {{ ucfirst($usulan->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <a href="{{ route('satyalancana.cetak-drh', $usulan->pegawai_id) }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                Unduh DRH
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-6 text-center text-slate-400 text-sm">
                                            Belum ada usulan resmi yang diajukan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>