<x-app-layout>
    <x-slot name="header">
        <x-enterprise.breadcrumb
            :items="[
                ['label' => 'Data Kepegawaian', 'url' => route('pegawai.index')],
                ['label' => $kategoriTitle ?? 'Data Pegawai']
            ]"
        />

        <div class="mt-4">
            <x-enterprise.page-header
                :title="$kategoriTitle"
                :subtitle="$kategoriSubtitle">

                <div class="flex items-center gap-2">
                    <a href="{{ route('pegawai.create') }}"
                       class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Tambah Pegawai</span>
                    </a>
                </div>
            </x-enterprise.page-header>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="font-medium text-green-700">{{ session('success') }}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 hover:text-green-900">✕</button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium text-red-700">{{ session('error') }}</span>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">✕</button>
                    </div>
                </div>
            @endif

            {{-- Kartu Statistik Kategori --}}
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                <x-enterprise.stat-card :title="'Total ' . $kategori" :value="$statistics['total'] ?? 0" :color="$badgeColor ?? 'blue'" icon="users" />
                <x-enterprise.stat-card title="Status Aktif" :value="$statistics['aktif'] ?? 0" color="emerald" icon="check-circle" />
                @if(isset($statistics['pns']))
                    <x-enterprise.stat-card title="Aparatur Sipil Negara (ASN)" :value="$statistics['pns'] ?? 0" color="green" icon="briefcase" />
                @elseif(isset($statistics['non_asn']))
                    <x-enterprise.stat-card title="Status Non ASN" :value="$statistics['non_asn'] ?? 0" color="amber" icon="briefcase" />
                @endif
                @if(isset($statistics['tubel']))
                    <x-enterprise.stat-card title="Tugas Belajar" :value="$statistics['tubel'] ?? 0" color="indigo" icon="academic-cap" />
                @elseif(isset($statistics['kontrak']))
                    <x-enterprise.stat-card title="Tenaga Kontrak" :value="$statistics['kontrak'] ?? 0" color="purple" icon="document-text" />
                @endif
            </div>

            {{-- Card & Tabel Kategori --}}
            <x-enterprise.card>
                <div class="p-6">
                    
                    {{-- Header Sub-Section & Toolbar --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-200">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Daftar {{ $kategoriTitle }}</h3>
                            <p class="text-xs text-slate-500">Menampilkan seluruh pegawai pada kelompok {{ $kategori }}.</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('pegawai.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 px-3 py-1.5 rounded-lg transition">
                                📋 Lihat Semua Pegawai
                            </a>
                            <a href="{{ route('duk.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                                📊 DUK
                            </a>
                        </div>
                    </div>

                    {{-- Toolbar Pencarian --}}
                    <form method="GET" action="{{ url()->current() }}" class="flex flex-wrap items-center gap-3">
                        <div class="relative flex-1 min-w-[240px]">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                                🔍
                            </span>
                            <input type="text"
                                   name="search"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Cari NIP, NIDN, atau Nama {{ $kategori }}..."
                                   class="w-full pl-9 pr-4 py-2 text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                            Cari
                        </button>
                        @if($search)
                            <a href="{{ url()->current() }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition">
                                Reset
                            </a>
                        @endif
                    </form>

                    {{-- Tabel --}}
                    <div class="mt-6 overflow-x-auto">
                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="w-16 px-4 py-3 text-center">Foto</th>
                                    <th class="px-4 py-3 text-left">NIP / NIDN</th>
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
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-semibold text-slate-700">{{ $row->nip }}</div>
                                        @if($row->nidn_nuptk)
                                            <div class="text-[11px] text-blue-600 font-mono">NIDN: {{ $row->nidn_nuptk }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900">{{ $row->nama_lengkap }}</div>
                                        @if($row->email)
                                            <div class="text-xs text-slate-500">{{ $row->email }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $row->unitKerja?->nama_unit ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $row->jabatan?->nama_jabatan ?? '-' }}
                                        @if($row->jenis_jabatan)
                                            <div class="text-[11px] text-slate-400">{{ $row->jenis_jabatan }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $row->golongan?->nama_golongan ?? '-' }}
                                        @if(!empty($row->golongan?->nama_pangkat))
                                            <div class="text-xs text-slate-500">{{ $row->golongan->nama_pangkat }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <x-enterprise.badge color="{{ $row->status_pegawai == 'Aktif' ? 'green' : ($row->status_pegawai == 'Tugas Belajar' ? 'blue' : 'red') }}">
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
                                                <span>Detail</span>
                                            </a>

                                            {{-- Edit --}}
                                            @if(Auth::user()->hasRole('admin'))
                                                <a href="{{ route('pegawai.edit', $row) }}" 
                                                   title="Edit Pegawai"
                                                   class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-amber-600 transition">
                                                    <span>Edit</span>
                                                </a>

                                                {{-- Hapus --}}
                                                <form action="{{ route('pegawai.destroy', $row) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            title="Hapus Pegawai"
                                                            class="inline-flex items-center gap-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-red-700 transition">
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <x-enterprise.empty-state
                                            :title="'Belum ada data ' . $kategori"
                                            :description="'Silakan tambahkan data ' . $kategori . ' melalui tombol Tambah Pegawai.'"
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
    </div>
</x-app-layout>
