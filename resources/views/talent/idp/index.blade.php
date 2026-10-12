<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('manajemen-talenta.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 flex items-center gap-1">
                        &larr; Kembali ke Manajemen Talenta
                    </a>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>📚</span> Rencana Pengembangan Kompetensi Individu (IDP - Hak 20 JP)
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Pelacakan Pemenuhan Minimal 20 Jam Pelajaran (JP) Diklat Tahunan Pegawai ASN (UU No. 20/2023 &amp; PermenPAN-RB No. 3/2020)
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Tombol SOP IDP & Hak 20 JP --}}
                <x-sop-modal title="SOP Pemenuhan Hak 20 JP & Individual Development Plan (IDP)" 
                             buttonLabel="SOP 20 JP & IDP" 
                             badge="UU No. 20/2023 Pasal 49" 
                             color="emerald">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200">
                            <h4 class="font-bold text-emerald-950 text-xs mb-1 flex items-center gap-1.5">
                                <x-icon name="book-open" class="w-4 h-4 text-emerald-700" />
                                Hak Mandatori 20 JP Pegawai ASN
                            </h4>
                            <p class="text-emerald-900 text-[11px] leading-relaxed">
                                Berdasarkan <strong>Pasal 49 UU No. 20 Tahun 2023</strong> dan <strong>PP No. 17 Tahun 2020</strong>, setiap ASN berhak dan wajib memperoleh pengembangan kompetensi minimal <strong>20 Jam Pelajaran (JP)</strong> per tahun yang difasilitasi oleh instansi.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">1</span>
                                Bentuk &amp; Jalur Pengembangan Kompetensi
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-emerald-200 text-[11px] text-slate-600">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Jalur Klasikal (Tatap Muka Langsung)</strong>
                                    Pelatihan kepemimpinan (PKA, PKP), seminar/konferensi ilmiah internasional, bimbingan teknis, workshop praktik laboratorium.
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Jalur Non-Klasikal (Modern Digital)</strong>
                                    E-learning MOOC LAN RI (ASN Unggul), coaching &amp; mentoring dengan atasan, magang kerja klinis di rumah sakit mitra, pertukaran dosen.
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-teal-600 text-white flex items-center justify-center text-[10px]">2</span>
                                Alur Penyusunan IDP Individu
                            </h4>
                            <div class="space-y-1.5 ps-2 border-l-2 border-teal-200 text-[11px] text-slate-600">
                                <div>1. Pegawai dan atasan langsung menganalisis gap kompetensi dari hasil asesmen BKN.</div>
                                <div>2. Menyusun roadmap pelatihan 1 tahun kalender (minimal 20 JP).</div>
                                <div>3. Mengunggah sertifikat selesai pelatihan ke modul <em>Riwayat Diklat</em>.</div>
                                <div>4. Sistem otomatis memvalidasi pemenuhan target tahun berjalan.</div>
                            </div>
                        </div>
                    </div>
                </x-sop-modal>

                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 rounded-xl shadow-2xs hover:bg-teal-700 transition">
                    <span>🖨️</span> Cetak Rekap IDP
                </button>
                <a href="{{ route('riwayat-diklat.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 shadow-2xs transition">
                    <span>📜</span> Katalog Diklat
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- 4 Kartu Statistik Pemenuhan 20 JP --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-200 shadow-sm">
                <div class="text-[11px] font-bold text-gray-500 uppercase tracking-wider">Total Pegawai Dinilai</div>
                <div class="text-3xl font-black text-gray-900 mt-1">{{ $totalPegawai }} <span class="text-sm font-normal text-gray-500">ASN</span></div>
                <div class="text-[10px] text-gray-400 mt-1">Dosen &amp; Tenaga Kependidikan</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-emerald-200 bg-emerald-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Memenuhi Standar (&ge; 20 JP)</div>
                <div class="text-3xl font-black text-emerald-600 mt-1">{{ $totalMemenuhi }} <span class="text-sm font-normal text-emerald-400">Pegawai</span></div>
                <div class="text-[10px] text-emerald-700 mt-1">{{ $totalPegawai > 0 ? round(($totalMemenuhi / $totalPegawai) * 100, 1) : 0 }}% Tingkat Pemenuhan</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-rose-200 bg-rose-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Kurang Jam Pelajaran (&lt; 20 JP)</div>
                <div class="text-3xl font-black text-rose-600 mt-1">{{ $totalKurang }} <span class="text-sm font-normal text-rose-400">Pegawai</span></div>
                <div class="text-[10px] text-rose-500 mt-1">Memerlukan Penugasan Pelatihan</div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-blue-200 bg-blue-50/30 shadow-sm">
                <div class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Rata-Rata Capaian JP</div>
                <div class="text-3xl font-black text-blue-600 mt-1">{{ $rataRataJp }} <span class="text-sm font-normal text-blue-400">JP / Pegawai</span></div>
                <div class="text-[10px] text-blue-700 mt-1">Target Minimum: 20 JP / Tahun</div>
            </div>
        </div>

        {{-- Filter & Pencarian --}}
        <div class="bg-white rounded-2xl p-4 border border-gray-200 shadow-sm">
            <form method="GET" action="{{ route('manajemen-talenta.idp') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                
                <div>
                    <label class="text-[11px] font-bold text-gray-500 block mb-1">Cari Pegawai:</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama / NIP pegawai..." class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                </div>

                <div>
                    <label class="text-[11px] font-bold text-gray-500 block mb-1">Unit Kerja:</label>
                    <select name="unit_kerja_id" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Semua Unit Kerja</option>
                        @foreach($unitKerjas as $u)
                            <option value="{{ $u->id }}" {{ $unitKerjaId == $u->id ? 'selected' : '' }}>
                                {{ $u->nama_unit }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-[11px] font-bold text-gray-500 block mb-1">Status Pemenuhan JP:</label>
                    <select name="status_jp" class="w-full text-xs rounded-xl border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="">Semua Status</option>
                        <option value="memenuhi" {{ $statusJp === 'memenuhi' ? 'selected' : '' }}>Memenuhi (&ge; 20 JP)</option>
                        <option value="kurang" {{ $statusJp === 'kurang' ? 'selected' : '' }}>Kurang (&lt; 20 JP)</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="w-full py-2 px-3 rounded-xl bg-teal-600 text-white font-bold text-xs hover:bg-teal-700 shadow-xs transition">
                        Filter Data
                    </button>
                    @if($search || $unitKerjaId || $statusJp)
                        <a href="{{ route('manajemen-talenta.idp') }}" class="py-2 px-3 rounded-xl border border-gray-300 text-gray-600 text-xs font-bold hover:bg-gray-50 transition text-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Rekapitulasi Pemenuhan Hak 20 JP & Roadmap IDP --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gray-50/80 border-b border-gray-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-black text-gray-900 uppercase tracking-wider">Pemantauan Pemenuhan 20 JP &amp; Kurikulum Pelatihan Berbasis Talenta</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Penugasan pengembangan kompetensi diselaraskan dengan kuadran Matriks 9-Kotak ASN.</p>
                </div>
                <span class="text-xs font-mono bg-teal-100 text-teal-800 font-bold px-2.5 py-1 rounded-lg">
                    {{ count($pegawaiList) }} Pegawai Ditampilkan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs divide-y divide-gray-200">
                    <thead class="bg-slate-50 text-gray-600 font-black uppercase text-[10px] tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 text-center w-10">No</th>
                            <th class="px-4 py-3.5">Nama Pegawai &amp; Jabatan</th>
                            <th class="px-3 py-3.5 text-center">Kuadran Talenta</th>
                            <th class="px-4 py-3.5 text-center">Realisasi Diklat</th>
                            <th class="px-3 py-3.5 text-center">Status Pemenuhan</th>
                            <th class="px-4 py-3.5">Rekomendasi Kurikulum IDP</th>
                            <th class="px-3 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-800">
                        @forelse($pegawaiList as $idx => $item)
                            <tr class="hover:bg-teal-50/30 transition {{ !$item['is_memenuhi'] ? 'bg-rose-50/10' : '' }}">
                                <td class="px-4 py-3 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 text-xs">{{ $item['pegawai']->nama }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        NIP. {{ $item['pegawai']->nip }} &bull; Gol. <b>{{ $item['pegawai']->golongan->kode ?? '-' }}</b>
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        {{ $item['pegawai']->jabatan->nama_jabatan ?? '-' }} ({{ $item['pegawai']->unitKerja->nama_unit ?? 'FKP' }})
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full font-black text-[10px] {{ $item['kuadran_box'] >= 8 ? 'bg-emerald-100 text-emerald-800' : ($item['kuadran_box'] >= 5 ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                                        Kotak {{ $item['kuadran_box'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center w-40">
                                    <div class="flex items-center justify-between text-[11px] mb-1 font-bold">
                                        <span class="{{ $item['is_memenuhi'] ? 'text-emerald-700' : 'text-rose-600' }}">{{ $item['total_jp'] }} JP</span>
                                        <span class="text-gray-400">Target 20 JP</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                        <div class="h-2 rounded-full {{ $item['is_memenuhi'] ? 'bg-emerald-500' : 'bg-rose-500' }}" style="width: {{ $item['persen'] }}%"></div>
                                    </div>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    @if($item['is_memenuhi'])
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                            ✅ Memenuhi Standar
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full font-bold text-[10px] bg-rose-100 text-rose-800">
                                            ⚠️ Kurang {{ $item['sisa_jp'] }} JP
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="text-[11px] text-gray-700 leading-snug">
                                        {{ $item['rekomendasi'] }}
                                    </p>
                                </td>
                                <td class="px-3 py-3 text-center">
                                    <a href="{{ route('manajemen-talenta.pegawai.idp', ['pegawai' => $item['pegawai']->id, 'tahun' => $tahun]) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-teal-50 text-teal-800 border border-teal-200 font-bold hover:bg-teal-100 transition">
                                        <span>Roadmap IDP</span> &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500 text-xs">
                                    Tidak ada data pegawai yang sesuai dengan kriteria filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Edukasi Regulasi Mandatori 20 JP --}}
        <div class="bg-gradient-to-r from-teal-50 via-emerald-50 to-white rounded-2xl p-5 border border-teal-200 text-xs text-gray-700 space-y-2">
            <h4 class="font-black text-teal-950 flex items-center gap-1.5 uppercase tracking-wider text-xs">
                <span>⚖️</span> Landasan Hukum Hak Pengembangan Kompetensi 20 JP Pegawai ASN
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-[11px] leading-relaxed">
                <div>
                    <p><b>1. UU No. 20 Tahun 2023 Pasal 49:</b> Setiap Pegawai ASN memiliki kewajiban dan hak untuk melakukan pengembangan kompetensi melalui pembelajaran secara terus-menerus.</p>
                    <p class="mt-1"><b>2. PP No. 11/2017 &amp; PP No. 17/2020:</b> Pengembangan kompetensi dilakukan paling sedikit 20 (dua puluh) jam pelajaran dalam 1 (satu) tahun bagi setiap PNS.</p>
                </div>
                <div>
                    <p><b>3. Peraturan LAN RI No. 10 Tahun 2018:</b> Metode pembelajaran mencakup klasikal (diklat tatap muka/seminar/workshop) dan non-klasikal (e-learning, magang, bimbingan di tempat kerja, pertukaran pegawai).</p>
                    <p class="mt-1"><b>4. Integrasi Matriks 9-Kotak:</b> Pegawai di Kotak Talenta tinggi dipersiapkan melalui Diklat Kepemimpinan, sedangkan di Kotak Kinerja perlu penguatan keahlian teknis.</p>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
