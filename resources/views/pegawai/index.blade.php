<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Data Utama Pegawai"
            subtitle="Kelola seluruh data operasional pegawai SIKAP Enterprise" />
    </x-slot>

    <div class="py-6" x-data="{ openImportModal: false }">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Message Success --}}
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

            {{-- Flash Message Error --}}
            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium text-red-700">{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">✕</button>
                    </div>
                </div>
            @endif

            {{-- Kartu Statistik --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <x-enterprise.stat-card title="Total Pegawai" :value="$statistics['total']" color="blue" icon="users" />
                <x-enterprise.stat-card title="PNS" :value="$statistics['pns']" color="green" icon="briefcase" />
                <x-enterprise.stat-card title="PPPK" :value="$statistics['pppk']" color="amber" icon="user-group" />
                <x-enterprise.stat-card title="Pegawai Aktif" :value="$statistics['aktif']" color="emerald" icon="check-circle" />
            </div>

            {{-- Card & Tabel Utama --}}
            <x-enterprise.card>
                <div class="p-6">
                    
                    {{-- Header Sub-Section --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-200">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar Pegawai Active</h3>
                            <p class="text-xs text-slate-500">Gunakan bilah pencarian di bawah untuk memfilter berdasarkan NIP atau Nama.</p>
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('duk.index') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline mr-2">
                                <span>Lihat Daftar Urut Kepangkatan (DUK)</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>

                            {{-- Tombol Impor Excel --}}
                            <button @click="openImportModal = true"
                                    type="button"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Impor Excel</span>
                            </button>
                        </div>
                    </div>

                    {{-- Toolbar Operasional --}}
                    <x-enterprise.toolbar
                        :create="route('pegawai.create')"
                        :searchAction="route('pegawai.index')"
                        :searchValue="request('search')"
                        placeholder="Cari NIP atau Nama Pegawai..."
                        createLabel="Tambah Pegawai"
                    />

                    {{-- Tabel --}}
                    <div class="mt-6 overflow-x-auto">
                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="w-16 px-4 py-3 text-center">Foto</th>
                                    <th class="px-4 py-3 text-left">NIP</th>
                                    <th class="px-4 py-3 text-left">Nama Pegawai</th>
                                    <th class="px-4 py-3 text-left">Unit Kerja</th>
                                    <th class="px-4 py-3 text-left">Jabatan</th>
                                    <th class="px-4 py-3 text-left">Golongan</th>
                                    <th class="w-28 px-4 py-3 text-center">Status</th>
                                    <th class="w-48 px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </x-slot>

                            @forelse($pegawai as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-medium text-slate-600">
                                        {{ ($pegawai->currentPage()-1)*$pegawai->perPage()+$loop->iteration }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <x-enterprise.avatar :src="$row->foto_url" :name="$row->nama_lengkap" />
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-slate-700 whitespace-nowrap">
                                        {{ $row->nip }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900">{{ $row->nama_lengkap }}</div>
                                        @if($row->email)
                                            <div class="text-xs text-slate-500">{{ $row->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $row->unitKerja?->nama_unit ?? $row->riwayatJabatan?->first()?->unitKerja?->nama_unit ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $row->jabatan?->nama_jabatan ?? $row->riwayatJabatan?->first()?->jabatan?->nama_jabatan ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $row->golongan?->nama_golongan ?? $row->riwayatPangkat?->first()?->golongan?->nama_golongan ?? '-' }}
                                        @if(!empty($row->golongan?->nama_pangkat ?? $row->riwayatPangkat?->first()?->golongan?->nama_pangkat))
                                            <div class="text-xs text-slate-500">{{ $row->golongan?->nama_pangkat ?? $row->riwayatPangkat?->first()?->golongan?->nama_pangkat }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <x-enterprise.badge color="{{ $row->status_pegawai == 'Aktif' ? 'green' : 'red' }}">
                                            {{ $row->status_pegawai }}
                                        </x-enterprise.badge>
                                    </td>
                                    
                                    {{-- Kolom Aksi --}}
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center justify-center gap-1.5">
                                            {{-- Detail --}}
                                            <a href="{{ route('pegawai.show', $row) }}" 
                                               title="Detail Pegawai"
                                               class="inline-flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-blue-700 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                <span>Detail</span>
                                            </a>

                                            {{-- Edit --}}
                                            <a href="{{ route('pegawai.edit', $row) }}" 
                                               title="Edit Pegawai"
                                               class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-amber-600 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span>Edit</span>
                                            </a>

                                            {{-- Hapus --}}
                                            <form action="{{ route('pegawai.destroy', $row) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pegawai ini?')" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        title="Hapus Pegawai"
                                                        class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-red-700 transition">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    <span>Hapus</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <x-enterprise.empty-state
                                            title="Belum ada data pegawai"
                                            description="Silakan tambahkan data pegawai terlebih dahulu."
                                        />
                                    </td>
                                </tr>
                            @endforelse
                        </x-enterprise.data-table>

                        @if($pegawai->hasPages())
                            <div class="mt-6">
                                {{ $pegawai->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </x-enterprise.card>

        </div>

        {{-- MODAL UPLOAD IMPOR EXCEL --}}
        <div x-show="openImportModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.away="openImportModal = false" class="bg-white rounded-xl shadow-xl max-w-md w-full p-6 text-left">
                <div class="flex justify-between items-center border-b pb-3 mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Impor Masal Data Pegawai</h3>
                    <button @click="openImportModal = false" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
                </div>

                {{-- Unduh Template --}}
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3.5 mb-4">
                    <p class="text-xs text-blue-900 mb-2 font-medium">1. Unduh template Excel resmi untuk mengunduh format kolom yang sesuai:</p>
                    <a href="{{ route('pegawai.template') }}" 
                       class="inline-flex items-center gap-1.5 text-xs bg-blue-600 text-white px-3 py-2 rounded-md font-semibold hover:bg-blue-700 transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh Template Excel (.xlsx)</span>
                    </a>
                </div>

                {{-- Form Upload Excel --}}
                <form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">2. Pilih Berkas Excel yang Sudah Diisi:</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-lg p-1">
                    </div>

                    <div class="flex justify-end space-x-2 pt-3 border-t">
                        <button type="button" @click="openImportModal = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition">Batal</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 rounded-lg shadow transition">Proses Impor</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>