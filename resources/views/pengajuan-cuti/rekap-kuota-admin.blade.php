<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>📊</span> Rekapitulasi Kuota Cuti Pegawai (N, N-1, N-2)
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pengelolaan Saldo & Monitoring Kuota Cuti Tahunan Sesuai Peraturan BKN No. 24 Tahun 2017
                </p>
            </div>

            <div class="flex items-center gap-2" x-data="{ openBulkInitModal: false }">
                <button type="button" @click="openBulkInitModal = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                    <span>⚡</span> Inisialisasi Kuota Massal ({{ $tahun }})
                </button>
                <a href="{{ route('pengajuan-cuti.rekap-kuota.export-pdf', request()->query()) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-xs transition">
                    <span>📄</span> Ekspor Rekap PDF
                </a>
                <a href="{{ route('pengajuan-cuti.index') }}"
                   class="inline-flex items-center px-3.5 py-2 border border-gray-300 shadow-xs text-xs font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition">
                    ← Kembali ke E-Cuti
                </a>

                {{-- Modal Bulk Init --}}
                <div x-show="openBulkInitModal" x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
                     @keydown.escape.window="openBulkInitModal = false">
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 text-left border border-slate-200"
                         @click.away="openBulkInitModal = false">
                        <h3 class="text-base font-bold text-slate-800 mb-2 flex items-center gap-2">
                            <span>⚡</span> Inisialisasi Saldo Kuota Awal Otomatis
                        </h3>
                        <p class="text-xs text-slate-600 mb-4 leading-relaxed">
                            Fitur ini akan membuat record kuota cuti tahunan (default 12 hari kerja) untuk seluruh pegawai berstatus Aktif yang belum memiliki record kuota pada tahun anggaran terpilih.
                        </p>
                        <form action="{{ route('pengajuan-cuti.rekap-kuota.bulk-init') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tahun Anggaran</label>
                                <input type="number" name="tahun" value="{{ $tahun }}" required min="2020" max="2035"
                                       class="w-full rounded-lg border-slate-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Hak Kuota Awal (Hari Kerja)</label>
                                <input type="number" name="kuota_awal" value="12" required min="1" max="30"
                                       class="w-full rounded-lg border-slate-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                            </div>
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                                <button type="button" @click="openBulkInitModal = false"
                                        class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                    Batal
                                </button>
                                <button type="submit"
                                        class="px-4 py-1.5 text-xs font-bold rounded-lg bg-amber-600 hover:bg-amber-700 text-white shadow-xs transition">
                                    Proses Inisialisasi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        editModal: false,
        pegawaiId: '',
        pegawaiNama: '',
        tahunEdit: {{ $tahun }},
        kuotaAwal: 12,
        kuotaTerpakai: 0,
        catatan: '',
        openEdit(id, nama, thn, awal, terpakai, cat) {
            this.pegawaiId = id;
            this.pegawaiNama = nama;
            this.tahunEdit = thn;
            this.kuotaAwal = awal;
            this.kuotaTerpakai = terpakai;
            this.catatan = cat || '';
            this.editModal = true;
        }
    }">
        <div class="w-full max-w-[1920px] mx-auto px-3 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="rounded-lg border border-green-300 bg-green-50 px-5 py-3 text-green-700 flex items-center justify-between shadow-xs text-sm">
                    <span>✓ {{ session('success') }}</span>
                    <button type="button" class="font-bold hover:text-green-900" onclick="this.parentElement.remove()">×</button>
                </div>
            @endif

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 p-4">
                <form method="GET" action="{{ route('pengajuan-cuti.rekap-kuota') }}" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Cari Nama / NIP Pegawai</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama atau NIP..."
                               class="w-full rounded-lg border-gray-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Tahun Anggaran (N)</label>
                        <select name="tahun" class="w-full rounded-lg border-gray-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            @for($y = now()->year + 1; $y >= 2024; $y--)
                                <option value="{{ $y }}" @selected($tahun == $y)>Tahun {{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Unit Kerja</label>
                        <select name="unit_kerja_id" class="w-full rounded-lg border-gray-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Unit Kerja</option>
                            @foreach($unitKerjaList as $u)
                                <option value="{{ $u->id }}" @selected($unitKerjaId == $u->id)>{{ $u->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Pegawai</label>
                        <select name="jenis_pegawai" class="w-full rounded-lg border-gray-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                            <option value="">Semua Kategori</option>
                            <option value="Dosen" @selected($jenisPegawai === 'Dosen')>Dosen</option>
                            <option value="Tendik" @selected($jenisPegawai === 'Tendik')>Tenaga Kependidikan</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="flex-1 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-semibold py-2 transition">
                            Terapkan Filter
                        </button>
                        @if(request()->hasAny(['search', 'unit_kerja_id', 'jenis_pegawai']) || $tahun != now()->year)
                            <a href="{{ route('pengajuan-cuti.rekap-kuota') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs transition">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- TABEL REKAPITULASI --}}
            <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-xs text-left text-gray-600">
                        <thead class="text-[11px] uppercase bg-gray-50 text-gray-700 border-b border-gray-200 font-bold">
                            <tr>
                                <th class="py-3 px-3 text-center w-10">No</th>
                                <th class="py-3 px-3">Pegawai & NIP</th>
                                <th class="py-3 px-3">Unit Kerja / Jabatan</th>
                                <th class="py-3 px-3 text-center bg-blue-50/60 border-l border-r border-blue-100">
                                    {{ $tahun - 2 }} (N-2)
                                </th>
                                <th class="py-3 px-3 text-center bg-indigo-50/60 border-r border-indigo-100">
                                    {{ $tahun - 1 }} (N-1)
                                </th>
                                <th class="py-3 px-3 text-center bg-emerald-50/60 border-r border-emerald-100">
                                    {{ $tahun }} (N)
                                </th>
                                <th class="py-3 px-3 text-center bg-teal-50/80 font-black text-teal-900 border-r border-teal-100">
                                    Total Sisa Aktif
                                </th>
                                <th class="py-3 px-3 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($pegawaiList as $p)
                                @php
                                    $catatan = $p->getCatatanCutiTahunan($tahun);
                                    $sisaN   = $catatan['N']['sisa'] ?? 12;
                                    $sisaN1  = $catatan['N-1']['sisa'];
                                    $sisaN2  = $catatan['N-2']['sisa'];
                                    $totalAktif = $sisaN + ($sisaN1 ?? 0) + ($sisaN2 ?? 0);

                                    $recN  = $p->kuotaCuti->firstWhere('tahun', $tahun);
                                    $recN1 = $p->kuotaCuti->firstWhere('tahun', $tahun - 1);
                                    $recN2 = $p->kuotaCuti->firstWhere('tahun', $tahun - 2);
                                @endphp
                                <tr class="hover:bg-gray-50/70 transition">
                                    <td class="py-2.5 px-3 text-center font-mono text-gray-400">
                                        {{ ($pegawaiList->currentPage() - 1) * $pegawaiList->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-gray-900">{{ $p->nama_lengkap ?? $p->nama }}</div>
                                        <div class="text-[10px] text-gray-500 font-mono">NIP. {{ $p->nip ?? '-' }}</div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <div class="text-gray-800">{{ $p->unitKerja?->nama_unit ?? '-' }}</div>
                                        <div class="text-[10px] text-gray-500">{{ $p->jabatan?->nama_jabatan ?? '-' }}</div>
                                    </td>
                                    {{-- N-2 --}}
                                    <td class="py-2.5 px-3 text-center border-l border-r border-gray-100 font-mono">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="font-semibold {{ $sisaN2 !== null ? 'text-blue-700' : 'text-gray-400' }}">
                                                {{ $catatan['N-2']['sisa_formatted'] }}
                                            </span>
                                            <button type="button" title="Koreksi N-2"
                                                    @click="openEdit({{ $p->id }}, '{{ addslashes($p->nama_lengkap ?? $p->nama) }}', {{ $tahun - 2 }}, {{ $recN2?->kuota_awal ?? 12 }}, {{ $recN2?->kuota_terpakai ?? 0 }}, '{{ addslashes($recN2?->catatan_penyesuaian ?? '') }}')"
                                                    class="text-[10px] text-gray-400 hover:text-blue-600">✏️</button>
                                        </div>
                                    </td>
                                    {{-- N-1 --}}
                                    <td class="py-2.5 px-3 text-center border-r border-gray-100 font-mono">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="font-semibold {{ $sisaN1 !== null ? 'text-indigo-700' : 'text-gray-400' }}">
                                                {{ $catatan['N-1']['sisa_formatted'] }}
                                            </span>
                                            <button type="button" title="Koreksi N-1"
                                                    @click="openEdit({{ $p->id }}, '{{ addslashes($p->nama_lengkap ?? $p->nama) }}', {{ $tahun - 1 }}, {{ $recN1?->kuota_awal ?? 12 }}, {{ $recN1?->kuota_terpakai ?? 0 }}, '{{ addslashes($recN1?->catatan_penyesuaian ?? '') }}')"
                                                    class="text-[10px] text-gray-400 hover:text-indigo-600">✏️</button>
                                        </div>
                                    </td>
                                    {{-- N --}}
                                    <td class="py-2.5 px-3 text-center border-r border-gray-100 font-mono">
                                        <div class="flex items-center justify-center gap-1">
                                            <span class="font-bold text-emerald-700">
                                                {{ $catatan['N']['sisa_formatted'] }}
                                            </span>
                                            <button type="button" title="Koreksi N"
                                                    @click="openEdit({{ $p->id }}, '{{ addslashes($p->nama_lengkap ?? $p->nama) }}', {{ $tahun }}, {{ $recN?->kuota_awal ?? 12 }}, {{ $recN?->kuota_terpakai ?? 0 }}, '{{ addslashes($recN?->catatan_penyesuaian ?? '') }}')"
                                                    class="text-[10px] text-gray-400 hover:text-emerald-600">✏️</button>
                                        </div>
                                    </td>
                                    {{-- Total --}}
                                    <td class="py-2.5 px-3 text-center font-bold font-mono text-teal-800 bg-teal-50/40 border-r border-gray-100">
                                        {{ $totalAktif }} Hari
                                    </td>
                                    {{-- Aksi --}}
                                    <td class="py-2.5 px-3 text-center">
                                        <a href="{{ route('pengajuan-cuti.buku-cuti', ['pegawai_id' => $p->id, 'tahun' => $tahun]) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-[11px] transition" title="Lihat Buku Cuti">
                                            📖 Buku Cuti
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-400 italic">
                                        Tidak ada data pegawai yang sesuai dengan filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($pegawaiList->hasPages())
                    <div class="p-3 border-t border-gray-100">
                        {{ $pegawaiList->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Modal Penyesuaian Saldo Kuota --}}
        <div x-show="editModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
             @keydown.escape.window="editModal = false">
            <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 text-left border border-slate-200"
                 @click.away="editModal = false">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <span>✏️</span> Penyesuaian Saldo Kuota Cuti
                    </h3>
                    <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
                </div>
                <div class="p-3 rounded-lg bg-slate-50 border border-slate-200 mb-4 text-xs">
                    <div class="text-slate-500 uppercase font-semibold">Pegawai</div>
                    <div class="font-bold text-slate-900" x-text="pegawaiNama"></div>
                    <div class="text-slate-600 mt-0.5">Tahun Anggaran: <strong class="font-mono" x-text="tahunEdit"></strong></div>
                </div>
                <form action="{{ route('pengajuan-cuti.rekap-kuota.simpan') }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <input type="hidden" name="pegawai_id" :value="pegawaiId">
                    <input type="hidden" name="tahun" :value="tahunEdit">

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Hak Kuota Awal</label>
                            <input type="number" name="kuota_awal" x-model="kuotaAwal" required min="0" max="30"
                                   class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Cuti Terpakai</label>
                            <input type="number" name="kuota_terpakai" x-model="kuotaTerpakai" required min="0" max="30"
                                   class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>

                    <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-900 font-semibold flex items-center justify-between">
                        <span>Estimasi Sisa Kuota Definitif:</span>
                        <span class="font-mono font-bold text-sm" x-text="Math.max(0, (kuotaAwal - kuotaTerpakai)) + ' Hari'"></span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Penyesuaian / Dasar SK</label>
                        <input type="text" name="catatan_penyesuaian" x-model="catatan" placeholder="Contoh: Sisa cuti tahun 2025 sesuai kartu kendali fisik..."
                               class="w-full rounded-lg border-slate-300 text-xs focus:ring-emerald-500 focus:border-emerald-500">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="editModal = false"
                                class="px-3.5 py-1.5 font-semibold rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-1.5 font-bold rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
