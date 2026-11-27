<x-app-layout>
    <x-slot name="header">
        <x-enterprise.page-header
            title="Daftar Urut Kepangkatan (DUK)"
            subtitle="Urutan hierarki kepangkatan dan masa kerja Aparatur Sipil Negara & Tenaga Penunjang" />
    </x-slot>

    <div class="py-6">
        <div class="w-full max-w-[1920px] mx-auto space-y-6 px-3 sm:px-6 lg:px-8 xl:px-10">

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

            @php
                $isExecutiveView = isset($isExecutive) ? (bool) $isExecutive : Auth::user()->canAccessExecutiveKepegawaianMenus();
                $cntDosenPns   = method_exists($dosenPnsList, 'total') ? $dosenPnsList->total() : count($dosenPnsList);
                $cntDosenPppk  = method_exists($dosenPppkList, 'total') ? $dosenPppkList->total() : count($dosenPppkList);
                $cntTendikPns  = method_exists($tendikPnsList, 'total') ? $tendikPnsList->total() : count($tendikPnsList);
                $cntTendikPppk = method_exists($tendikPppkList, 'total') ? $tendikPppkList->total() : count($tendikPppkList);
                $cntPhl        = method_exists($phlList, 'total') ? $phlList->total() : count($phlList);
            @endphp

            {{-- Ringkasan Statistik DUK Interaktif (Khusus Admin & Pimpinan / Eksekutif) --}}
            @if($isExecutiveView)
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                <x-enterprise.stat-card
                    title="DOSEN PNS"
                    :value="$statistics['dosen_pns']"
                    color="purple"
                    icon="academic"
                    :href="route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'dosen_pns' ? '' : 'dosen_pns']))"
                    :active="$filter === 'dosen_pns'"
                />

                <x-enterprise.stat-card
                    title="DOSEN PPPK"
                    :value="$statistics['dosen_pppk']"
                    color="purple"
                    icon="academic"
                    :href="route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'dosen_pppk' ? '' : 'dosen_pppk']))"
                    :active="$filter === 'dosen_pppk'"
                />

                <x-enterprise.stat-card
                    title="TENDIK PNS"
                    :value="$statistics['tendik_pns']"
                    color="green"
                    icon="briefcase"
                    :href="route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'tendik_pns' ? '' : 'tendik_pns']))"
                    :active="$filter === 'tendik_pns'"
                />

                <x-enterprise.stat-card
                    title="TENDIK PPPK"
                    :value="$statistics['tendik_pppk']"
                    color="amber"
                    icon="user-group"
                    :href="route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'tendik_pppk' ? '' : 'tendik_pppk']))"
                    :active="$filter === 'tendik_pppk'"
                />

                <x-enterprise.stat-card
                    title="PHL / HONOR"
                    :value="$statistics['phl']"
                    color="red"
                    icon="clipboard"
                    :href="route('duk.index', array_merge(request()->except('filter'), ['filter' => $filter === 'phl' ? '' : 'phl']))"
                    :active="$filter === 'phl'"
                />
            </div>
            @endif

            {{-- Card Utama DUK --}}
            <x-enterprise.card>
                <div class="p-6 space-y-6">
                    
                    @if($isExecutiveView)
                        {{-- Toolbar Pencarian & Tombol Export (Tanpa Judul Dobel) --}}
                        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-slate-200">
                            <form method="GET" action="{{ route('duk.index') }}" class="flex-1 min-w-[280px]">
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
                    @else
                        {{-- Pemberitahuan Privasi Data ASN untuk Akun Pegawai --}}
                        <div class="flex items-start gap-3 rounded-lg border border-blue-200 bg-blue-50/70 p-4 text-blue-900">
                            <svg class="h-5 w-5 text-blue-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-xs space-y-1">
                                <p class="font-bold text-sm text-blue-800">Pemberitahuan Privasi Data ASN</p>
                                <p class="text-slate-600">
                                    Sesuai regulasi perlindungan data pribadi ASN, Anda hanya dapat melihat data dan urutan hierarki DUK pada kelompok kepegawaian Anda. Nomor urut yang tertera pada kolom <strong>No</strong> merupakan peringkat resmi Anda di dalam daftar hierarki kepangkatan fakultas.
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 1. TABEL DUK DOSEN PNS                                                    --}}
                    {{-- ========================================================================= --}}
                    @if(($isExecutiveView && (!$filter || $filter === 'dosen_pns')) || (!$isExecutiveView && $cntDosenPns > 0))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-blue-50 border-l-4 border-blue-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-blue-900">👨‍🏫 {{ $isExecutiveView ? 'I. ' : '' }}DAFTAR URUT KEPANGKATAN DOSEN PNS</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-blue-600 text-white rounded-full">{{ $cntDosenPns }} Orang</span>
                            </div>
                            <span class="text-xs text-blue-700 font-medium">Tenaga Pendidik PNS</span>
                        </div>

                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3 text-left">Nama / NIP / NIDN</th>
                                    <th class="px-4 py-3 text-center">Gol / Pangkat</th>
                                    <th class="px-4 py-3 text-left">Unit Kerja &amp; Jabatan</th>
                                    <th class="px-4 py-3 text-center">Pendidikan</th>
                                    <th class="px-4 py-3 text-center">TMT Pangkat<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">Masa Kerja</th>
                                    <th class="px-4 py-3 text-center">Satyalancana</th>
                                    <th class="w-24 px-4 py-3 text-center">Status</th>
                                </tr>
                            </x-slot>

                            @forelse($dosenPnsList as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-medium text-slate-600">{{ $row->official_duk_rank ?? $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        @if(Auth::user()->hasRole(['admin', 'pimpinan']) || (int)Auth::user()->pegawai_id === (int)$row->id)
                                            <a href="{{ route('pegawai.show', $row->id) }}" class="font-semibold text-slate-900 hover:text-blue-600 hover:underline">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-900">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </span>
                                        @endif
                                        <div class="text-xs text-slate-500 font-mono">NIP. {{ $row->nip ?? '-' }}</div>
                                        @if($row->nidn_nuptk)
                                            <div class="text-[11px] font-mono text-blue-600">NIDN: {{ $row->nidn_nuptk }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="font-bold text-slate-800">{{ $row->golongan?->nama_golongan ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->golongan?->nama_pangkat ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $row->unit_kerja_tampil }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->jabatan_tampil }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->pendidikan_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-blue-700 text-xs">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-emerald-700 text-xs">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->masa_kerja_formatted }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-700">{{ $row->satyalancana_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <x-enterprise.badge color="{{ $row->status_pegawai == 'Aktif' ? 'green' : 'blue' }}">
                                            {{ $row->status_pegawai ?? 'Aktif' }}
                                        </x-enterprise.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-slate-400">Tidak ada data Dosen PNS yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </x-enterprise.data-table>

                        @if(method_exists($dosenPnsList, 'links'))
                            <div class="mt-4">
                                {{ $dosenPnsList->links() }}
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 2. TABEL DUK DOSEN PPPK                                                   --}}
                    {{-- ========================================================================= --}}
                    @if(($isExecutiveView && (!$filter || $filter === 'dosen_pppk')) || (!$isExecutiveView && $cntDosenPppk > 0))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-indigo-50 border-l-4 border-indigo-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-indigo-900">🎓 {{ $isExecutiveView ? 'II. ' : '' }}DAFTAR URUT KEPANGKATAN DOSEN PPPK</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-indigo-600 text-white rounded-full">{{ $cntDosenPppk }} Orang</span>
                            </div>
                            <span class="text-xs text-indigo-700 font-medium">Tenaga Pendidik PPPK</span>
                        </div>

                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3 text-left">Nama / NIP / NIDN</th>
                                    <th class="px-4 py-3 text-center">Gol / Pangkat</th>
                                    <th class="px-4 py-3 text-left">Unit Kerja &amp; Jabatan</th>
                                    <th class="px-4 py-3 text-center">Pendidikan</th>
                                    <th class="px-4 py-3 text-center">TMT Pangkat<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">Masa Kerja</th>
                                    <th class="px-4 py-3 text-center">Satyalancana</th>
                                    <th class="w-24 px-4 py-3 text-center">Status</th>
                                </tr>
                            </x-slot>

                            @forelse($dosenPppkList as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-medium text-slate-600">{{ $row->official_duk_rank ?? $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        @if(Auth::user()->hasRole(['admin', 'pimpinan']) || (int)Auth::user()->pegawai_id === (int)$row->id)
                                            <a href="{{ route('pegawai.show', $row->id) }}" class="font-semibold text-slate-900 hover:text-indigo-600 hover:underline">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-900">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </span>
                                        @endif
                                        <div class="text-xs text-slate-500 font-mono">NIP. {{ $row->nip ?? '-' }}</div>
                                        @if($row->nidn_nuptk)
                                            <div class="text-[11px] font-mono text-blue-600">NIDN: {{ $row->nidn_nuptk }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="font-bold text-slate-800">{{ $row->golongan?->nama_golongan ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->golongan?->nama_pangkat ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $row->unit_kerja_tampil }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->jabatan_tampil }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->pendidikan_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-blue-700 text-xs">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-emerald-700 text-xs">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->masa_kerja_formatted }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-700">{{ $row->satyalancana_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <x-enterprise.badge color="{{ $row->status_pegawai == 'Aktif' ? 'green' : 'blue' }}">
                                            {{ $row->status_pegawai ?? 'Aktif' }}
                                        </x-enterprise.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-slate-400">Tidak ada data Dosen PPPK yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </x-enterprise.data-table>

                        @if(method_exists($dosenPppkList, 'links'))
                            <div class="mt-4">
                                {{ $dosenPppkList->links() }}
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 3. TABEL DUK TENAGA KEPENDIDIKAN (TENDIK) PNS                             --}}
                    {{-- ========================================================================= --}}
                    @if(($isExecutiveView && (!$filter || $filter === 'tendik_pns')) || (!$isExecutiveView && $cntTendikPns > 0))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-emerald-50 border-l-4 border-emerald-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-emerald-900">🧑‍💼 {{ $isExecutiveView ? 'III. ' : '' }}DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PNS</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-emerald-600 text-white rounded-full">{{ $cntTendikPns }} Orang</span>
                            </div>
                            <span class="text-xs text-emerald-700 font-medium">Staf Administrasi / Laboran PNS</span>
                        </div>

                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3 text-left">Nama / NIP</th>
                                    <th class="px-4 py-3 text-center">Gol / Pangkat</th>
                                    <th class="px-4 py-3 text-left">Unit Kerja &amp; Jabatan</th>
                                    <th class="px-4 py-3 text-center">Pendidikan</th>
                                    <th class="px-4 py-3 text-center">TMT Pangkat<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">Masa Kerja</th>
                                    <th class="px-4 py-3 text-center">Satyalancana</th>
                                    <th class="w-24 px-4 py-3 text-center">Status</th>
                                </tr>
                            </x-slot>

                            @forelse($tendikPnsList as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-medium text-slate-600">{{ $row->official_duk_rank ?? $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        @if(Auth::user()->hasRole(['admin', 'pimpinan']) || (int)Auth::user()->pegawai_id === (int)$row->id)
                                            <a href="{{ route('pegawai.show', $row->id) }}" class="font-semibold text-slate-900 hover:text-emerald-700 hover:underline">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-900">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </span>
                                        @endif
                                        <div class="text-xs text-slate-500 font-mono">NIP. {{ $row->nip ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="font-bold text-slate-800">{{ $row->golongan?->nama_golongan ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->golongan?->nama_pangkat ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $row->unit_kerja_tampil }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->jabatan_tampil }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->pendidikan_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-blue-700 text-xs">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-emerald-700 text-xs">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->masa_kerja_formatted }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-700">{{ $row->satyalancana_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <x-enterprise.badge color="{{ $row->status_pegawai == 'Aktif' ? 'green' : 'blue' }}">
                                            {{ $row->status_pegawai ?? 'Aktif' }}
                                        </x-enterprise.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-slate-400">Tidak ada data Tendik PNS yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </x-enterprise.data-table>

                        @if(method_exists($tendikPnsList, 'links'))
                            <div class="mt-4">
                                {{ $tendikPnsList->links() }}
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 4. TABEL DUK TENAGA KEPENDIDIKAN (TENDIK) PPPK                            --}}
                    {{-- ========================================================================= --}}
                    @if(($isExecutiveView && (!$filter || $filter === 'tendik_pppk')) || (!$isExecutiveView && $cntTendikPppk > 0))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-teal-50 border-l-4 border-teal-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-teal-900">📑 {{ $isExecutiveView ? 'IV. ' : '' }}DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PPPK</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-teal-600 text-white rounded-full">{{ $cntTendikPppk }} Orang</span>
                            </div>
                            <span class="text-xs text-teal-700 font-medium">Staf Administrasi / Laboran PPPK</span>
                        </div>

                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3 text-left">Nama / NIP</th>
                                    <th class="px-4 py-3 text-center">Gol / Pangkat</th>
                                    <th class="px-4 py-3 text-left">Unit Kerja &amp; Jabatan</th>
                                    <th class="px-4 py-3 text-center">Pendidikan</th>
                                    <th class="px-4 py-3 text-center">TMT Pangkat<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">TMT KGB<br><span class="text-[10px] text-slate-500 font-normal">(Lama / Depan)</span></th>
                                    <th class="px-4 py-3 text-center">Masa Kerja</th>
                                    <th class="px-4 py-3 text-center">Satyalancana</th>
                                    <th class="w-24 px-4 py-3 text-center">Status</th>
                                </tr>
                            </x-slot>

                            @forelse($tendikPppkList as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-medium text-slate-600">{{ $row->official_duk_rank ?? $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        @if(Auth::user()->hasRole(['admin', 'pimpinan']) || (int)Auth::user()->pegawai_id === (int)$row->id)
                                            <a href="{{ route('pegawai.show', $row->id) }}" class="font-semibold text-slate-900 hover:text-teal-700 hover:underline">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-900">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </span>
                                        @endif
                                        <div class="text-xs text-slate-500 font-mono">NIP. {{ $row->nip ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="font-bold text-slate-800">{{ $row->golongan?->nama_golongan ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->golongan?->nama_pangkat ?? '' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $row->unit_kerja_tampil }}</div>
                                        <div class="text-xs text-slate-500">{{ $row->jabatan_tampil }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->pendidikan_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-blue-700 text-xs">{{ $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-600">{{ $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-' }}</span>
                                        <br>
                                        <strong class="text-emerald-700 text-xs">{{ $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-' }}</strong>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->masa_kerja_formatted }}</td>
                                    <td class="px-4 py-3 text-center font-medium text-slate-700">{{ $row->satyalancana_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <x-enterprise.badge color="{{ $row->status_pegawai == 'Aktif' ? 'green' : 'blue' }}">
                                            {{ $row->status_pegawai ?? 'Aktif' }}
                                        </x-enterprise.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-6 text-center text-slate-400">Tidak ada data Tendik PPPK yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </x-enterprise.data-table>

                        @if(method_exists($tendikPppkList, 'links'))
                            <div class="mt-4">
                                {{ $tendikPppkList->links() }}
                            </div>
                        @endif
                    </div>
                    @endif

                    {{-- ========================================================================= --}}
                    {{-- 5. TABEL DUK PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK                 --}}
                    {{-- ========================================================================= --}}
                    @if(($isExecutiveView && (!$filter || $filter === 'phl')) || (!$isExecutiveView && $cntPhl > 0))
                    <div class="space-y-3">
                        <div class="flex items-center justify-between bg-amber-50 border-l-4 border-amber-600 px-4 py-2.5 rounded-r-lg">
                            <div class="flex items-center gap-2">
                                <span class="text-base font-bold text-amber-900">👷 {{ $isExecutiveView ? 'V. ' : '' }}DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK</span>
                                <span class="px-2 py-0.5 text-xs font-bold bg-amber-600 text-white rounded-full">{{ $cntPhl }} Orang</span>
                            </div>
                            <span class="text-xs text-amber-700 font-medium">Non-ASN & Kontrak Kerja</span>
                        </div>

                        <x-enterprise.data-table>
                            <x-slot name="head">
                                <tr>
                                    <th class="w-12 px-4 py-3 text-center">No</th>
                                    <th class="px-4 py-3 text-left">Nama / NIK / NIP</th>
                                    <th class="px-4 py-3 text-left">Jenis Kontrak / Jabatan</th>
                                    <th class="px-4 py-3 text-left">Unit Kerja</th>
                                    <th class="px-4 py-3 text-center">Pendidikan</th>
                                    <th class="px-4 py-3 text-center">Periode Kontrak</th>
                                    <th class="px-4 py-3 text-center">Masa Kerja</th>
                                    <th class="w-24 px-4 py-3 text-center">Status</th>
                                </tr>
                            </x-slot>

                            @forelse($phlList as $row)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-4 py-3 text-center font-medium text-slate-600">{{ $row->official_duk_rank ?? $loop->iteration }}</td>
                                    <td class="px-4 py-3">
                                        @if(Auth::user()->hasRole(['admin', 'pimpinan']) || (int)Auth::user()->pegawai_id === (int)$row->id)
                                            <a href="{{ route('pegawai.show', $row->id) }}" class="font-semibold text-slate-900 hover:text-amber-800 hover:underline">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </a>
                                        @else
                                            <span class="font-semibold text-slate-900">
                                                {{ $row->nama_lengkap ?? $row->nama }}
                                            </span>
                                        @endif
                                        <div class="text-xs text-slate-500 font-mono">ID/NIP: {{ $row->nip ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-800">{{ $row->jabatan?->nama_jabatan ?? 'Tenaga Penunjang / PHL' }}</div>
                                        <div class="text-xs text-amber-700 font-medium">{{ $row->jenis_kontrak ?? 'Kontrak Harian / Tahunan' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $row->unit_kerja_tampil }}</td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->pendidikan_tampil }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-slate-700">{{ $row->tanggal_kontrak_mulai ? $row->tanggal_kontrak_mulai->format('d/m/Y') : ($row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-') }}</span>
                                        <span class="text-slate-400"> s/d </span>
                                        <span class="text-slate-700 font-semibold">{{ $row->tanggal_kontrak_selesai ? $row->tanggal_kontrak_selesai->format('d/m/Y') : 'Aktif' }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $row->masa_kerja_formatted }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <x-enterprise.badge color="{{ $row->status_pegawai == 'Aktif' ? 'green' : 'amber' }}">
                                            {{ $row->status_pegawai ?? 'Aktif' }}
                                        </x-enterprise.badge>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-slate-400">Tidak ada data PHL yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </x-enterprise.data-table>

                        @if(method_exists($phlList, 'links'))
                            <div class="mt-4">
                                {{ $phlList->links() }}
                            </div>
                        @endif
                    </div>
                    @endif

                    @if(!$isExecutiveView && ($cntDosenPns + $cntDosenPppk + $cntTendikPns + $cntTendikPppk + $cntPhl) === 0)
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-8 text-center text-slate-500 text-sm">
                            Akun Anda belum terhubung dengan data kepegawaian pada Daftar Urut Kepangkatan (DUK).
                        </div>
                    @endif

                </div>
            </x-enterprise.card>

        </div>
    </div>
</x-app-layout>