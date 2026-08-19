<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Daftar Urut Kepangkatan (DUK)"
            subtitle="Urutan hierarki kepangkatan dan masa kerja Apratur Sipil Negara" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Message Error --}}
            @if(session('error'))
                <x-enterprise.alert type="error" title="Terjadi Kesalahan">
                    {{ session('error') }}
                </x-enterprise.alert>
            @endif

            {{-- Flash Message Success --}}
            @if(session('success'))
                <x-enterprise.alert type="success" title="Sukses">
                    {{ session('success') }}
                </x-enterprise.alert>
            @endif

            {{-- Card Utama DUK --}}
            <x-enterprise.card>
                <div class="p-6">
                    
                    {{-- Action Header & Tombol Export --}}
                    <div class="flex flex-wrap items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-200">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Laporan Resmi DUK</h3>
                            <p class="text-xs text-slate-500">Urutan otomatis berdasarkan Golongan, TMT Pangkat, dan Usia Pegawai.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('reports.duk.pdf', request()->query()) }}" target="_blank"
                               class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Cetak DUK (PDF)
                            </a>

                            <a href="{{ route('reports.duk.excel', request()->query()) }}"
                               class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Export DUK (Excel)
                            </a>
                        </div>
                    </div>

                    {{-- Form Pencarian DUK --}}
                    <form method="GET" action="{{ route('duk.index') }}" class="mb-6">
                        <div class="flex items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Cari NIP atau Nama Pegawai dalam DUK..." 
                                   class="w-full max-w-md rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500" />
                            
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 active:bg-blue-800 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="text-white font-medium">Cari</span>
                            </button>

                            @if(request('search'))
                                <a href="{{ route('duk.index') }}" 
                                   class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300 transition">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- Tabel DUK Lengkap --}}
                    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                        <table class="w-full min-w-full divide-y divide-slate-200 text-left text-xs text-slate-700">
                            <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-2 py-3 text-center border-r border-slate-200">NO</th>
                                    <th class="px-3 py-3 text-left border-r border-slate-200">NAMA / NIP</th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">GOL / PANGKAT</th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">PENDIDIKAN</th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">TGL MASUK</th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">TMT SK 1</th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">
                                        TMT PANGKAT<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span>
                                    </th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">
                                        TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span>
                                    </th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">MASA KERJA</th>
                                    <th class="px-3 py-3 text-center border-r border-slate-200">SATYALANCANA</th>
                                    <th class="px-3 py-3 text-center">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse($pegawais as $row)
                                    @php
                                        // Format Tanggal
                                        $tglMasuk = $row->tanggal_masuk ? \Carbon\Carbon::parse($row->tanggal_masuk)->format('d/m/Y') : '-';
                                        $tmtSk1   = $row->tmt_sk_pertama ? \Carbon\Carbon::parse($row->tmt_sk_pertama)->format('d/m/Y') : '-';
                                        
                                        $tmtPangkatLama  = $row->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($row->tmt_pangkat_terakhir)->format('d/m/Y') : '-';
                                        $tmtPangkatDepan = $row->kp_berikutnya ? \Carbon\Carbon::parse($row->kp_berikutnya)->format('d/m/Y') : ($row->kp_berikutnya_kalkulasi ? \Carbon\Carbon::parse($row->kp_berikutnya_kalkulasi)->format('d/m/Y') : '-');
                                        
                                        $tmtKgbLama  = $row->tmt_kgb_terakhir ? \Carbon\Carbon::parse($row->tmt_kgb_terakhir)->format('d/m/Y') : '-';
                                        $tmtKgbDepan = $row->kgb_berikutnya ? \Carbon\Carbon::parse($row->kgb_berikutnya)->format('d/m/Y') : ($row->kgb_berikutnya_kalkulasi ? \Carbon\Carbon::parse($row->kgb_berikutnya_kalkulasi)->format('d/m/Y') : '-');
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition">
                                        {{-- NO --}}
                                        <td class="px-2 py-3 text-center font-bold text-slate-800 border-r border-slate-100">
                                            {{ $loop->iteration }}
                                        </td>
                                        
                                        {{-- NAMA / NIP --}}
                                        <td class="px-3 py-3 border-r border-slate-100 whitespace-nowrap">
                                            <div class="font-bold text-slate-900">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </div>
                                            <div class="text-[11px] text-slate-500 font-mono">NIP. {{ $row->nip ?? '-' }}</div>
                                        </td>

                                        {{-- GOL / PANGKAT --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            <span class="font-semibold text-slate-800">
                                                {{ $row->golongan?->nama_golongan ?? $row->golongan?->golongan ?? '-' }}
                                            </span>
                                            @if(!empty($row->golongan?->nama_pangkat ?? $row->golongan?->pangkat))
                                                <div class="text-[10px] text-slate-500">
                                                    {{ $row->golongan?->nama_pangkat ?? $row->golongan?->pangkat }}
                                                </div>
                                            @endif
                                        </td>

                                        {{-- PENDIDIKAN --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            {{ $row->pendidikan_tampil ?? $row->pendidikan_terakhir ?? $row->pendidikan ?? '-' }}
                                        </td>

                                        {{-- TGL MASUK --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            {{ $tglMasuk }}
                                        </td>

                                        {{-- TMT SK 1 + Link SK --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            <div>{{ $tmtSk1 }}</div>
                                            @if(!empty($row->file_sk_pertama) && trim($row->file_sk_pertama) !== '')
                                                <a href="{{ route('document.preview', ['path' => $row->file_sk_pertama]) }}" target="_blank" 
                                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline mt-1 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                                    📄 Lihat SK
                                                </a>
                                            @else
                                                <span class="text-[10px] text-slate-400 italic block mt-1">(SK Tidak Ada)</span>
                                            @endif
                                        </td>

                                        {{-- TMT PANGKAT (LAMA / DEPAN) + Link SK --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            <div>Terakhir: {{ $tmtPangkatLama }}</div>
                                            <div class="font-bold text-blue-600">Kedepan: {{ $tmtPangkatDepan }}</div>
                                            @if(!empty($row->file_sk_pangkat_terakhir) && trim($row->file_sk_pangkat_terakhir) !== '')
                                                <a href="{{ route('document.preview', ['path' => $row->file_sk_pangkat_terakhir]) }}" target="_blank" 
                                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline mt-1 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                                    📄 Lihat SK
                                                </a>
                                            @else
                                                <span class="text-[10px] text-slate-400 italic block mt-1">(SK Tidak Ada)</span>
                                            @endif
                                        </td>

                                        {{-- TMT KGB (LAMA / DEPAN) + Link SK --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            <div>Terakhir: {{ $tmtKgbLama }}</div>
                                            <div class="font-bold text-blue-600">Kedepan: {{ $tmtKgbDepan }}</div>
                                            @if(!empty($row->file_sk_kgb_terakhir) && trim($row->file_sk_kgb_terakhir) !== '')
                                                <a href="{{ route('document.preview', ['path' => $row->file_sk_kgb_terakhir]) }}" target="_blank" 
                                                   class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline mt-1 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                                                    📄 Lihat SK
                                                </a>
                                            @else
                                                <span class="text-[10px] text-slate-400 italic block mt-1">(SK Tidak Ada)</span>
                                            @endif
                                        </td>

                                        {{-- MASA KERJA --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            {{ $row->masa_kerja_formatted ?? ($row->masa_kerja_tahun ? $row->masa_kerja_tahun . ' Thn ' . $row->masa_kerja_bulan . ' Bln' : '-') }}
                                        </td>

                                        {{-- SATYALANCANA --}}
                                        <td class="px-3 py-3 text-center border-r border-slate-100 whitespace-nowrap">
                                            {{ $row->satyalancana_tampil ?? $row->satyalancana_terakhir ?? '-' }}
                                        </td>

                                        {{-- STATUS --}}
                                        <td class="px-3 py-3 text-center whitespace-nowrap">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                                {{ $row->status_pegawai ?? 'Aktif' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="p-8 text-center text-slate-500">
                                            <x-enterprise.empty-state
                                                title="Data DUK Tidak Ditemukan"
                                                description="Tidak ada pegawai yang memenuhi kriteria pengurutan DUK."
                                            />
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