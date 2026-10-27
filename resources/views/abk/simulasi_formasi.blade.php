<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('abk.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-700 flex items-center gap-1">
                        &larr; Kembali ke Analisis Beban Kerja
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>🎯</span> Simulasi &amp; Usulan Formasi CASN / PPPK
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Integrasi Kebutuhan ABK (WKE 1.250 Jam) &amp; Radar Pensiun BUP untuk Pengajuan E-Formasi KemenPAN-RB
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Tombol SOP E-Formasi & Radar Pensiun --}}
                <x-sop-modal title="SOP Simulasi & Pengusulan E-Formasi CASN / PPPK" 
                             buttonLabel="SOP E-Formasi" 
                             badge="PermenPAN & BUP" 
                             color="amber">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200">
                            <h4 class="font-bold text-amber-950 text-xs mb-1 flex items-center gap-1.5">
                                <x-icon name="target" class="w-4 h-4 text-amber-700" />
                                Ketentuan Usulan E-Formasi MenPAN-RB
                            </h4>
                            <p class="text-amber-900 text-[11px] leading-relaxed">
                                Pengusulan formasi CASN (CPNS &amp; PPPK) didasarkan pada selisih kebutuhan riil ABK ditambah antisipasi lowongan jabatan akibat Batas Usia Pensiun (BUP) dalam 1–5 tahun mendatang.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-amber-600 text-white flex items-center justify-center text-[10px]">1</span>
                                Ketentuan Batas Usia Pensiun (BUP)
                            </h4>
                            <div class="space-y-1.5 ps-2 border-l-2 border-amber-200 text-[11px] text-slate-600">
                                <div>&bull; <strong>Dosen (Asisten Ahli, Lektor, Lektor Kepala):</strong> BUP 65 Tahun.</div>
                                <div>&bull; <strong>Dosen (Guru Besar / Profesor):</strong> BUP 70 Tahun.</div>
                                <div>&bull; <strong>Pejabat Administrator / Madya:</strong> BUP 60 Tahun.</div>
                                <div>&bull; <strong>Tenaga Kependidikan Pelaksana / Terampil:</strong> BUP 58 Tahun.</div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px]">2</span>
                                Alokasi Jenis Formasi (CPNS vs PPPK)
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-purple-200 text-[11px] text-slate-600">
                                <div class="bg-slate-50 p-2 rounded-xl">
                                    <strong class="text-slate-800 block text-xs">CPNS (Pegawai Negeri Sipil)</strong>
                                    Diprioritaskan untuk regenerasi dosen tetap akademik, pranata laboratorium pendidikan, dan jabatan fungsional keahlian utama.
                                </div>
                                <div class="bg-slate-50 p-2 rounded-xl">
                                    <strong class="text-slate-800 block text-xs">PPPK (Pegawai Pemerintah dengan Perjanjian Kerja)</strong>
                                    Dialokasikan untuk tenaga profesional pelayanan klinis keperawatan, tenaga teknis operasional, dan staf administrasi pendukung.
                                </div>
                            </div>
                        </div>
                    </div>
                </x-sop-modal>

                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-purple-600 rounded-xl shadow-2xs hover:bg-purple-700 transition">
                    <span>🖨️</span> Cetak Lembar Simulasi
                </button>
                <a href="{{ route('anjab.peta-jabatan') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 shadow-2xs transition">
                    <span>📊</span> Peta Jabatan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- 5 Kartu Statistik Agregat --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Kebutuhan ABK</div>
                <div class="text-2xl font-black text-blue-600 mt-1">{{ $totalKebutuhan }} <span class="text-xs font-normal text-gray-500">Formasi</span></div>
                <div class="text-[10px] text-gray-400 mt-1">Standar WKE MenPAN</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Bezetting Riil</div>
                <div class="text-2xl font-black text-gray-900 mt-1">{{ $totalBezetting }} <span class="text-xs font-normal text-gray-500">Pegawai</span></div>
                <div class="text-[10px] text-gray-400 mt-1">Pegawai Aktif Saat Ini</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-rose-200 bg-rose-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Defisit Formasi</div>
                <div class="text-2xl font-black text-rose-600 mt-1">{{ $totalDefisit }} <span class="text-xs font-normal text-rose-400">Lowong</span></div>
                <div class="text-[10px] text-rose-500 mt-1">Kekurangan Beban Kerja</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-amber-200 bg-amber-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Radar Pensiun BUP</div>
                <div class="text-2xl font-black text-amber-600 mt-1">{{ $totalPensiun1Thn }} <span class="text-xs font-normal text-amber-700">1 Thn</span> &bull; <span class="text-lg text-gray-600">{{ $totalPensiun3Thn }}</span> <span class="text-xs font-normal text-gray-500">3 Thn</span></div>
                <div class="text-[10px] text-amber-700 mt-1">Proyeksi BUP Dosen &amp; Tendik</div>
            </div>

            <div class="bg-white rounded-2xl p-4 border border-purple-200 bg-purple-50/40 shadow-sm col-span-2 lg:col-span-1">
                <div class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Usulan CASN Baru</div>
                <div class="text-2xl font-black text-purple-700 mt-1">{{ $totalUsulanPns + $totalUsulanPppk }} <span class="text-xs font-normal text-purple-500">Formasi</span></div>
                <div class="text-[10px] text-purple-600 mt-1">CPNS: <b>{{ $totalUsulanPns }}</b> | PPPK: <b>{{ $totalUsulanPppk }}</b></div>
            </div>
        </div>

        {{-- Filter Unit Kerja --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
            <form method="GET" action="{{ route('abk.simulasi-formasi') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="w-full sm:w-auto text-xs font-bold text-gray-700 flex items-center gap-1.5 shrink-0">
                    <span>🔍</span> Filter Unit Kerja:
                </div>
                <select name="unit_kerja_id" onchange="this.form.submit()" class="w-full sm:w-72 text-xs rounded-xl border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                    <option value="">Semua Unit Kerja Fakultas</option>
                    @foreach($unitKerjas as $u)
                        <option value="{{ $u->id }}" {{ request('unit_kerja_id') == $u->id ? 'selected' : '' }}>
                            {{ $u->nama_unit }}
                        </option>
                    @endforeach
                </select>
                @if(request()->filled('unit_kerja_id'))
                    <a href="{{ route('abk.simulasi-formasi') }}" class="text-xs text-purple-600 hover:text-purple-800 font-semibold underline">
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Matriks Simulasi Formasi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider">Matriks Analisis Kebutuhan &amp; Usulan Formasi CASN</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Dihitung otomatis dari beban kerja jam efektif tahunan &amp; tanggal lahir pegawai ASN.</p>
                </div>
                <span class="text-xs font-mono bg-purple-100 text-purple-800 font-bold px-2.5 py-1 rounded-lg">
                    Total {{ $simulationData->count() }} Jabatan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-gray-200">
                    <thead class="bg-slate-50 text-gray-600 font-black uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-10">No</th>
                            <th class="px-4 py-3.5">Nama Jabatan &amp; Unit</th>
                            <th class="px-3 py-3.5 text-center">Kelas</th>
                            <th class="px-3 py-3.5 text-center">Kebutuhan (ABK)</th>
                            <th class="px-3 py-3.5 text-center">Bezetting</th>
                            <th class="px-3 py-3.5 text-center">Selisih</th>
                            <th class="px-3 py-3.5 text-center">Radar Pensiun (1-3 Thn)</th>
                            <th class="px-3 py-3.5 text-center bg-purple-50/60 text-purple-900">Usulan CPNS</th>
                            <th class="px-3 py-3.5 text-center bg-purple-50/60 text-purple-900">Usulan PPPK</th>
                            <th class="px-3 py-3.5 text-center">Prioritas</th>
                            <th class="px-3 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                        @forelse($simulationData as $idx => $row)
                            <tr class="hover:bg-purple-50/30 transition {{ $row['selisih'] < 0 ? 'bg-rose-50/15' : '' }}">
                                <td class="px-4 py-3 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 text-xs">{{ $row['anjab']->jabatan?->nama_jabatan ?? '-' }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5">
                                        <span>🏢 {{ $row['anjab']->unitKerja?->nama_unit ?? 'Fakultas' }}</span>
                                        <span class="text-gray-300">&bull;</span>
                                        <span class="font-mono text-gray-400">{{ $row['anjab']->kode_anjab ?? '-' }}</span>
                                    </div>

                                    {{-- Daftar nama yang akan pensiun jika ada --}}
                                    @if(!empty($row['pegawai_pensiun_list']))
                                        <div class="mt-1.5 p-1.5 rounded-lg bg-amber-50 border border-amber-200 text-[10px] text-amber-900">
                                            <span class="font-bold">⚠️ Pegawai Masuk Masa Pensiun BUP:</span>
                                            <ul class="list-disc list-inside mt-0.5 space-y-0.5">
                                                @foreach($row['pegawai_pensiun_list'] as $pensiun)
                                                    <li>
                                                        <b>{{ $pensiun['pegawai']?->nama ?? '-' }}</b> (NIP. {{ $pensiun['pegawai']?->nip ?? '-' }}) &bull; TMT BUP: {{ $pensiun['tgl_pensiun'] }} [{{ $pensiun['sisa'] }}]
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center font-mono font-bold text-amber-700">
                                    {{ $row['anjab']->kelas_jabatan ?? $row['anjab']->jabatan?->kelas_jabatan ?? '-' }}
                                </td>
                                <td class="px-3 py-3 text-center font-black text-blue-700 text-sm">
                                    {{ $row['kebutuhan'] }}
                                </td>
                                <td class="px-3 py-3 text-center font-black text-gray-900 text-sm">
                                    {{ $row['bezetting'] }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($row['selisih'] < 0)
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-rose-100 text-rose-800">
                                            Kurang ({{ abs($row['selisih']) }})
                                        </span>
                                    @elseif($row['selisih'] > 0)
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-100 text-amber-800">
                                            Lebih (+{{ $row['selisih'] }})
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                            Cukup
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center font-mono">
                                    @if($row['pensiun_1_thn'] > 0)
                                        <span class="px-1.5 py-0.5 rounded font-black text-[11px] bg-rose-100 text-rose-800" title="{{ $row['pensiun_1_thn'] }} Pegawai pensiun dalam 1 tahun">
                                            🚨 {{ $row['pensiun_1_thn'] }} Org (1 Thn)
                                        </span>
                                    @elseif($row['pensiun_3_thn'] > 0)
                                        <span class="px-1.5 py-0.5 rounded font-bold text-[10px] bg-amber-100 text-amber-800" title="{{ $row['pensiun_3_thn'] }} Pegawai pensiun dalam 3 tahun">
                                            ⏳ {{ $row['pensiun_3_thn'] }} Org (3 Thn)
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center bg-purple-50/40 font-black text-sm text-purple-900">
                                    {{ $row['usulan_pns'] > 0 ? $row['usulan_pns'] : '-' }}
                                </td>
                                <td class="px-3 py-3 text-center bg-purple-50/40 font-black text-sm text-purple-900">
                                    {{ $row['usulan_pppk'] > 0 ? $row['usulan_pppk'] : '-' }}
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($row['prioritas'] === 'Mendesak / Sangat Tinggi')
                                        <span class="px-2 py-0.5 rounded-full font-black text-[10px] bg-rose-600 text-white shadow-xs">
                                            Mendesak
                                        </span>
                                    @elseif($row['prioritas'] === 'Tinggi')
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-500 text-white shadow-xs">
                                            Tinggi
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full font-semibold text-[10px] bg-slate-100 text-slate-700">
                                            Normal
                                        </span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('abk.edit', $row['anjab']->id) }}" class="text-blue-600 hover:text-blue-800 font-bold" title="Detail Butir Beban Kerja">
                                            🧮
                                        </a>
                                        <a href="{{ route('anjab.show', $row['anjab']->id) }}" class="text-purple-600 hover:text-purple-800 font-bold" title="Detail Dokumen Anjab">
                                            📑
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-6 py-8 text-center text-gray-500 text-xs">
                                    Belum ada data analisis beban kerja jabatan yang tersimpan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-slate-100 text-gray-900 font-black text-xs">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right uppercase tracking-wider">Total Rekapitulasi:</td>
                            <td class="px-3 py-3 text-center font-mono text-blue-700">{{ $totalKebutuhan }}</td>
                            <td class="px-3 py-3 text-center font-mono">{{ $totalBezetting }}</td>
                            <td class="px-3 py-3 text-center font-mono text-rose-700">-{{ $totalDefisit }}</td>
                            <td class="px-3 py-3 text-center font-mono text-amber-700">{{ $totalPensiun1Thn }} (1Thn) / {{ $totalPensiun3Thn }} (3Thn)</td>
                            <td class="px-3 py-3 text-center font-mono text-purple-900 bg-purple-100">{{ $totalUsulanPns }}</td>
                            <td class="px-3 py-3 text-center font-mono text-purple-900 bg-purple-100">{{ $totalUsulanPppk }}</td>
                            <td colspan="2" class="px-3 py-3 text-center text-purple-900 font-bold">Total: {{ $totalUsulanPns + $totalUsulanPppk }} CASN</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Panduan & Dasar Hukum E-Formasi --}}
        <div class="bg-gradient-to-r from-purple-50 via-indigo-50 to-white rounded-2xl p-5 border border-purple-200 text-xs text-gray-700 space-y-2">
            <h4 class="font-black text-purple-950 flex items-center gap-1.5 uppercase tracking-wider text-xs">
                <span>ℹ️</span> Dasar Kebijakan Penetapan Kebutuhan &amp; E-Formasi ASN
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] leading-relaxed">
                <div>
                    <p><b>1. PermenPAN-RB No. 1 Tahun 2020:</b> Pedoman Analisis Jabatan dan Analisis Beban Kerja menetapkan rumus formasi berbasis Jam Kerja Efektif tahunan (1.250 jam atau 75.000 menit kerja).</p>
                    <p class="mt-1"><b>2. UU No. 20 Tahun 2023 (UU ASN):</b> Penataan pegawai non-ASN dialihkan melalui mekanisme seleksi CASN (CPNS dan PPPK) berbasis integritas dan meritokrasi.</p>
                </div>
                <div>
                    <p><b>3. Perhitungan Radar Pensiun (BUP):</b> Batas Usia Pensiun Dosen dihitung 65 tahun (Guru Besar 70 tahun), Pejabat Administrator/Madya 60 tahun, dan Tenaga Kependidikan Pelaksana 58 tahun.</p>
                    <p class="mt-1"><b>4. Usulan Formasi:</b> Prioritas pengusulan dialokasikan ke posisi dengan defisit tinggi dan posisi kunci yang pejabatnya memasuki masa purna tugas dalam 12 bulan ke depan.</p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
