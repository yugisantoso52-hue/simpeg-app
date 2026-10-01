<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-100 text-blue-800">Bagan Interaktif</span>
                    <span class="text-xs text-gray-500 font-mono">BKN & PermenPAN-RB No. 1/2020</span>
                </div>
                <h1 class="text-2xl font-black text-gray-900 mt-1 flex items-center gap-2">
                    <span>🏛️</span> Peta Jabatan Fakultas Keperawatan UNRI
                </h1>
                <p class="text-xs text-gray-600 mt-0.5">
                    Visualisasi Struktur Organisasi, Rantai Komando, dan Formasi Kuota ABK vs Bezetting Riil Pegawai
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>🖨️</span> Cetak Bagan
                </button>
                <a href="{{ route('abk.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition">
                    <span>🧮</span> Rekap ABK & Formasi
                </a>
                <a href="{{ route('anjab.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition">
                    <span>📑</span> Katalog Anjab
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
        showModal: false,
        activeNode: null,
        loading: false,
        openDetail(title, subtitle, bezetting, kebutuhan, status, pegawaiList, ikhtisar, anjabUrl) {
            this.activeNode = {
                title: title,
                subtitle: subtitle,
                bezetting: bezetting,
                kebutuhan: kebutuhan,
                status: status,
                pegawaiList: pegawaiList || [],
                ikhtisar: ikhtisar || 'Belum ada ikhtisar tugas.',
                anjabUrl: anjabUrl || '#'
            };
            this.showModal = true;
        }
    }">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            {{-- Indikator Legenda --}}
            <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-700 uppercase tracking-wider">Status Formasi ABK:</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> 🟢 Ideal (Cukup)
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> 🔴 Kurang Pegawai
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> 🟡 Kelebihan Pegawai
                    </span>
                </div>
                <div class="text-gray-500 font-medium">
                    💡 <span class="italic">Klik pada kotak jabatan atau unit untuk melihat daftar pejabat, foto, NIP & rincian tugas</span>
                </div>
            </div>

            {{-- KANVAS BAGAN STRUKTUR ORGANISASI (SESUAI DOKUMEN RESMI FKP UNRI) --}}
            <div class="bg-gradient-to-b from-sky-50/50 via-white to-gray-50 rounded-2xl border-2 border-blue-200/80 p-6 md:p-8 shadow-md overflow-x-auto">

                {{-- Header Resmi Bagan --}}
                <div class="text-center pb-6 border-b border-blue-100 mb-8">
                    <div class="inline-flex items-center gap-3 justify-center mb-2">
                        <div class="w-10 h-10 rounded-full bg-blue-700 text-white font-black flex items-center justify-center text-sm shadow">
                            UNRI
                        </div>
                        <div>
                            <h2 class="text-lg md:text-xl font-black text-blue-950 uppercase tracking-wide">
                                STRUKTUR ORGANISASI & PETA JABATAN
                            </h2>
                            <p class="text-xs font-bold text-blue-700 uppercase tracking-widest">
                                FAKULTAS KEPERAWATAN UNIVERSITAS RIAU
                            </p>
                        </div>
                    </div>
                </div>

                {{-- TREE DIAGRAM CONTAINER --}}
                <div class="min-w-[950px] flex flex-col items-center gap-8">

                    {{-- LEVEL 1: DEKAN & STAF PENUNJANG (SPMF & SENAT) --}}
                    <div class="w-full flex items-center justify-center gap-6 relative">

                        {{-- Sayap Kiri: SPMF (Garis Koordinasi Putus-putus) --}}
                        <div class="w-64">
                            <div class="bg-sky-600 text-white p-3 rounded-xl shadow-md border-2 border-dashed border-sky-400 hover:scale-[1.02] transition cursor-pointer"
                                 @click="openDetail(
                                     'SATUAN PENJAMIN MUTU (SPMF)',
                                     'Unsur Penjaminan Mutu Fakultas & Gugus Penjamin Mutu (GPM)',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%SPMF%'))->count() }},
                                     1,
                                     'Ideal',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%SPMF%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     'Mengkoordinasikan penjaminan mutu akademik, akreditasi LAM-PTKes, dan evaluasi pembelajaran internal fakultas.'
                                 )">
                                <div class="text-[10px] font-bold tracking-wider uppercase text-sky-200">Garis Koordinasi</div>
                                <div class="font-extrabold text-xs mt-0.5">SATUAN PENJAMIN MUTU (SPMF)</div>
                                <div class="mt-2 pt-2 border-t border-sky-400/40 flex items-center justify-between text-[11px]">
                                    <span>Kepala: <strong>Dosen SPMF</strong></span>
                                    <span class="bg-sky-700 px-1.5 py-0.5 rounded text-[10px]">Detail ➔</span>
                                </div>
                            </div>
                        </div>

                        {{-- Garis Penghubung Kiri ke Dekan --}}
                        <div class="h-0.5 w-10 border-t-2 border-dashed border-sky-400"></div>

                        {{-- Kotak Dekan (Pusat Pimpinan) --}}
                        <div class="w-72">
                            <div class="bg-gradient-to-r from-blue-900 to-indigo-900 text-white p-4 rounded-2xl shadow-xl border-2 border-amber-400 hover:scale-105 transition cursor-pointer"
                                 @click="openDetail(
                                     'DEKAN',
                                     'Pimpinan Tertinggi Fakultas Keperawatan UNRI (Grade 15)',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Dekan'))->count() }},
                                     1,
                                     'Ideal',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Dekan'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     'Memimpin penyelenggaraan tridharma perguruan tinggi, pembinaan sivitas akademika, pengelolaan keuangan, SDM, sarana prasarana, serta pengembangan mutu dan kerjasama Fakultas.'
                                 )">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded bg-amber-400 text-blue-950">PIMPINAN FAKULTAS</span>
                                    <span class="text-[10px] font-mono text-blue-200">Grade 15</span>
                                </div>
                                <div class="text-base font-black tracking-wider uppercase mt-1">DEKAN</div>
                                <div class="text-xs text-blue-200 font-medium">Fakultas Keperawatan UNRI</div>
                                <div class="mt-3 pt-2 border-t border-blue-700/60 flex items-center justify-between text-xs">
                                    <span class="text-blue-100">Bezetting: <strong>1</strong> / Butuh: <strong>1</strong></span>
                                    <span class="bg-emerald-500 text-white font-black text-[10px] px-2 py-0.5 rounded-full">🟢 Ideal</span>
                                </div>
                            </div>
                        </div>

                        {{-- Garis Penghubung Kanan ke Senat --}}
                        <div class="h-0.5 w-10 border-t-2 border-dashed border-sky-400"></div>

                        {{-- Sayap Kanan: Senat Fakultas (Garis Pertimbangan) --}}
                        <div class="w-64">
                            <div class="bg-slate-800 text-white p-3 rounded-xl shadow-md border-2 border-dashed border-slate-500 hover:scale-[1.02] transition cursor-pointer"
                                 @click="openDetail(
                                     'SENAT FAKULTAS',
                                     'Unsur Pertimbangan Normatif dan Perwakilan Fakultas',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Senat%'))->count() }},
                                     2,
                                     'Ideal',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Senat%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     'Merumuskan kebijakan akademik, memberikan pertimbangan dan pengawasan terhadap pelaksanaan proses akademik di lingkungan Fakultas Keperawatan.'
                                 )">
                                <div class="text-[10px] font-bold tracking-wider uppercase text-slate-300">Badan Pertimbangan</div>
                                <div class="font-extrabold text-xs mt-0.5">SENAT FAKULTAS</div>
                                <div class="mt-2 pt-2 border-t border-slate-600 flex items-center justify-between text-[11px]">
                                    <span>Ketua & Sekretaris</span>
                                    <span class="bg-slate-700 px-1.5 py-0.5 rounded text-[10px]">Detail ➔</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Garis Vertikal dari Dekan ke Para Wakil Dekan --}}
                    <div class="w-0.5 h-6 bg-blue-600"></div>

                    {{-- LEVEL 2: PARA WAKIL DEKAN (WD I, WD II, WD III) --}}
                    <div class="w-full flex items-start justify-center gap-6 relative">

                        {{-- Garis Horizontal Penghubung 3 Wadek --}}
                        <div class="absolute top-0 left-1/4 right-1/4 h-0.5 bg-blue-600"></div>

                        {{-- 1. WD I (Akademik) + GPM --}}
                        <div class="w-72 flex flex-col items-center">
                            <div class="w-0.5 h-4 bg-blue-600"></div>
                            <div class="w-full bg-blue-800 text-white p-3.5 rounded-xl shadow-md hover:scale-[1.02] transition cursor-pointer"
                                 @click="openDetail(
                                     'WAKIL DEKAN BID. AKADEMIK (WD I)',
                                     'Unsur Pimpinan Bidang Pendidikan, Kurikulum & Penjaminan Mutu (Grade 13)',
                                     1, 1, 'Ideal',
                                     [],
                                     'Membantu Dekan memimpin pelaksanaan pendidikan, penelitian, pengabdian masyarakat, penjaminan mutu, dan evaluasi kurikulum OBE.'
                                 )">
                                <div class="flex justify-between items-center text-[10px] text-blue-200">
                                    <span>UNSUR PIMPINAN</span>
                                    <span class="font-mono">Grade 13</span>
                                </div>
                                <div class="font-bold text-xs uppercase mt-0.5">WAKIL DEKAN BID. AKADEMIK</div>
                                <div class="mt-2 pt-2 border-t border-blue-700 flex justify-between text-[11px]">
                                    <span>Bezetting: 1 / Butuh: 1</span>
                                    <span class="bg-emerald-500 text-white font-bold text-[9px] px-1.5 py-0.2 rounded">Ideal</span>
                                </div>
                            </div>

                            {{-- Sub-Unit di bawah WD I: GPM S1, S2, NERS --}}
                            <div class="w-0.5 h-3 bg-blue-400"></div>
                            <div class="w-56 bg-sky-100 border border-sky-300 rounded-lg p-2 text-center text-xs shadow-sm">
                                <div class="font-bold text-sky-900 text-[11px] mb-1">GUGUS PENJAMIN MUTU (GPM)</div>
                                <div class="grid grid-cols-3 gap-1 text-[10px] font-semibold text-sky-800">
                                    <span class="bg-white p-1 rounded border border-sky-200 shadow-2xs">GPM S1</span>
                                    <span class="bg-white p-1 rounded border border-sky-200 shadow-2xs">GPM S2</span>
                                    <span class="bg-white p-1 rounded border border-sky-200 shadow-2xs">GPM NERS</span>
                                </div>
                            </div>
                        </div>

                        {{-- 2. WD II (Keuangan & Umum) --}}
                        <div class="w-72 flex flex-col items-center">
                            <div class="w-0.5 h-4 bg-blue-600"></div>
                            <div class="w-full bg-blue-800 text-white p-3.5 rounded-xl shadow-md hover:scale-[1.02] transition cursor-pointer"
                                 @click="openDetail(
                                     'WAKIL DEKAN BID. KEUANGAN DAN UMUM (WD II)',
                                     'Unsur Pimpinan Bidang Perencanaan, Anggaran & Kepegawaian (Grade 13)',
                                     1, 1, 'Ideal',
                                     [],
                                     'Membantu Dekan dalam perencanaan anggaran, perbendaharaan, kepegawaian, ketatausahaan, dan sarana prasarana fakultas.'
                                 )">
                                <div class="flex justify-between items-center text-[10px] text-blue-200">
                                    <span>UNSUR PIMPINAN</span>
                                    <span class="font-mono">Grade 13</span>
                                </div>
                                <div class="font-bold text-xs uppercase mt-0.5">WAKIL DEKAN BID. KEUANGAN DAN UMUM</div>
                                <div class="mt-2 pt-2 border-t border-blue-700 flex justify-between text-[11px]">
                                    <span>Bezetting: 1 / Butuh: 1</span>
                                    <span class="bg-emerald-500 text-white font-bold text-[9px] px-1.5 py-0.2 rounded">Ideal</span>
                                </div>
                            </div>
                        </div>

                        {{-- 3. WD III (Kemahasiswaan, Alumni & Kerjasama) --}}
                        <div class="w-72 flex flex-col items-center">
                            <div class="w-0.5 h-4 bg-blue-600"></div>
                            <div class="w-full bg-blue-800 text-white p-3.5 rounded-xl shadow-md hover:scale-[1.02] transition cursor-pointer"
                                 @click="openDetail(
                                     'WAKIL DEKAN BID. KEMAHASISWAAN, ALUMNI & KERJASAMA (WD III)',
                                     'Unsur Pimpinan Bidang Penalaran, Minat Bakat, Tracer Study & Kemitraan (Grade 13)',
                                     1, 1, 'Ideal',
                                     [],
                                     'Membantu Dekan dalam pembinaan kegiatan kemahasiswaan, tracer study alumni, dan kerjasama institusional.'
                                 )">
                                <div class="flex justify-between items-center text-[10px] text-blue-200">
                                    <span>UNSUR PIMPINAN</span>
                                    <span class="font-mono">Grade 13</span>
                                </div>
                                <div class="font-bold text-xs uppercase mt-0.5">WAKIL DEKAN BID. KEMAHASISWAAN, ALUMNI & KERJASAMA</div>
                                <div class="mt-2 pt-2 border-t border-blue-700 flex justify-between text-[11px]">
                                    <span>Bezetting: 1 / Butuh: 1</span>
                                    <span class="bg-emerald-500 text-white font-bold text-[9px] px-1.5 py-0.2 rounded">Ideal</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Garis Vertikal Pemisah ke Tingkat Pelaksana --}}
                    <div class="w-full border-t-2 border-blue-300 relative my-2">
                        <div class="absolute left-1/2 -top-2 w-4 h-4 rounded-full bg-blue-600 -translate-x-1/2"></div>
                    </div>

                    {{-- LEVEL 3: TINGKAT PELAKSANA AKADEMIK, TATA USAHA, LAB & FUNGSIONAL (5 KOLOM UTAMA) --}}
                    <div class="w-full grid grid-cols-1 lg:grid-cols-5 gap-4 items-start">

                        {{-- 1. UNIT-UNIT FUNGSIONAL --}}
                        <div class="bg-white border-2 border-sky-300 rounded-xl p-3.5 shadow-sm">
                            <div class="bg-sky-700 text-white text-center py-1.5 px-2 rounded-lg font-bold text-xs uppercase tracking-wide mb-2.5">
                                UNIT-UNIT FUNGSIONAL
                            </div>
                            <div class="space-y-1 text-[11px]">
                                @php
                                    $unitFung = [
                                        '1. Etik Riset', '2. Komite Etik', '3. CBT (Computer Based Test)',
                                        '4. Kerjasama', '5. Penelitian & Pengabmasy', '6. NEDU (S1, Ners, S2)',
                                        '7. Bimbingan Konseling', '8. Humas', '9. PPID'
                                    ];
                                @endphp
                                @foreach($unitFung as $uf)
                                    <div class="bg-sky-50/70 hover:bg-sky-100 p-1.5 rounded border border-sky-100 font-medium text-sky-950 flex items-center justify-between cursor-pointer"
                                         @click="openDetail('{{ $uf }}', 'Unit Fungsional Khusus Fakultas', 1, 1, 'Ideal', [], 'Melaksanakan fungsi spesifik pendukung akademik dan pelayanan publik.')">
                                        <span>{{ $uf }}</span>
                                        <span class="text-[9px] text-sky-600 font-mono">Detail</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 2. JURUSAN PREKLINIK KEPERAWATAN --}}
                        <div class="bg-white border-2 border-emerald-400 rounded-xl p-3.5 shadow-sm">
                            <div class="bg-emerald-700 text-white text-center py-1.5 px-2 rounded-lg font-bold text-xs uppercase tracking-wide mb-2.5">
                                JURUSAN PREKLINIK KEPERAWATAN
                            </div>

                            {{-- Sub-blok 1: Koordinator Prodi S1 & S2 --}}
                            <div class="mb-3">
                                <div class="bg-emerald-100 text-emerald-950 font-bold text-[10px] uppercase px-2 py-1 rounded mb-1.5 text-center">
                                    KOORDINATOR PROGRAM STUDI
                                </div>
                                <div class="space-y-1 text-xs">
                                    <div class="bg-emerald-50 hover:bg-emerald-100 p-2 rounded border border-emerald-200 cursor-pointer"
                                         @click="openDetail(
                                             'Koordinator Prodi S1 Keperawatan',
                                             'Program Studi Sarjana Keperawatan (Grade 10)',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S1%'))->count() }},
                                             1, 'Ideal', [],
                                             'Mengkoordinasikan kurikulum S1, pembelajaran OBE, plotting dosen, dan akreditasi LAM-PTKes.'
                                         )">
                                        <div class="font-bold text-emerald-950 text-[11px]">1. S1 Keperawatan</div>
                                        <div class="text-[10px] text-gray-600">Bezetting: 1 / Butuh: 1 (Ideal)</div>
                                    </div>
                                    <div class="bg-emerald-50 hover:bg-emerald-100 p-2 rounded border border-emerald-200 cursor-pointer"
                                         @click="openDetail(
                                             'Koordinator Prodi S2 Keperawatan',
                                             'Program Studi Magister Keperawatan (Grade 10)',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S2%'))->count() }},
                                             1, 'Ideal', [],
                                             'Mengkoordinasikan kurikulum Magister S2, riset tesis, dan akreditasi prodi.'
                                         )">
                                        <div class="font-bold text-emerald-950 text-[11px]">2. S2 Keperawatan</div>
                                        <div class="text-[10px] text-gray-600">Bezetting: 1 / Butuh: 1 (Ideal)</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Sub-blok 2: 7 KJFD Preklinik --}}
                            <div>
                                <div class="bg-emerald-100 text-emerald-950 font-bold text-[10px] uppercase px-2 py-1 rounded mb-1.5 text-center">
                                    KJFD KEPERAWATAN (7 BIDANG)
                                </div>
                                <div class="grid grid-cols-1 gap-1 text-[10.5px]">
                                    @php
                                        $kjfd = ['1. Medikal Bedah', '2. Gawat Darurat', '3. Maternitas', '4. Anak', '5. Keluarga Komunitas', '6. Gerontik', '7. Jiwa'];
                                    @endphp
                                    @foreach($kjfd as $k)
                                        <div class="bg-gray-50 hover:bg-emerald-50 p-1.5 rounded border border-gray-200 text-gray-800 font-medium">
                                            {{ $k }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- 3. JURUSAN KLINIK DAN KOMUNITAS --}}
                        <div class="bg-white border-2 border-teal-400 rounded-xl p-3.5 shadow-sm">
                            <div class="bg-teal-700 text-white text-center py-1.5 px-2 rounded-lg font-bold text-xs uppercase tracking-wide mb-2.5">
                                JURUSAN KLINIK DAN KOMUNITAS
                            </div>

                            {{-- Koorprodi Ners --}}
                            <div class="mb-3">
                                <div class="bg-teal-100 text-teal-950 font-bold text-[10px] uppercase px-2 py-1 rounded mb-1.5 text-center">
                                    KOORDINATOR PROGRAM STUDI
                                </div>
                                <div class="bg-teal-50 hover:bg-teal-100 p-2 rounded border border-teal-200 cursor-pointer"
                                     @click="openDetail(
                                         'Koordinator Prodi Profesi Ners',
                                         'Program Studi Profesi Ners (Grade 10)',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi Ners%'))->count() }},
                                         1, 'Ideal', [],
                                         'Mengkoordinasikan stase kepaniteraan klinik mahasiswa ners di Rumah Sakit dan Puskesmas.'
                                     )">
                                    <div class="font-bold text-teal-950 text-[11px]">1. Profesi Ners</div>
                                    <div class="text-[10px] text-gray-600">Bezetting: 1 / Butuh: 1 (Ideal)</div>
                                </div>
                            </div>

                            {{-- KJFD Klinik & Komunitas --}}
                            <div>
                                <div class="bg-teal-100 text-teal-950 font-bold text-[10px] uppercase px-2 py-1 rounded mb-1.5 text-center">
                                    KELOMPOK FUNGSIONAL (KJFD)
                                </div>
                                <div class="space-y-1.5 text-xs">
                                    <div class="bg-gray-50 hover:bg-teal-50 p-2 rounded border border-gray-200 font-medium text-gray-800">
                                        1. KJFD Klinik
                                    </div>
                                    <div class="bg-gray-50 hover:bg-teal-50 p-2 rounded border border-gray-200 font-medium text-gray-800">
                                        2. KJFD Komunitas
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- 4. LABORATORIUM KEPERAWATAN (9 RUANG LAB) --}}
                        <div class="bg-white border-2 border-indigo-400 rounded-xl p-3.5 shadow-sm">
                            <div class="bg-indigo-700 text-white text-center py-1.5 px-2 rounded-lg font-bold text-xs uppercase tracking-wide mb-2.5">
                                LABORATORIUM KEPERAWATAN
                            </div>
                            <div class="bg-indigo-50 border border-indigo-200 rounded p-1.5 text-[10px] text-center font-bold text-indigo-900 mb-2 cursor-pointer"
                                 @click="openDetail(
                                     'Pranata Laboratorium Pendidikan (PLP)',
                                     'Fungsional PLP / Laboran (Grade 8)',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Laboran%')->orWhere('nama_jabatan', 'like', '%PLP%'))->count() }},
                                     2, '🔴 Kurang 1', [],
                                     'Mengelola 9 ruang laboratorium keperawatan, manikin medis, bahan habis pakai, dan keselamatan kerja K3 praktikum.'
                                 )">
                                Kepala Lab & Pranata Lab (PLP)
                                <span class="block text-[9px] text-rose-600 font-bold mt-0.5">Bezetting: 1 / Kebutuhan: 2 (Kurang 1)</span>
                            </div>
                            <div class="space-y-1 text-[10.5px]">
                                @php
                                    $labs = [
                                        '1. Biomedik', '2. Medikal Bedah', '3. Gawat Darurat',
                                        '4. Jiwa', '5. Keluarga Komunitas', '6. Gerontik',
                                        '7. Maternitas', '8. Anak', '9. Tumbuh Kembang Anak'
                                    ];
                                @endphp
                                @foreach($labs as $lb)
                                    <div class="bg-gray-50 hover:bg-indigo-50 p-1.5 rounded border border-gray-200 font-medium text-gray-800">
                                        {{ $lb }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- 5. KEPALA BAGIAN UMUM (BAGIAN UMUM & POKJA) --}}
                        <div class="bg-white border-2 border-amber-400 rounded-xl p-3.5 shadow-sm">
                            <div class="bg-amber-600 text-white text-center py-1.5 px-2 rounded-lg font-bold text-xs uppercase tracking-wide mb-2">
                                KEPALA BAGIAN UMUM
                            </div>
                            <div class="bg-amber-50 text-[10px] font-bold text-amber-950 p-1.5 rounded border border-amber-200 text-center mb-2.5">
                                KELOMPOK KERJA (POKJA)
                            </div>
                            <div class="space-y-2 text-xs">
                                {{-- Pokja 1: Akademik & Kemahasiswaan --}}
                                <div class="bg-white hover:bg-amber-50 p-2 rounded-lg border border-amber-200 shadow-2xs cursor-pointer"
                                     @click="openDetail(
                                         'Pokja Akademik & Kemahasiswaan',
                                         'Kelompok Kerja Layanan Registrasi, Perkuliahan & Yudisium (Grade 9 & 6)',
                                         {{ \App\Models\Pegawai::where('unit_kerja_id', 11)->count() }},
                                         4, 'Ideal', [],
                                         'Melaksanakan pelayanan administrasi nilai, KRS mahasiswa, surat keterangan aktif, dan kelengkapan yudisium.'
                                     )">
                                    <div class="font-bold text-gray-900 text-[11px]">1. Bidang Akademik & Kemahasiswaan</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">Ka Pokja & Staf Pelaksana</div>
                                </div>

                                {{-- Pokja 2: Keuangan & Kepegawaian --}}
                                <div class="bg-white hover:bg-amber-50 p-2 rounded-lg border border-amber-200 shadow-2xs cursor-pointer"
                                     @click="openDetail(
                                         'Pokja Keuangan dan Kepegawaian',
                                         'Kelompok Kerja Pengelolaan Anggaran, Presensi & Karir ASN (Grade 9 & 6)',
                                         {{ \App\Models\Pegawai::where('unit_kerja_id', 12)->count() }},
                                         3, 'Ideal', [],
                                         'Melaksanakan verifikasi presensi mobile, rekapitulasi logbook harian, usulan kenaikan pangkat, gaji berkala, dan berkas cuti pegawai.'
                                     )">
                                    <div class="font-bold text-gray-900 text-[11px]">2. Keuangan dan Kepegawaian</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">Ka Pokja & Staf Kepegawaian</div>
                                </div>

                                {{-- Pokja 3: Umum Sarana Akademik --}}
                                <div class="bg-white hover:bg-amber-50 p-2 rounded-lg border border-amber-200 shadow-2xs cursor-pointer"
                                     @click="openDetail(
                                         'Pokja Umum Sarana Akademik',
                                         'Kelompok Kerja Pengelolaan BMN, Perlengkapan & Sarana (Grade 9 & 6)',
                                         {{ \App\Models\Pegawai::where('unit_kerja_id', 13)->count() }},
                                         3, '🔴 Kurang 1', [],
                                         'Melaksanakan inventarisasi BMN, pemeliharaan gedung kuliah, kebersihan lingkungan, dan sarana prasarana.'
                                     )">
                                    <div class="font-bold text-gray-900 text-[11px]">3. Kemahasiswaan, Alumni & Kerjasama / Sarana</div>
                                    <div class="text-[10px] text-gray-500 mt-0.5">Ka Pokja & Staf Perlengkapan</div>
                                </div>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        {{-- MODAL / DRAWER DETAIL JABATAN & PEGAWAI RIIL --}}
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
             style="display: none;">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-200 relative" @click.away="showModal = false">
                <button @click="showModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 text-xl font-bold">
                    ✕
                </button>

                <div class="pr-6">
                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase rounded bg-blue-100 text-blue-800" x-text="activeNode ? activeNode.subtitle : ''"></span>
                    <h3 class="text-lg font-black text-gray-900 mt-1" x-text="activeNode ? activeNode.title : ''"></h3>
                </div>

                <div class="mt-4 p-3 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between text-xs">
                    <div>
                        <span class="text-gray-500 block">Kondisi Riil (Bezetting):</span>
                        <span class="text-base font-black text-gray-900" x-text="activeNode ? activeNode.bezetting + ' Orang' : ''"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Kebutuhan (ABK):</span>
                        <span class="text-base font-black text-blue-700" x-text="activeNode ? activeNode.kebutuhan + ' Orang' : ''"></span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Status Formasi:</span>
                        <span class="font-bold px-2 py-0.5 rounded text-[11px] bg-blue-50 text-blue-800" x-text="activeNode ? activeNode.status : ''"></span>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Ikhtisar & Fungsi Jabatan:</h4>
                    <p class="text-xs text-gray-600 leading-relaxed bg-blue-50/50 p-3 rounded-lg border border-blue-100" x-text="activeNode ? activeNode.ikhtisar : ''"></p>
                </div>

                <div class="mt-4">
                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pejabat / Pegawai yang Menduduki:</h4>
                    <template x-if="activeNode && activeNode.pegawaiList && activeNode.pegawaiList.length > 0">
                        <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                            <template x-for="p in activeNode.pegawaiList" :key="p.nip">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs">
                                        <span x-text="(p.nama || p.nama_lengkap || '').substring(0, 1)"></span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-gray-900 truncate" x-text="(p.gelar_depan ? p.gelar_depan + ' ' : '') + (p.nama || p.nama_lengkap || '') + (p.gelar_belakang ? ', ' + p.gelar_belakang : '')"></div>
                                        <div class="text-[11px] text-gray-500 font-mono" x-text="'NIP: ' + p.nip"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!activeNode || !activeNode.pegawaiList || activeNode.pegawaiList.length === 0">
                        <p class="text-xs text-gray-500 italic p-2 bg-gray-50 rounded border border-gray-200">
                            Pegawai terdata di pangkalan data SIMPEG sesuai formasi ini.
                        </p>
                    </template>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end gap-2">
                    <button @click="showModal = false" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Tutup
                    </button>
                    <a :href="'{{ route('abk.index') }}'" class="px-4 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Buka Rekap ABK ➔
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
