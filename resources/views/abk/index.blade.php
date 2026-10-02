<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <span>🧮</span> Analisis Beban Kerja & Formasi Pegawai
                </h1>
                <p class="text-sm text-gray-600">
                    Kalkulasi Kebutuhan Formasi Pegawai Berdasarkan Standar Waktu Kerja Efektif (WKE: 1.250 Jam / 75.000 Menit/Tahun)
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-indigo-600 rounded-lg shadow hover:bg-indigo-700 transition">
                    <span>📊</span> Peta Jabatan
                </a>
                <a href="{{ route('abk.print.rekap', request()->query()) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg shadow hover:bg-emerald-700 transition">
                    <span>🖨️</span> Cetak Rekap Formasi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- 4 Kartu Statistik Agregat Formasi --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Kebutuhan Formasi (ABK)</div>
                        <div class="text-3xl font-black text-blue-600 mt-1">{{ $totalKebutuhan }} <span class="text-sm font-normal text-gray-500">Pegawai</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl font-bold">
                        📋
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Bezetting (Pegawai Riil)</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">{{ $totalBezetting }} <span class="text-sm font-normal text-gray-500">Pegawai</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-gray-50 text-gray-700 flex items-center justify-center text-xl font-bold">
                        👥
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-rose-600 uppercase tracking-wider">Kekurangan Pegawai</div>
                        <div class="text-3xl font-black text-rose-600 mt-1">{{ $totalKurang }} <span class="text-sm font-normal text-rose-400">Posisi</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold">
                        ⚠️
                    </div>
                </div>

                <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm flex items-center justify-between">
                    <div>
                        <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">Kelebihan Pegawai</div>
                        <div class="text-3xl font-black text-amber-600 mt-1">{{ $totalLebih }} <span class="text-sm font-normal text-amber-400">Posisi</span></div>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                        ℹ️
                    </div>
                </div>
            </div>

            {{-- Filter Unit Kerja --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <form method="GET" action="{{ route('abk.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3 flex-1 max-w-md">
                        <label class="text-xs font-bold text-gray-600 uppercase whitespace-nowrap">Filter Unit Kerja:</label>
                        <select name="unit_kerja_id" onchange="this.form.submit()" class="w-full text-sm rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Seluruh Unit Kerja Fakultas --</option>
                            @foreach($unitKerjas as $u)
                                <option value="{{ $u->id }}" {{ request('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->kode_unit }} - {{ $u->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('unit_kerja_id'))
                        <a href="{{ route('abk.index') }}" class="text-xs text-blue-600 hover:underline">
                            ✕ Hapus Filter
                        </a>
                    @endif
                </form>
            </div>

            @php
                $canManage = Auth::user()->canManageAnjabAbk();
                $itemsJson = $anjabs->values()->map(function($item, $index) use ($canManage) {
                    return [
                        'no'            => $index + 1,
                        'id'            => $item->id,
                        'nama_jabatan'  => $item->jabatan->nama_jabatan ?? '-',
                        'nama_unit'     => $item->unitKerja->nama_unit ?? 'Fakultas Keperawatan',
                        'grade'         => $item->kelas_jabatan ?? $item->jabatan?->kelas_jabatan ?? '-',
                        'butir_count'   => $item->uraianTugas->count(),
                        'total_jam'     => number_format($item->total_jam_beban, 1),
                        'kebutuhan_abk' => $item->kebutuhan_pegawai,
                        'formasi'       => (int) $item->formasi_pembulatan,
                        'bezetting'     => (int) $item->bezetting,
                        'selisih'       => (int) $item->selisih_formasi,
                        'is_defisit'    => $item->selisih_formasi < 0,
                        'is_ideal'      => $item->selisih_formasi == 0,
                        'is_lebih'      => $item->selisih_formasi > 0,
                        'url_edit'      => route('abk.edit', $item),
                        'url_show'      => route('anjab.show', $item),
                        'can_manage'    => $canManage,
                    ];
                });
            @endphp

            {{-- Tabel Matrix Formasi ABK dengan Paginasi & Kontrol Tampilan --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                 x-data="{
                     search: '',
                     statusFilter: 'all',
                     perPage: 10,
                     currentPage: 1,
                     items: {{ Js::from($itemsJson) }},
                     get filteredItems() {
                         let q = this.search.toLowerCase().trim();
                         let sf = this.statusFilter;
                         return this.items.filter(item => {
                             let matchSearch = !q || (
                                 item.nama_jabatan.toLowerCase().includes(q) ||
                                 item.nama_unit.toLowerCase().includes(q) ||
                                 ('grade ' + item.grade).toLowerCase().includes(q) ||
                                 String(item.no).includes(q)
                             );
                             let matchStatus = true;
                             if (sf === 'defisit') matchStatus = item.is_defisit;
                             if (sf === 'ideal') matchStatus = item.is_ideal;
                             if (sf === 'lebih') matchStatus = item.is_lebih;
                             return matchSearch && matchStatus;
                         });
                     },
                     get totalPages() {
                         if (this.perPage === 'all') return 1;
                         return Math.max(1, Math.ceil(this.filteredItems.length / parseInt(this.perPage)));
                     },
                     get paginatedItems() {
                         if (this.perPage === 'all') return this.filteredItems;
                         let p = parseInt(this.perPage);
                         let start = (this.currentPage - 1) * p;
                         return this.filteredItems.slice(start, start + p);
                     },
                     get pageStart() {
                         if (this.filteredItems.length === 0) return 0;
                         if (this.perPage === 'all') return 1;
                         return (this.currentPage - 1) * parseInt(this.perPage) + 1;
                     },
                     get pageEnd() {
                         if (this.filteredItems.length === 0) return 0;
                         if (this.perPage === 'all') return this.filteredItems.length;
                         return Math.min(this.filteredItems.length, this.currentPage * parseInt(this.perPage));
                     },
                     setPage(p) {
                         if (p >= 1 && p <= this.totalPages) {
                             this.currentPage = p;
                         }
                     },
                     prevPage() {
                         if (this.currentPage > 1) this.currentPage--;
                     },
                     nextPage() {
                         if (this.currentPage < this.totalPages) this.currentPage++;
                     },
                     resetFilters() {
                         this.search = '';
                         this.statusFilter = 'all';
                         this.currentPage = 1;
                     }
                 }">
                {{-- Header Box --}}
                <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="font-bold text-gray-800 text-base">TABEL PERHITUNGAN FORMASI & BEBAN KERJA JABATAN</h2>
                            <span class="text-xs font-bold text-blue-700 bg-blue-100 px-2.5 py-0.5 rounded-full" x-text="filteredItems.length + ' Jabatan'"></span>
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">Urutan hierarki organisasi (Dekan No. 1) & WKE standar: 1.250 Jam (75.000 Menit/Tahun)</p>
                    </div>

                    {{-- Pilihan Jumlah Tampilan Baris --}}
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-gray-500 font-medium">Tampilkan per hal:</span>
                        <div class="inline-flex rounded-lg border border-gray-300 bg-white p-0.5 shadow-2xs">
                            <button type="button" @click="perPage = 10; currentPage = 1"
                                    :class="perPage === 10 ? 'bg-blue-600 text-white font-bold' : 'text-gray-600 hover:text-gray-900'"
                                    class="px-2.5 py-1 text-xs rounded-md transition">10</button>
                            <button type="button" @click="perPage = 15; currentPage = 1"
                                    :class="perPage === 15 ? 'bg-blue-600 text-white font-bold' : 'text-gray-600 hover:text-gray-900'"
                                    class="px-2.5 py-1 text-xs rounded-md transition">15</button>
                            <button type="button" @click="perPage = 25; currentPage = 1"
                                    :class="perPage === 25 ? 'bg-blue-600 text-white font-bold' : 'text-gray-600 hover:text-gray-900'"
                                    class="px-2.5 py-1 text-xs rounded-md transition">25</button>
                            <button type="button" @click="perPage = 'all'; currentPage = 1"
                                    :class="perPage === 'all' ? 'bg-blue-600 text-white font-bold' : 'text-gray-600 hover:text-gray-900'"
                                    class="px-2.5 py-1 text-xs rounded-md transition">Semua</button>
                        </div>
                    </div>
                </div>

                {{-- Toolbar Pencarian Cepat & Filter Status --}}
                <div class="p-3.5 bg-slate-50/70 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                    {{-- Tab Status Cepat --}}
                    <div class="flex flex-wrap items-center gap-1.5 text-xs">
                        <button type="button" @click="statusFilter = 'all'; currentPage = 1"
                                :class="statusFilter === 'all' ? 'bg-gray-800 text-white font-bold shadow-2xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-200'"
                                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                            <span>📋 Semua</span>
                            <span class="opacity-80" x-text="'(' + items.length + ')'"></span>
                        </button>
                        <button type="button" @click="statusFilter = 'defisit'; currentPage = 1"
                                :class="statusFilter === 'defisit' ? 'bg-rose-600 text-white font-bold shadow-2xs' : 'bg-white text-rose-700 hover:bg-rose-50 border border-rose-200'"
                                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                            <span>🔴 Kurang Pegawai</span>
                            <span class="opacity-80" x-text="'(' + items.filter(i => i.is_defisit).length + ')'"></span>
                        </button>
                        <button type="button" @click="statusFilter = 'ideal'; currentPage = 1"
                                :class="statusFilter === 'ideal' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-white text-emerald-700 hover:bg-emerald-50 border border-emerald-200'"
                                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                            <span>🟢 Ideal (Cukup)</span>
                            <span class="opacity-80" x-text="'(' + items.filter(i => i.is_ideal).length + ')'"></span>
                        </button>
                        <button type="button" x-show="items.filter(i => i.is_lebih).length > 0"
                                @click="statusFilter = 'lebih'; currentPage = 1"
                                :class="statusFilter === 'lebih' ? 'bg-amber-600 text-white font-bold shadow-2xs' : 'bg-white text-amber-700 hover:bg-amber-50 border border-amber-200'"
                                class="px-3 py-1.5 rounded-lg transition flex items-center gap-1">
                            <span>🟡 Lebih</span>
                            <span class="opacity-80" x-text="'(' + items.filter(i => i.is_lebih).length + ')'"></span>
                        </button>
                    </div>

                    {{-- Input Pencarian Langsung (Live Search) --}}
                    <div class="relative w-full md:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400 text-xs">
                            🔍
                        </span>
                        <input type="text"
                               x-model="search"
                               @input="currentPage = 1"
                               placeholder="Cari jabatan, unit, grade..."
                               class="w-full pl-8 pr-7 py-1.5 text-xs bg-white rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-2xs">
                        <button type="button"
                                x-show="search.length > 0"
                                @click="search = ''; currentPage = 1"
                                class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 text-xs">
                            ✕
                        </button>
                    </div>
                </div>

                {{-- Scroll Container yang Terkontrol (Max Height + Sticky Header) --}}
                <div class="overflow-x-auto max-h-[580px] overflow-y-auto">
                    <table class="w-full text-left text-sm text-gray-700">
                        <thead class="bg-gray-100 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase sticky top-0 z-10 shadow-2xs">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">No</th>
                                <th class="px-4 py-3">Nama Jabatan & Unit Kerja</th>
                                <th class="px-4 py-3 text-center">Grade</th>
                                <th class="px-4 py-3 text-center">Butir Tugas</th>
                                <th class="px-4 py-3 text-center">Total JKE (Jam)</th>
                                <th class="px-4 py-3 text-center">Kebutuhan (ABK)</th>
                                <th class="px-4 py-3 text-center bg-blue-50 text-blue-900">Formasi</th>
                                <th class="px-4 py-3 text-center bg-gray-50 text-gray-900">Bezetting</th>
                                <th class="px-4 py-3 text-center">Selisih</th>
                                <th class="px-4 py-3 text-center">Status Formasi</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <template x-for="item in paginatedItems" :key="item.id">
                                <tr class="hover:bg-blue-50/40 transition">
                                    <td class="px-4 py-2.5 text-center font-bold text-gray-500 text-xs" x-text="item.no"></td>
                                    <td class="px-4 py-2.5">
                                        <div class="font-bold text-gray-900" x-text="item.nama_jabatan"></div>
                                        <div class="text-xs text-gray-500" x-text="item.nama_unit"></div>
                                    </td>
                                    <td class="px-4 py-2.5 text-center">
                                        <span class="px-2 py-0.5 text-xs font-bold rounded bg-amber-50 text-amber-800 border border-amber-200"
                                              x-text="item.grade"></span>
                                    </td>
                                    <td class="px-4 py-2.5 text-center font-semibold text-gray-700" x-text="item.butir_count"></td>
                                    <td class="px-4 py-2.5 text-center font-mono font-medium text-gray-800" x-text="item.total_jam"></td>
                                    <td class="px-4 py-2.5 text-center font-mono font-semibold text-blue-700" x-text="item.kebutuhan_abk"></td>
                                    <td class="px-4 py-2.5 text-center font-mono font-black text-blue-900 bg-blue-50/60 text-base" x-text="item.formasi"></td>
                                    <td class="px-4 py-2.5 text-center font-mono font-black text-gray-900 bg-gray-50 text-base" x-text="item.bezetting"></td>
                                    <td class="px-4 py-2.5 text-center font-mono font-bold"
                                        :class="item.selisih < 0 ? 'text-rose-600' : (item.selisih > 0 ? 'text-amber-600' : 'text-emerald-600')"
                                        x-text="(item.selisih > 0 ? '+' : '') + item.selisih"></td>
                                    <td class="px-4 py-2.5 text-center whitespace-nowrap">
                                        <template x-if="item.is_defisit">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800"
                                                  x-text="'🔴 Kurang ' + Math.abs(item.selisih)"></span>
                                        </template>
                                        <template x-if="item.is_lebih">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800"
                                                  x-text="'🟡 Lebih +' + item.selisih"></span>
                                        </template>
                                        <template x-if="item.is_ideal">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                                                🟢 Ideal (Cukup)
                                            </span>
                                        </template>
                                    </td>
                                    <td class="px-4 py-2.5 text-right space-x-1 whitespace-nowrap">
                                        <template x-if="item.can_manage">
                                            <a :href="item.url_edit" class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100 transition" title="Kelola Butir Tugas ABK">
                                                ✏️ Tugas
                                            </a>
                                        </template>
                                        <template x-if="!item.can_manage">
                                            <a :href="item.url_edit" class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded bg-slate-100 text-slate-700 border border-slate-200 hover:bg-slate-200 transition" title="Lihat Rincian Tugas ABK">
                                                📄 Rincian
                                            </a>
                                        </template>
                                        <a :href="item.url_show" class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 transition" title="Detail Anjab">
                                            👁️
                                        </a>
                                    </td>
                                </tr>
                            </template>

                            {{-- Pesan Ketika Tidak Ada Data Sesuai Filter --}}
                            <tr x-show="filteredItems.length === 0">
                                <td colspan="11" class="px-6 py-12 text-center text-gray-500">
                                    <div class="text-3xl mb-2">🔍</div>
                                    <div class="font-bold text-gray-800">Tidak ada jabatan yang sesuai dengan filter atau kata kunci.</div>
                                    <p class="text-xs text-gray-500 mt-1">Coba gunakan kata kunci lain atau reset filter pencarian.</p>
                                    <button type="button" @click="resetFilters()" class="mt-3 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 font-semibold text-xs border border-blue-200 hover:bg-blue-100 transition">
                                        &larr; Tampilkan Semua Data
                                    </button>
                                </td>
                            </tr>
                        </tbody>

                        @if($anjabs->isNotEmpty())
                            <tfoot class="bg-gray-100 border-t-2 border-gray-300 font-bold text-gray-900 text-xs sticky bottom-0 z-10 shadow-2xs">
                                <tr>
                                    <td colspan="6" class="px-4 py-2.5 text-right uppercase">TOTAL KESELURUHAN ({{ $anjabs->count() }} JABATAN):</td>
                                    <td class="px-4 py-2.5 text-center font-mono font-black text-blue-900 text-sm bg-blue-100">{{ $totalKebutuhan }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono font-black text-gray-900 text-sm bg-gray-200">{{ $totalBezetting }}</td>
                                    <td class="px-4 py-2.5 text-center font-mono font-bold {{ ($totalBezetting - $totalKebutuhan) < 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                        {{ ($totalBezetting - $totalKebutuhan) > 0 ? '+' : '' }}{{ $totalBezetting - $totalKebutuhan }}
                                    </td>
                                    <td colspan="2" class="px-4 py-2.5 text-center text-[11px] text-gray-600 font-normal">
                                        Defisit: <span class="font-bold text-rose-600">{{ $totalKurang }}</span> | Lebih: <span class="font-bold text-amber-600">{{ $totalLebih }}</span>
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>

                {{-- Bar Paginasi Interaktif (Bawah Tabel) --}}
                <div class="px-6 py-3.5 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs"
                     x-show="filteredItems.length > 0">
                    <div class="text-gray-600">
                        Menampilkan <span class="font-bold text-gray-900" x-text="pageStart"></span> - <span class="font-bold text-gray-900" x-text="pageEnd"></span> dari <span class="font-bold text-gray-900" x-text="filteredItems.length"></span> jabatan
                        <template x-if="perPage !== 'all'">
                            <span class="text-gray-400 font-normal">(Halaman <span x-text="currentPage"></span> dari <span x-text="totalPages"></span>)</span>
                        </template>
                    </div>

                    {{-- Tombol Navigasi Halaman --}}
                    <div class="flex items-center gap-1.5" x-show="totalPages > 1 && perPage !== 'all'">
                        <button type="button" @click="prevPage()" :disabled="currentPage === 1"
                                :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-900 border border-gray-300 shadow-2xs'"
                                class="px-3 py-1.5 rounded-lg font-semibold transition">
                            &laquo; Sebelumnya
                        </button>

                        <template x-for="p in totalPages" :key="p">
                            <button type="button" @click="setPage(p)"
                                    :class="currentPage === p ? 'bg-blue-600 text-white font-bold shadow-2xs' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300 shadow-2xs'"
                                    class="w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition"
                                    x-text="p">
                            </button>
                        </template>

                        <button type="button" @click="nextPage()" :disabled="currentPage === totalPages"
                                :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-white text-gray-700 hover:bg-gray-100 hover:text-gray-900 border border-gray-300 shadow-2xs'"
                                class="px-3 py-1.5 rounded-lg font-semibold transition">
                            Selanjutnya &raquo;
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
