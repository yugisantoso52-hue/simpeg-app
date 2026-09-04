<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Daftar Urut Kepangkatan (DUK)"
            subtitle="Urutan hierarki kepangkatan dan masa kerja Aparatur Sipil Negara & Tenaga Penunjang" />
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('error'))
                <x-enterprise.alert type="error" title="Terjadi Kesalahan">
                    {{ session('error') }}
                </x-enterprise.alert>
            @endif

            @if(session('success'))
                <x-enterprise.alert type="success" title="Sukses">
                    {{ session('success') }}
                </x-enterprise.alert>
            @endif

            {{-- Ringkasan Statistik DUK Interaktif --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <a href="{{ route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'dosen_pns' ? '' : 'dosen_pns'])) }}"
                   class="bg-white rounded-xl border p-4 shadow-sm flex items-center justify-between transition hover:shadow-md hover:scale-[1.01] cursor-pointer {{ $filter === 'dosen_pns' ? 'border-blue-500 ring-2 ring-blue-500/20 bg-blue-50/20' : 'border-blue-200' }}">
                    <div>
                        <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">DUK Dosen PNS</span>
                        <div class="text-xl font-black text-slate-800 mt-0.5">{{ $statistics['dosen_pns'] }} Orang</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Tenaga Pendidik PNS</p>
                    </div>
                    <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center text-lg font-bold">
                        👨‍🏫
                    </div>
                </a>

                <a href="{{ route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'dosen_pppk' ? '' : 'dosen_pppk'])) }}"
                   class="bg-white rounded-xl border p-4 shadow-sm flex items-center justify-between transition hover:shadow-md hover:scale-[1.01] cursor-pointer {{ $filter === 'dosen_pppk' ? 'border-indigo-500 ring-2 ring-indigo-500/20 bg-indigo-50/20' : 'border-indigo-200' }}">
                    <div>
                        <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">DUK Dosen PPPK</span>
                        <div class="text-xl font-black text-slate-800 mt-0.5">{{ $statistics['dosen_pppk'] }} Orang</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Tenaga Pendidik PPPK</p>
                    </div>
                    <div class="w-10 h-10 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center text-lg font-bold">
                        🎓
                    </div>
                </a>

                <a href="{{ route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'tendik_pns' ? '' : 'tendik_pns'])) }}"
                   class="bg-white rounded-xl border p-4 shadow-sm flex items-center justify-between transition hover:shadow-md hover:scale-[1.01] cursor-pointer {{ $filter === 'tendik_pns' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/20' : 'border-emerald-200' }}">
                    <div>
                        <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">DUK Tendik PNS</span>
                        <div class="text-xl font-black text-slate-800 mt-0.5">{{ $statistics['tendik_pns'] }} Orang</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Tenaga Kependidikan PNS</p>
                    </div>
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-lg flex items-center justify-center text-lg font-bold">
                        🧑‍💼
                    </div>
                </a>

                <a href="{{ route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'tendik_pppk' ? '' : 'tendik_pppk'])) }}"
                   class="bg-white rounded-xl border p-4 shadow-sm flex items-center justify-between transition hover:shadow-md hover:scale-[1.01] cursor-pointer {{ $filter === 'tendik_pppk' ? 'border-teal-500 ring-2 ring-teal-500/20 bg-teal-50/20' : 'border-teal-200' }}">
                    <div>
                        <span class="text-[11px] font-bold text-teal-600 uppercase tracking-wider">DUK Tendik PPPK</span>
                        <div class="text-xl font-black text-slate-800 mt-0.5">{{ $statistics['tendik_pppk'] }} Orang</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Tenaga Kependidikan PPPK</p>
                    </div>
                    <div class="w-10 h-10 bg-teal-50 text-teal-600 rounded-lg flex items-center justify-center text-lg font-bold">
                        📑
                    </div>
                </a>

                <a href="{{ route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'phl' ? '' : 'phl'])) }}"
                   class="bg-white rounded-xl border p-4 shadow-sm flex items-center justify-between transition hover:shadow-md hover:scale-[1.01] cursor-pointer {{ $filter === 'phl' ? 'border-amber-500 ring-2 ring-amber-500/20 bg-amber-50/20' : 'border-amber-200' }}">
                    <div>
                        <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Daftar Urut PHL</span>
                        <div class="text-xl font-black text-slate-800 mt-0.5">{{ $statistics['phl'] }} Orang</div>
                        <p class="text-[10px] text-slate-500 mt-0.5">Pegawai Harian Lepas & Kontrak</p>
                    </div>
                    <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-lg flex items-center justify-center text-lg font-bold">
                        👷
                    </div>
                </a>
            </div>

            {{-- Card Utama DUK --}}
            <x-enterprise.card>
                <div class="p-6 space-y-8">
                    
                    {{-- Action Header & Tombol Export --}}
                    <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-200">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">Laporan Resmi DUK Terpisah (Dosen & Tendik: PNS vs PPPK, PHL)</h3>
                            <p class="text-xs text-slate-500">Urutan hierarki otomatis berdasarkan Golongan (Tertinggi), TMT Pangkat, dan Usia.</p>
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
                    <form method="GET" action="{{ route('duk.index') }}">
                        @if(request('filter'))
                            <input type="hidden" name="filter" value="{{ request('filter') }}" />
                        @endif
                        <div class="flex items-center gap-2">
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Cari NIP, NIDN, atau Nama Pegawai..." 
                                   class="w-full max-w-md rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-800 focus:border-blue-500 focus:ring-blue-500" />
                            
                            <button type="submit" 
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700 active:bg-blue-800 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <span class="text-white font-medium">Cari</span>
                            </button>

                            @if(request('search') || request('filter'))
                                <a href="{{ route('duk.index') }}" 
                                   class="inline-flex items-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-300 transition">
                                    Reset Filter
                                </a>
                            @endif
                        </div>
                    </form>

                    {{-- Indikator Filter Aktif --}}
                    @if($filter)
                        <div class="flex items-center justify-between bg-blue-50 border border-blue-200 px-4 py-2 rounded-lg text-xs text-blue-800 font-medium">
                            <div>
                                Menampilkan DUK untuk kategori: <strong>{{ strtoupper(str_replace('_', ' ', $filter)) }}</strong>
                            </div>
                            <a href="{{ route('duk.index', request()->except('filter')) }}" class="text-blue-600 underline font-bold hover:text-blue-800">
                                Tampilkan Semua Kategori
                            </a>
                        </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 1. TABEL DUK DOSEN PNS                                                    --}}
                    {{-- ========================================================================= --}}
                    @if(!$filter || $filter === 'dosen_pns')
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-blue-50 border-l-4 border-blue-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-blue-900">👨‍🏫 I. DAFTAR URUT KEPANGKATAN DOSEN PNS</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-blue-600 text-white rounded-full">{{ count($dosenPnsList) }} Orang</span>
                            </div>
                            <span class="text-xs text-blue-700 font-medium">Tenaga Pendidik PNS</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                            <table class="w-full min-w-full divide-y divide-slate-200 text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-2 py-3 text-center border-r border-slate-200 w-10">NO</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">NAMA / NIP / NIDN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">GOL / PANGKAT</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">JABATAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">PENDIDIKAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT PANGKAT<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">MASA KERJA</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">SATYALANCANA</th>
                                        <th class="px-3 py-3 text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($dosenPnsList as $row)
                                        <tr class="hover:bg-blue-50/50 transition">
                                            <td class="px-2 py-3 text-center font-bold text-slate-600 border-r border-slate-200">{{ $loop->iteration }}</td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <a href="{{ route('pegawai.show', $row->id) }}" class="font-bold text-blue-600 hover:underline">
                                                    {{ $row->nama_lengkap ?? $row->nama }}
                                                </a>
                                                <div class="text-[11px] text-slate-500">NIP. {{ $row->nip ?? '-' }}</div>
                                                @if($row->nidn_nuptk)
                                                    <div class="text-[10px] font-mono text-emerald-600">NIDN: {{ $row->nidn_nuptk }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <div class="font-bold text-slate-800">{{ $row->golongan->nama_golongan ?? '-' }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $row->golongan->nama_pangkat ?? '' }}</div>
                                            </td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <div class="font-semibold text-slate-800">{{ $row->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $row->unitKerja->nama_unit ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->pendidikan_tampil }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-blue-700 text-[11px]">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-emerald-700 text-[11px]">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->masa_kerja_formatted }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200 font-medium">{{ $row->satyalancana_tampil }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $row->status_pegawai == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ $row->status_pegawai ?? 'Aktif' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-4 py-4 text-center text-slate-400">Tidak ada data Dosen PNS yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 2. TABEL DUK DOSEN PPPK                                                   --}}
                    {{-- ========================================================================= --}}
                    @if(!$filter || $filter === 'dosen_pppk')
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-indigo-50 border-l-4 border-indigo-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-indigo-900">🎓 II. DAFTAR URUT KEPANGKATAN DOSEN PPPK</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-indigo-600 text-white rounded-full">{{ count($dosenPppkList) }} Orang</span>
                            </div>
                            <span class="text-xs text-indigo-700 font-medium">Tenaga Pendidik PPPK</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                            <table class="w-full min-w-full divide-y divide-slate-200 text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-2 py-3 text-center border-r border-slate-200 w-10">NO</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">NAMA / NIP / NIDN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">GOL / PANGKAT</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">JABATAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">PENDIDIKAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT PANGKAT<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">MASA KERJA</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">SATYALANCANA</th>
                                        <th class="px-3 py-3 text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($dosenPppkList as $row)
                                        <tr class="hover:bg-indigo-50/50 transition">
                                            <td class="px-2 py-3 text-center font-bold text-slate-600 border-r border-slate-200">{{ $loop->iteration }}</td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <a href="{{ route('pegawai.show', $row->id) }}" class="font-bold text-indigo-600 hover:underline">
                                                    {{ $row->nama_lengkap ?? $row->nama }}
                                                </a>
                                                <div class="text-[11px] text-slate-500">NIP. {{ $row->nip ?? '-' }}</div>
                                                @if($row->nidn_nuptk)
                                                    <div class="text-[10px] font-mono text-emerald-600">NIDN: {{ $row->nidn_nuptk }}</div>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <div class="font-bold text-slate-800">{{ $row->golongan->nama_golongan ?? '-' }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $row->golongan->nama_pangkat ?? '' }}</div>
                                            </td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <div class="font-semibold text-slate-800">{{ $row->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $row->unitKerja->nama_unit ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->pendidikan_tampil }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-blue-700 text-[11px]">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-emerald-700 text-[11px]">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->masa_kerja_formatted }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200 font-medium">{{ $row->satyalancana_tampil }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $row->status_pegawai == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-indigo-100 text-indigo-700' }}">
                                                    {{ $row->status_pegawai ?? 'Aktif' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-4 py-4 text-center text-slate-400">Tidak ada data Dosen PPPK yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 3. TABEL DUK TENAGA KEPENDIDIKAN (TENDIK) PNS                             --}}
                    {{-- ========================================================================= --}}
                    @if(!$filter || $filter === 'tendik_pns')
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-emerald-50 border-l-4 border-emerald-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-emerald-900">🧑‍💼 III. DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PNS</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-emerald-600 text-white rounded-full">{{ count($tendikPnsList) }} Orang</span>
                            </div>
                            <span class="text-xs text-emerald-700 font-medium">Staf Administrasi / Laboran PNS</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                            <table class="w-full min-w-full divide-y divide-slate-200 text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-2 py-3 text-center border-r border-slate-200 w-10">NO</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">NAMA / NIP</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">GOL / PANGKAT</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">JABATAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">PENDIDIKAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT PANGKAT<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">MASA KERJA</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">SATYALANCANA</th>
                                        <th class="px-3 py-3 text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($tendikPnsList as $row)
                                        <tr class="hover:bg-emerald-50/50 transition">
                                            <td class="px-2 py-3 text-center font-bold text-slate-600 border-r border-slate-200">{{ $loop->iteration }}</td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <a href="{{ route('pegawai.show', $row->id) }}" class="font-bold text-emerald-700 hover:underline">
                                                    {{ $row->nama_lengkap ?? $row->nama }}
                                                </a>
                                                <div class="text-[11px] text-slate-500">NIP. {{ $row->nip ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <div class="font-bold text-slate-800">{{ $row->golongan->nama_golongan ?? '-' }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $row->golongan->nama_pangkat ?? '' }}</div>
                                            </td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <div class="font-semibold text-slate-800">{{ $row->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $row->unitKerja->nama_unit ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->pendidikan_tampil }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-blue-700 text-[11px]">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-emerald-700 text-[11px]">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->masa_kerja_formatted }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200 font-medium">{{ $row->satyalancana_tampil }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $row->status_pegawai == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                                    {{ $row->status_pegawai ?? 'Aktif' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-4 py-4 text-center text-slate-400">Tidak ada data Tendik PNS yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 4. TABEL DUK TENAGA KEPENDIDIKAN (TENDIK) PPPK                            --}}
                    {{-- ========================================================================= --}}
                    @if(!$filter || $filter === 'tendik_pppk')
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-teal-50 border-l-4 border-teal-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-teal-900">📑 IV. DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PPPK</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-teal-600 text-white rounded-full">{{ count($tendikPppkList) }} Orang</span>
                            </div>
                            <span class="text-xs text-teal-700 font-medium">Staf Administrasi / Laboran PPPK</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                            <table class="w-full min-w-full divide-y divide-slate-200 text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-2 py-3 text-center border-r border-slate-200 w-10">NO</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">NAMA / NIP</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">GOL / PANGKAT</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">JABATAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">PENDIDIKAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT PANGKAT<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">MASA KERJA</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">SATYALANCANA</th>
                                        <th class="px-3 py-3 text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($tendikPppkList as $row)
                                        <tr class="hover:bg-teal-50/50 transition">
                                            <td class="px-2 py-3 text-center font-bold text-slate-600 border-r border-slate-200">{{ $loop->iteration }}</td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <a href="{{ route('pegawai.show', $row->id) }}" class="font-bold text-teal-700 hover:underline">
                                                    {{ $row->nama_lengkap ?? $row->nama }}
                                                </a>
                                                <div class="text-[11px] text-slate-500">NIP. {{ $row->nip ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <div class="font-bold text-slate-800">{{ $row->golongan->nama_golongan ?? '-' }}</div>
                                                <div class="text-[10px] text-slate-500">{{ $row->golongan->nama_pangkat ?? '' }}</div>
                                            </td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <div class="font-semibold text-slate-800">{{ $row->jabatan->nama_jabatan ?? '-' }}</div>
                                                <div class="text-[11px] text-slate-500">{{ $row->unitKerja->nama_unit ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->pendidikan_tampil }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-blue-700 text-[11px]">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                                <br>
                                                <strong class="text-emerald-700 text-[11px]">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->masa_kerja_formatted }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200 font-medium">{{ $row->satyalancana_tampil }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $row->status_pegawai == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-teal-100 text-teal-700' }}">
                                                    {{ $row->status_pegawai ?? 'Aktif' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="px-4 py-4 text-center text-slate-400">Tidak ada data Tendik PPPK yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 5. TABEL DUK PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK                 --}}
                    {{-- ========================================================================= --}}
                    @if(!$filter || $filter === 'phl')
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-amber-50 border-l-4 border-amber-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-amber-900">👷 V. DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-amber-600 text-white rounded-full">{{ count($phlList) }} Orang</span>
                            </div>
                            <span class="text-xs text-amber-700 font-medium">Non-ASN & Kontrak Kerja</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-slate-200 shadow-sm">
                            <table class="w-full min-w-full divide-y divide-slate-200 text-left text-xs text-slate-700">
                                <thead class="bg-slate-100 text-slate-700 font-bold uppercase tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-2 py-3 text-center border-r border-slate-200 w-10">NO</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">NAMA / NIK / NIP</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">JENIS KONTRAK / JABATAN</th>
                                        <th class="px-3 py-3 text-left border-r border-slate-200">UNIT KERJA</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">PENDIDIKAN</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">PERIODE KONTRAK</th>
                                        <th class="px-3 py-3 text-center border-r border-slate-200">MASA KERJA</th>
                                        <th class="px-3 py-3 text-center">STATUS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 bg-white">
                                    @forelse($phlList as $row)
                                        <tr class="hover:bg-amber-50/50 transition">
                                            <td class="px-2 py-3 text-center font-bold text-slate-600 border-r border-slate-200">{{ $loop->iteration }}</td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <a href="{{ route('pegawai.show', $row->id) }}" class="font-bold text-amber-800 hover:underline">
                                                    {{ $row->nama_lengkap ?? $row->nama }}
                                                </a>
                                                <div class="text-[11px] text-slate-500">ID/NIP: {{ $row->nip ?? '-' }}</div>
                                            </td>
                                            <td class="px-3 py-3 border-r border-slate-200">
                                                <div class="font-semibold text-slate-800">{{ $row->jabatan->nama_jabatan ?? 'Tenaga Penunjang / PHL' }}</div>
                                                <div class="text-[11px] text-amber-700 font-medium">{{ $row->jenis_kontrak ?? 'Kontrak Harian / Tahunan' }}</div>
                                            </td>
                                            <td class="px-3 py-3 border-r border-slate-200">{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->pendidikan_tampil }}</td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">
                                                <span class="text-slate-700">{{ $row->tanggal_kontrak_mulai ? $row->tanggal_kontrak_mulai->format('d/m/Y') : ($row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-') }}</span>
                                                <span class="text-slate-400"> s/d </span>
                                                <span class="text-slate-700 font-semibold">{{ $row->tanggal_kontrak_selesai ? $row->tanggal_kontrak_selesai->format('d/m/Y') : 'Aktif' }}</span>
                                            </td>
                                            <td class="px-3 py-3 text-center border-r border-slate-200">{{ $row->masa_kerja_formatted }}</td>
                                            <td class="px-3 py-3 text-center">
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $row->status_pegawai == 'Aktif' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $row->status_pegawai ?? 'Aktif' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-4 py-4 text-center text-slate-400">Tidak ada data PHL yang ditemukan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>