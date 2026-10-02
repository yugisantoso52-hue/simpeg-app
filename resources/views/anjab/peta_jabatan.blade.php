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

            {{-- KANVAS BAGAN STRUKTUR ORGANISASI (SESUAI DOKUMEN RESMI TERBARU FKP UNRI) --}}
            <div class="bg-gradient-to-b from-sky-50/50 via-white to-gray-50 rounded-2xl border-2 border-blue-200/80 p-6 md:p-8 shadow-md overflow-x-auto">

                {{-- Header Resmi Bagan --}}
                <div class="text-center pb-6 border-b-2 border-blue-200 mb-8 flex items-center justify-between px-4">
                    {{-- Logo UNRI --}}
                    <div class="flex items-center gap-3">
                        <div style="background-color: #047857 !important; color: #ffffff !important; width: 48px; height: 48px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 13px; border: 2px solid #a7f3d0; box-shadow: 0 2px 6px rgba(4, 120, 87, 0.3);">
                            UNRI
                        </div>
                    </div>

                    {{-- Judul Tengah --}}
                    <div>
                        <h2 class="text-xl md:text-2xl font-black uppercase tracking-wider" style="color: #0284c7 !important; letter-spacing: 0.5px;">
                            STRUKTUR ORGANISASI
                        </h2>
                        <h3 class="text-lg md:text-xl font-black uppercase tracking-wide" style="color: #0369a1 !important;">
                            FAKULTAS KEPERAWATAN UNIVERSITAS RIAU
                        </h3>
                    </div>

                    {{-- Tut Wuri Handayani Badge --}}
                    <div class="flex items-center gap-3">
                        <div style="background-color: #0284c7 !important; color: #ffffff !important; width: 48px; height: 48px; border-radius: 9999px; display: flex; align-items: center; justify-content: center; font-weight: 900; font-size: 11px; border: 2px solid #bae6fd; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3); text-align: center; line-height: 1.1;">
                            KEMDIK<br>TI
                        </div>
                    </div>
                </div>

                {{-- TREE DIAGRAM CONTAINER --}}
                <div class="min-w-[1150px] flex flex-col items-center gap-6">

                    {{-- ========================================================================= --}}
                    {{-- LEVEL 1: DEKAN, SATUAN PENJAMINAN MUTU (SPMF), DAN SENAT FAKULTAS         --}}
                    {{-- ========================================================================= --}}
                    <div class="w-full flex items-center justify-center gap-4 relative">

                        {{-- Sayap Kiri: SATUAN PENJAMINAN MUTU (SPMF) dengan GPM S1, S2, NERS --}}
                        <div class="w-72">
                            <div class="p-3.5 rounded-xl shadow-md cursor-pointer hover:scale-[1.02] transition"
                                 style="background-color: #ffffff !important; border: 2px solid #0284c7 !important;"
                                 @click="openDetail(
                                     'SATUAN PENJAMINAN MUTU (SPMF)',
                                     'Unsur Penjaminan Mutu & Gugus Penjamin Mutu (GPM)',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%SPMF%')->orWhere('nama_jabatan', 'like', '%GPM%'))->count() }},
                                     1, 'Ideal',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%SPMF%')->orWhere('nama_jabatan', 'like', '%GPM%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     'Mengkoordinasikan sistem penjaminan mutu internal (SPMI), monev pembelajaran OBE, dan akreditasi internasional/LAM-PTKes.'
                                 )">
                                <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11px; letter-spacing: 0.3px; padding: 6px 8px; border-radius: 6px; text-align: center; text-transform: uppercase;">
                                    SATUAN PENJAMINAN MUTU (SPMF)
                                </div>
                                <div class="mt-2 space-y-1 text-xs">
                                    <div class="py-1 px-2 rounded font-bold text-center text-[10.5px]" style="background-color: #f0f9ff !important; border: 1px solid #bae6fd !important; color: #0369a1 !important;">
                                        GPM S1
                                    </div>
                                    <div class="py-1 px-2 rounded font-bold text-center text-[10.5px]" style="background-color: #f0f9ff !important; border: 1px solid #bae6fd !important; color: #0369a1 !important;">
                                        GPM S2
                                    </div>
                                    <div class="py-1 px-2 rounded font-bold text-center text-[10.5px]" style="background-color: #f0f9ff !important; border: 1px solid #bae6fd !important; color: #0369a1 !important;">
                                        GPM NERS
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Garis Solid Penghubung Kiri ke Dekan --}}
                        <div class="h-0.5 w-12" style="background-color: #0284c7 !important;"></div>

                        {{-- Pusat Puncak: KOTAK DEKAN --}}
                        <div class="w-80">
                            <div class="p-4 rounded-2xl shadow-xl hover:scale-105 transition cursor-pointer"
                                 style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important; color: #ffffff !important; border: 2.5px solid #fbbf24 !important; box-shadow: 0 6px 15px rgba(30, 58, 138, 0.4) !important;"
                                 @click="openDetail(
                                     'DEKAN',
                                     'Pimpinan Tertinggi Fakultas Keperawatan UNRI (Grade 15)',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Dekan'))->count() }},
                                     1, 'Ideal',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Dekan'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     'Memimpin penyelenggaraan tridharma perguruan tinggi, pembinaan sivitas akademika, pengelolaan keuangan, SDM, sarana prasarana, serta pengembangan mutu dan kerjasama Fakultas.'
                                 )">
                                <div class="flex items-center justify-between text-[10px]">
                                    <span style="background-color: #fbbf24 !important; color: #1e3a8a !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; text-transform: uppercase;">PIMPINAN FAKULTAS</span>
                                    <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 15</span>
                                </div>
                                <div class="text-lg font-black tracking-wider uppercase mt-2 text-center" style="color: #ffffff !important; letter-spacing: 1px;">
                                    DEKAN
                                </div>
                                <div class="text-xs text-center font-semibold mt-0.5" style="color: #93c5fd !important;">
                                    Fakultas Keperawatan UNRI
                                </div>
                                <div class="mt-3 pt-2 flex items-center justify-between text-xs" style="border-top: 1px solid rgba(255,255,255,0.2) !important; color: #e0e7ff !important;">
                                    <span>Bezetting: <strong style="color: #ffffff !important;">1</strong> / Butuh: <strong style="color: #ffffff !important;">1</strong></span>
                                    <span style="background-color: #10b981 !important; color: #ffffff !important; font-weight: 800; font-size: 10px; padding: 2px 8px; border-radius: 9999px;">🟢 Ideal</span>
                                </div>
                            </div>
                        </div>

                        {{-- Garis Putus-putus Penghubung Kanan ke Senat (Pertimbangan) --}}
                        <div class="h-0.5 w-12 border-t-2 border-dashed" style="border-color: #64748b !important;"></div>

                        {{-- Sayap Kanan: SENAT FAKULTAS (SEKRETARIS) --}}
                        <div class="w-72">
                            <div class="p-3.5 rounded-xl shadow-md cursor-pointer hover:scale-[1.02] transition"
                                 style="background-color: #ffffff !important; border: 2px dashed #475569 !important;"
                                 @click="openDetail(
                                     'SENAT FAKULTAS',
                                     'Badan Pertimbangan Normatif Fakultas',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Senat%'))->count() }},
                                     2, 'Ideal',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Senat%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     'Merumuskan kebijakan akademik, memberikan pertimbangan dan pengawasan terhadap pelaksanaan proses akademik di lingkungan Fakultas Keperawatan.'
                                 )">
                                <div style="background: linear-gradient(135deg, #334155 0%, #1e293b 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11px; letter-spacing: 0.3px; padding: 6px 8px; border-radius: 6px; text-align: center; text-transform: uppercase;">
                                    SENAT FAKULTAS
                                </div>
                                <div class="mt-2 space-y-1 text-xs">
                                    <div class="py-1 px-2 rounded font-bold text-center text-[10.5px]" style="background-color: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #334155 !important;">
                                        SEKRETARIS
                                    </div>
                                    <div class="py-1 px-2 rounded font-bold text-center text-[10.5px]" style="background-color: #f8fafc !important; border: 1px solid #cbd5e1 !important; color: #64748b !important;">
                                        Komisi Akademik & Etik
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Garis Vertikal dari Dekan ke Tiga Wakil Dekan --}}
                    <div class="w-0.5 h-6" style="background-color: #1e3a8a !important;"></div>

                    {{-- ========================================================================= --}}
                    {{-- LEVEL 2: TIGA SAYAP WAKIL DEKAN BESERTA SELURUH UNIT PELAKSANA DI BAWAHNYA--}}
                    {{-- ========================================================================= --}}
                    <div class="w-full relative">

                        {{-- Garis Horizontal Penghubung 3 Sayap Wakil Dekan --}}
                        <div class="w-5/6 mx-auto h-0.5" style="background-color: #1e3a8a !important;"></div>

                        <div class="w-full grid grid-cols-3 gap-6 items-start mt-2">

                            {{-- ================================================================= --}}
                            {{-- SAYAP 1 (KIRI): WAKIL DEKAN BIDANG AKADEMIK                       --}}
                            {{-- ================================================================= --}}
                            <div class="flex flex-col items-center">
                                <div class="w-0.5 h-4" style="background-color: #1e3a8a !important;"></div>

                                {{-- Kartu WD I --}}
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG AKADEMIK (WD I)',
                                         'Unsur Pimpinan Bidang Pendidikan, Kurikulum & Penjaminan Mutu (Grade 13)',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan I%'))->count() }},
                                         1, 'Ideal',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan I%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         'Membantu Dekan memimpin pelaksanaan pendidikan, penelitian, pengabdian masyarakat, penjaminan mutu, dan evaluasi kurikulum OBE.'
                                     )">
                                    <div class="flex justify-between items-center text-[10px]">
                                        <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px;">UNSUR PIMPINAN</span>
                                        <span style="background-color: #fbbf24 !important; color: #1e3a8a !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 13</span>
                                    </div>
                                    <div class="font-black text-xs uppercase mt-2 text-center" style="color: #ffffff !important; letter-spacing: 0.3px; font-size: 12.5px;">
                                        WAKIL DEKAN BIDANG AKADEMIK
                                    </div>
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">1</strong> / Butuh: <strong style="color: #ffffff !important;">1</strong></span>
                                        <span style="background-color: #10b981 !important; color: #ffffff !important; font-weight: 800; font-size: 10px; padding: 2px 8px; border-radius: 9999px;">🟢 Ideal</span>
                                    </div>
                                </div>

                                {{-- Garis Turun ke Ketua Jurusan & Sek. Jurusan --}}
                                <div class="w-0.5 h-4" style="background-color: #0d9488 !important;"></div>

                                {{-- Kotak KETUA JURUSAN & SEK. JURUSAN (Di bawah WD I, Membawahi 2 Jurusan) --}}
                                <div class="w-full grid grid-cols-2 gap-2">
                                    {{-- Ketua Jurusan --}}
                                    <div class="p-2 rounded-lg shadow-sm cursor-pointer hover:scale-[1.02] transition text-center"
                                         style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important; color: #ffffff !important; border: 1.5px solid #2dd4bf !important;"
                                         @click="openDetail(
                                             'KETUA JURUSAN (KAJUR)',
                                             'Pimpinan Jurusan Keperawatan (Grade 11)',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Ketua Jurusan%'))->count() }},
                                             1, 'Ideal',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Ketua Jurusan%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             'Memimpin jurusan dalam pengelolaan tridharma, pembagian beban kerja dosen (BKD), dan membawahi Jurusan Preklinik serta Jurusan Klinik & Komunitas di bawah koordinasi Wakil Dekan I.'
                                         )">
                                        <div class="flex justify-between items-center text-[9px] mb-1">
                                            <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 1px 4px; border-radius: 3px;">KAJUR</span>
                                            <span style="background-color: #fbbf24 !important; color: #0f766e !important; font-weight: 900; padding: 1px 4px; border-radius: 3px; font-family: monospace;">Grade 11</span>
                                        </div>
                                        <div class="font-black text-[11px] uppercase tracking-wide" style="color: #ffffff !important;">
                                            KETUA JURUSAN
                                        </div>
                                        <div class="text-[9.5px] mt-1 pt-1 border-t border-white/20 text-teal-100 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Ketua Jurusan%'))->count() }}</strong>/1</span>
                                            <span class="text-[8.5px] px-1.5 py-0.2 rounded font-bold {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Ketua Jurusan%'))->count() > 0 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' }}">
                                                {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Ketua Jurusan%'))->count() > 0 ? 'Terisi' : 'Butuh SK' }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Sekretaris Jurusan --}}
                                    <div class="p-2 rounded-lg shadow-sm cursor-pointer hover:scale-[1.02] transition text-center"
                                         style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important; color: #ffffff !important; border: 1.5px solid #5eead4 !important;"
                                         @click="openDetail(
                                             'SEKRETARIS JURUSAN (SEKJUR)',
                                             'Pimpinan Administrasi Jurusan Keperawatan (Grade 10)',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Sekretaris Jurusan%'))->count() }},
                                             1, 'Ideal',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Sekretaris Jurusan%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             'Membantu Ketua Jurusan dalam pengelolaan administrasi akademik, ketatausahaan, dokumentasi kurikulum, dan rekapitulasi BKD/SKP dosen.'
                                         )">
                                        <div class="flex justify-between items-center text-[9px] mb-1">
                                            <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 1px 4px; border-radius: 3px;">SEKJUR</span>
                                            <span style="background-color: #fbbf24 !important; color: #0d9488 !important; font-weight: 900; padding: 1px 4px; border-radius: 3px; font-family: monospace;">Grade 10</span>
                                        </div>
                                        <div class="font-black text-[11px] uppercase tracking-wide" style="color: #ffffff !important;">
                                            SEKRETARIS JURUSAN
                                        </div>
                                        <div class="text-[9.5px] mt-1 pt-1 border-t border-white/20 text-teal-100 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Sekretaris Jurusan%'))->count() }}</strong>/1</span>
                                            <span class="text-[8.5px] px-1.5 py-0.2 rounded font-bold {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Sekretaris Jurusan%'))->count() > 0 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' }}">
                                                {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Sekretaris Jurusan%'))->count() > 0 ? 'Terisi' : 'Butuh SK' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Garis Turun Bercabang ke 2 Jurusan --}}
                                <div class="w-0.5 h-3" style="background-color: #0d9488 !important;"></div>
                                <div class="w-4/5 h-0.5" style="background-color: #0d9488 !important;"></div>

                                {{-- Cabang 2 Jurusan: Preklinik & Klinik Komunitas --}}
                                <div class="w-full grid grid-cols-2 gap-3 mt-1.5">

                                    {{-- 1. JURUSAN PREKLINIK KEPERAWATAN --}}
                                    <div class="p-2.5 rounded-xl shadow-sm" style="background-color: #ffffff !important; border: 2px solid #059669 !important;">
                                        <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 10.5px; letter-spacing: 0.3px; padding: 5px 6px; border-radius: 6px; text-align: center; text-transform: uppercase;">
                                            JURUSAN PREKLINIK KEPERAWATAN
                                        </div>

                                        <div class="mt-2">
                                            <div style="background-color: #d1fae5 !important; color: #064e3b !important; font-weight: 800; font-size: 9px; text-transform: uppercase; padding: 3px 6px; border-radius: 4px; text-align: center; margin-bottom: 5px;">
                                                KOORDINATOR PROGRAM STUDI
                                            </div>
                                            <div class="space-y-1.5 text-xs">
                                                {{-- S1 --}}
                                                <div class="p-1.5 rounded cursor-pointer hover:opacity-90 transition"
                                                     style="background-color: #f0fdf4 !important; border: 1px solid #86efac !important;"
                                                     @click="openDetail(
                                                         'Koordinator Program Studi S1 Keperawatan',
                                                         'Program Studi Sarjana Keperawatan (Grade 10)',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S1%'))->count() }},
                                                         1, 'Ideal',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S1%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         'Mengkoordinasikan kurikulum S1, pembelajaran OBE, plotting dosen, dan akreditasi LAM-PTKes.'
                                                     )">
                                                    <div class="font-black text-[10.5px]" style="color: #064e3b !important;">1. S1 KEPERAWATAN</div>
                                                    <div class="text-[9.5px] text-gray-600">Bezetting: 1 / Butuh: 1 (🟢 Ideal)</div>
                                                </div>
                                                {{-- S2 --}}
                                                <div class="p-1.5 rounded cursor-pointer hover:opacity-90 transition"
                                                     style="background-color: #f0fdf4 !important; border: 1px solid #86efac !important;"
                                                     @click="openDetail(
                                                         'Koordinator Program Studi S2 Keperawatan',
                                                         'Program Studi Magister Keperawatan (Grade 10)',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S2%'))->count() }},
                                                         1, 'Ideal',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S2%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         'Mengkoordinasikan kurikulum Magister S2, bimbingan riset tesis, dan akreditasi prodi.'
                                                     )">
                                                    <div class="font-black text-[10.5px]" style="color: #064e3b !important;">2. S2 KEPERAWATAN</div>
                                                    <div class="text-[9.5px] text-gray-600">Bezetting: 1 / Butuh: 1 (🟢 Ideal)</div>
                                                </div>
                                                {{-- S3 --}}
                                                <div class="p-1.5 rounded cursor-pointer hover:opacity-90 transition"
                                                     style="background-color: #f0fdf4 !important; border: 1px solid #86efac !important;"
                                                     @click="openDetail(
                                                         'Koordinator Program Studi S3 Keperawatan',
                                                         'Program Studi Doktor Keperawatan (Grade 10)',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S3%'))->count() }},
                                                         1, 'Ideal',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S3%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         'Mengkoordinasikan kurikulum Doktor S3, riset lanjutan translasi keperawatan, dan publikasi internasional bereputasi.'
                                                     )">
                                                    <div class="font-black text-[10.5px]" style="color: #064e3b !important;">3. S3 KEPERAWATAN</div>
                                                    <div class="text-[9.5px] text-gray-600">Bezetting: 1 / Butuh: 1 (🟢 Ideal)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. JURUSAN KLINIK DAN KOMUNITAS --}}
                                    <div class="p-2.5 rounded-xl shadow-sm" style="background-color: #ffffff !important; border: 2px solid #0d9488 !important;">
                                        <div style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 10.5px; letter-spacing: 0.3px; padding: 5px 6px; border-radius: 6px; text-align: center; text-transform: uppercase;">
                                            JURUSAN KLINIK & KOMUNITAS
                                        </div>

                                        <div class="mt-2">
                                            <div style="background-color: #ccfbf1 !important; color: #115e59 !important; font-weight: 800; font-size: 9px; text-transform: uppercase; padding: 3px 6px; border-radius: 4px; text-align: center; margin-bottom: 5px;">
                                                KOORDINATOR PROGRAM STUDI
                                            </div>
                                            <div class="space-y-1.5 text-xs">
                                                {{-- NERS --}}
                                                <div class="p-2 rounded cursor-pointer hover:opacity-90 transition"
                                                     style="background-color: #f0fdfa !important; border: 1px solid #99f6e4 !important;"
                                                     @click="openDetail(
                                                         'Koordinator Program Studi Profesi Ners',
                                                         'Program Studi Profesi Ners (Grade 10)',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi Ners%'))->count() }},
                                                         1, 'Ideal',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi Ners%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         'Mengkoordinasikan stase kepaniteraan klinik mahasiswa ners di Rumah Sakit, Puskesmas, dan persiapan Uji Kompetensi Ners (UKNI).'
                                                     )">
                                                    <div class="font-black text-[11px]" style="color: #115e59 !important;">1. NERS</div>
                                                    <div class="text-[9.5px] text-gray-600 mt-0.5">Bezetting: 1 / Butuh: 1 (🟢 Ideal)</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- ================================================================= --}}
                            {{-- SAYAP 2 (TENGAH): WAKIL DEKAN BIDANG KEUANGAN DAN UMUM             --}}
                            {{-- ================================================================= --}}
                            <div class="flex flex-col items-center">
                                <div class="w-0.5 h-4" style="background-color: #1e3a8a !important;"></div>

                                {{-- Kartu WD II --}}
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG KEUANGAN DAN UMUM (WD II)',
                                         'Unsur Pimpinan Bidang Perencanaan, Anggaran & Kepegawaian (Grade 13)',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan II%'))->count() }},
                                         1, 'Ideal',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan II%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         'Membantu Dekan dalam perencanaan anggaran, perbendaharaan, kepegawaian, ketatausahaan, dan sarana prasarana fakultas.'
                                     )">
                                    <div class="flex justify-between items-center text-[10px]">
                                        <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px;">UNSUR PIMPINAN</span>
                                        <span style="background-color: #fbbf24 !important; color: #1e3a8a !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 13</span>
                                    </div>
                                    <div class="font-black text-xs uppercase mt-2 text-center" style="color: #ffffff !important; letter-spacing: 0.3px; font-size: 12.5px;">
                                        WAKIL DEKAN BIDANG KEUANGAN DAN UMUM
                                    </div>
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">1</strong> / Butuh: <strong style="color: #ffffff !important;">1</strong></span>
                                        <span style="background-color: #10b981 !important; color: #ffffff !important; font-weight: 800; font-size: 10px; padding: 2px 8px; border-radius: 9999px;">🟢 Ideal</span>
                                    </div>
                                </div>

                                {{-- Garis Turun ke Kepala Bagian Umum --}}
                                <div class="w-0.5 h-4" style="background-color: #d97706 !important;"></div>

                                {{-- Kotak KEPALA BAGIAN UMUM --}}
                                <div class="w-full p-2.5 rounded-lg shadow-sm cursor-pointer hover:opacity-95 transition"
                                     style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important; color: #ffffff !important; border: 1.5px solid #fde68a !important;"
                                     @click="openDetail(
                                         'KEPALA BAGIAN UMUM',
                                         'Kepala Bagian Tata Usaha Fakultas Keperawatan (Grade 11)',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Kepala Bagian Umum%'))->count() }},
                                         1, 'Ideal',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Kepala Bagian Umum%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         'Memimpin dan mengkoordinasikan pelaksanaan urusan akademik, keuangan, kepegawaian, persuratan, BMN, dan perlengkapan sarana prasarana.'
                                     )">
                                    <div class="text-center font-black text-xs uppercase tracking-wide" style="color: #ffffff !important;">
                                        KEPALA BAGIAN UMUM
                                    </div>
                                </div>

                                {{-- Garis Turun ke 3 Pokja --}}
                                <div class="w-0.5 h-3" style="background-color: #d97706 !important;"></div>

                                {{-- 3 Kotak Ka. POKJA --}}
                                <div class="w-full space-y-2 mt-1">
                                    {{-- Pokja 1: Akademik & Kemahasiswaan --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ka. POKJA AKADEMIK DAN KEMAHASISWAAN',
                                             'Kelompok Kerja Layanan Registrasi, Perkuliahan & Kemahasiswaan (Grade 9 & 6)',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 11)->count() }},
                                             4, 'Ideal',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 11)->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             'Melaksanakan pelayanan administrasi nilai, KRS mahasiswa, surat keterangan aktif, kelengkapan yudisium, dan beasiswa.'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA AKADEMIK DAN KEMAHASISWAAN</div>
                                        <div class="text-[10px] text-gray-600 mt-0.5">Ka Pokja & Staf Pelaksana Akademik</div>
                                    </div>

                                    {{-- Pokja 2: Keuangan & Kepegawaian --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ka. POKJA KEUANGAN DAN KEPEGAWAIAN',
                                             'Kelompok Kerja Pengelolaan Anggaran, Presensi & Karir ASN (Grade 9 & 6)',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 12)->count() }},
                                             3, 'Ideal',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 12)->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             'Melaksanakan verifikasi presensi, rekapitulasi logbook harian, usulan kenaikan pangkat, gaji berkala, SPJ keuangan, dan berkas cuti pegawai.'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA KEUANGAN DAN KEPEGAWAIAN</div>
                                        <div class="text-[10px] text-gray-600 mt-0.5">Ka Pokja & Staf Kepegawaian / Keuangan</div>
                                    </div>

                                    {{-- Pokja 3: Umum dan Sarana Akademik --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ka. POKJA UMUM DAN SARANA AKADEMIK',
                                             'Kelompok Kerja Pengelolaan BMN, Perlengkapan & Sarana (Grade 9 & 6)',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 13)->count() }},
                                             3, '🔴 Kurang 1',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 13)->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             'Melaksanakan inventarisasi BMN, pemeliharaan gedung kuliah, kebersihan lingkungan, persuratan umum, dan sarana prasarana.'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA UMUM DAN SARANA AKADEMIK</div>
                                        <div class="text-[10px] text-gray-600 mt-0.5">Ka Pokja & Staf Perlengkapan / BMN</div>
                                    </div>
                                </div>
                            </div>

                            {{-- ================================================================= --}}
                            {{-- SAYAP 3 (KANAN): WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI & KERJASAMA--}}
                            {{-- ================================================================= --}}
                            <div class="flex flex-col items-center">
                                <div class="w-0.5 h-4" style="background-color: #1e3a8a !important;"></div>

                                {{-- Kartu WD III --}}
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI & KERJASAMA (WD III)',
                                         'Unsur Pimpinan Bidang Penalaran, Minat Bakat, Tracer Study & Kemitraan (Grade 13)',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan III%'))->count() }},
                                         1, 'Ideal',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan III%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         'Membantu Dekan dalam pembinaan kegiatan kemahasiswaan, tracer study alumni, dan kerjasama institusional.'
                                     )">
                                    <div class="flex justify-between items-center text-[10px]">
                                        <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px;">UNSUR PIMPINAN</span>
                                        <span style="background-color: #fbbf24 !important; color: #1e3a8a !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 13</span>
                                    </div>
                                    <div class="font-black text-xs uppercase mt-2 text-center" style="color: #ffffff !important; letter-spacing: 0.3px; font-size: 12px; line-height: 1.3;">
                                        WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI DAN KERJASAMA
                                    </div>
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">1</strong> / Butuh: <strong style="color: #ffffff !important;">1</strong></span>
                                        <span style="background-color: #10b981 !important; color: #ffffff !important; font-weight: 800; font-size: 10px; padding: 2px 8px; border-radius: 9999px;">🟢 Ideal</span>
                                    </div>
                                </div>

                                {{-- Garis Turun ke BEM, DPM, ALUMNI --}}
                                <div class="w-0.5 h-4" style="background-color: #7c3aed !important;"></div>
                                <div class="w-5/6 h-0.5" style="background-color: #7c3aed !important;"></div>

                                {{-- 3 Kotak Organisasi Mahasiswa & Alumni: BEM, DPM, ALUMNI --}}
                                <div class="w-full grid grid-cols-3 gap-2 mt-2">
                                    {{-- BEM --}}
                                    <div class="p-2.5 rounded-xl shadow-sm text-center cursor-pointer hover:scale-105 transition"
                                         style="background-color: #ffffff !important; border: 1.5px solid #8b5cf6 !important;"
                                         @click="openDetail(
                                             'BADAN EKSEKUTIF MAHASISWA (BEM)',
                                             'Lembaga Eksekutif Kemahasiswaan Fakultas Keperawatan',
                                             1, 1, 'Ideal', [],
                                             'Melaksanakan program kerja penalaran, advokasi, pengabdian mahasiswa kepada masyarakat, dan minat bakat sivitas mahasiswa.'
                                         )">
                                        <div style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11px; padding: 4px 6px; border-radius: 6px; text-transform: uppercase;">
                                            BEM
                                        </div>
                                        <div class="text-[9.5px] font-bold mt-1.5" style="color: #5b21b6 !important;">
                                            BADAN EKSEKUTIF MAHASISWA
                                        </div>
                                    </div>

                                    {{-- DPM --}}
                                    <div class="p-2.5 rounded-xl shadow-sm text-center cursor-pointer hover:scale-105 transition"
                                         style="background-color: #ffffff !important; border: 1.5px solid #8b5cf6 !important;"
                                         @click="openDetail(
                                             'DEWAN PERWAKILAN MAHASISWA (DPM)',
                                             'Lembaga Legislatif dan Pengawasan Kemahasiswaan',
                                             1, 1, 'Ideal', [],
                                             'Melaksanakan fungsi legislasi kemahasiswaan, pengawasan program BEM, dan penyaluran aspirasi mahasiswa fakultas.'
                                         )">
                                        <div style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11px; padding: 4px 6px; border-radius: 6px; text-transform: uppercase;">
                                            DPM
                                        </div>
                                        <div class="text-[9.5px] font-bold mt-1.5" style="color: #5b21b6 !important;">
                                            DEWAN PERWAKILAN MAHASISWA
                                        </div>
                                    </div>

                                    {{-- ALUMNI --}}
                                    <div class="p-2.5 rounded-xl shadow-sm text-center cursor-pointer hover:scale-105 transition"
                                         style="background-color: #ffffff !important; border: 1.5px solid #8b5cf6 !important;"
                                         @click="openDetail(
                                             'IKATAN ALUMNI FAKULTAS KEPERAWATAN',
                                             'Organisasi Alumni & Jejaring Kemitraan Profesi',
                                             1, 1, 'Ideal', [],
                                             'Mewadahi jejaring alumni perawat, tracer study lulusan, pendayagunaan karir ners di RS nasional dan internasional.'
                                         )">
                                        <div style="background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11px; padding: 4px 6px; border-radius: 6px; text-transform: uppercase;">
                                            ALUMNI
                                        </div>
                                        <div class="text-[9.5px] font-bold mt-1.5" style="color: #5b21b6 !important;">
                                            IKATAN ALUMNI FKp
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Garis Penghubung Vertikal Besar Menuju Tiga Pilar Fungsional Bawah --}}
                    <div class="w-full relative my-3">
                        <div class="w-full h-0.5" style="background-color: #0284c7 !important;"></div>
                        <div class="absolute left-1/2 -top-1.5 w-3.5 h-3.5 rounded-full -translate-x-1/2" style="background-color: #0284c7 !important;"></div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- LEVEL 3: TIGA PILAR UTAMA FUNGSIONAL & PENUNJANG (3 KOLOM BERDAMPINGAN)   --}}
                    {{-- ========================================================================= --}}
                    <div class="w-full grid grid-cols-3 gap-6 items-start">

                        {{-- --------------------------------------------------------------------- --}}
                        {{-- PILAR 1 (KIRI): KELOMPOK JABATAN FUNGSIONAL DOSEN (KJFD - 9 BIDANG)   --}}
                        {{-- --------------------------------------------------------------------- --}}
                        <div class="p-4 rounded-xl shadow-sm" style="background-color: #ffffff !important; border: 2.5px solid #059669 !important;">
                            <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11.5px; letter-spacing: 0.5px; padding: 8px 10px; border-radius: 8px; text-align: center; text-transform: uppercase; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3); margin-bottom: 10px;">
                                KELOMPOK JABATAN FUNGSIONAL DOSEN (KJFD)
                            </div>
                            <div class="space-y-1.5 text-xs">
                                @php
                                    $kjfds = [
                                        '1. MEDIKAL BEDAH',
                                        '2. GAWAT DARURAT',
                                        '3. MATERNITAS',
                                        '4. ANAK',
                                        '5. KELUARGA KOMUNITAS',
                                        '6. GERONTIK',
                                        '7. JIWA',
                                        '8. KLINIK',
                                        '9. KOMUNITAS',
                                    ];
                                @endphp
                                @foreach($kjfds as $idx => $k)
                                    <div class="p-2 rounded flex items-center justify-between cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #f0fdf4 !important; border: 1px solid #86efac !important; font-weight: 800;"
                                         @click="openDetail(
                                             '{{ $k }}',
                                             'Kelompok Jabatan Fungsional Dosen (KJFD)',
                                             {{ \App\Models\Pegawai::where('jenis_pegawai', 'like', '%Dosen%')->count() > 0 ? round(\App\Models\Pegawai::where('jenis_pegawai', 'like', '%Dosen%')->count() / 9) : 6 }},
                                             6, 'Ideal', [],
                                             'Melaksanakan tridharma perguruan tinggi pada rumpun keahlian {{ $k }}, pembimbingan tugas akhir, praktikum klinik dan riset keperawatan.'
                                         )">
                                        <span style="color: #064e3b !important; font-size: 11px;">{{ $k }}</span>
                                        <span style="color: #059669 !important; font-weight: 800; font-size: 9.5px; font-family: monospace;">Detail ➔</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- --------------------------------------------------------------------- --}}
                        {{-- PILAR 2 (TENGAH): UNIT-UNIT FUNGSIONAL (9 UNIT)                       --}}
                        {{-- --------------------------------------------------------------------- --}}
                        <div class="p-4 rounded-xl shadow-sm" style="background-color: #ffffff !important; border: 2.5px solid #0284c7 !important;">
                            <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11.5px; letter-spacing: 0.5px; padding: 8px 10px; border-radius: 8px; text-align: center; text-transform: uppercase; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3); margin-bottom: 10px;">
                                UNIT-UNIT FUNGSIONAL
                            </div>
                            <div class="space-y-1.5 text-xs">
                                @php
                                    $unitFung = [
                                        '1. ETIK RISET',
                                        '2. KOMITE ETIK',
                                        '3. COMPUTER BASED TEST (CBT)',
                                        '4. KERJASAMA',
                                        '5. PENELITIAN DAN PENGABMASY',
                                        '6. NURSING EDUCATION DEVELOPMENT UNIT (NEDU) S1, NERS DAN S2',
                                        '7. BIMBINGAN KONSELING',
                                        '8. HUMAS',
                                        '9. PPID',
                                    ];
                                @endphp
                                @foreach($unitFung as $uf)
                                    <div class="p-2 rounded flex items-center justify-between cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #f0f9ff !important; border: 1px solid #bae6fd !important; font-weight: 800;"
                                         @click="openDetail(
                                             '{{ $uf }}',
                                             'Unit Fungsional Khusus Fakultas',
                                             1, 1, 'Ideal', [],
                                             'Melaksanakan fungsi penunjang akademik, kepatuhan etik, pengujian CBT, kemitraan institusi, dan layanan keterbukaan informasi publik.'
                                         )">
                                        <span style="color: #0c4a6e !important; font-size: 11px;">{{ $uf }}</span>
                                        <span style="color: #0284c7 !important; font-weight: 800; font-size: 9.5px; font-family: monospace;">Detail ➔</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- --------------------------------------------------------------------- --}}
                        {{-- PILAR 3 (KANAN): LABORATORIUM / RUANG KEPERAWATAN (9 RUANG)           --}}
                        {{-- --------------------------------------------------------------------- --}}
                        <div class="p-4 rounded-xl shadow-sm" style="background-color: #ffffff !important; border: 2.5px solid #4f46e5 !important;">
                            <div style="background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11.5px; letter-spacing: 0.5px; padding: 8px 10px; border-radius: 8px; text-align: center; text-transform: uppercase; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.3); margin-bottom: 8px;">
                                LABORATORIUM / RUANG KEPERAWATAN
                            </div>

                            {{-- Badge Pranata Lab (PLP) --}}
                            <div class="p-2 rounded text-center mb-2.5 cursor-pointer hover:opacity-90 transition"
                                 style="background-color: #eef2ff !important; border: 1.5px solid #c7d2fe !important;"
                                 @click="openDetail(
                                     'Pranata Laboratorium Pendidikan (PLP)',
                                     'Fungsional PLP / Laboran (Grade 8)',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Laboran%')->orWhere('nama_jabatan', 'like', '%PLP%'))->count() }},
                                     2, '🔴 Kurang 1',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Laboran%')->orWhere('nama_jabatan', 'like', '%PLP%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     'Mengelola 9 ruang laboratorium keperawatan, manikin medis, bahan habis pakai, dan keselamatan kerja K3 praktikum.'
                                 )">
                                <div class="font-black text-[11px]" style="color: #312e81 !important;">Kepala Lab & Pranata Lab (PLP)</div>
                                <span class="font-extrabold text-[9.5px] block mt-0.5" style="color: #dc2626 !important;">Bezetting: 1 / Kebutuhan: 2 (🔴 Kurang 1)</span>
                            </div>

                            <div class="space-y-1.5 text-xs">
                                @php
                                    $labs = [
                                        '1. BIOMEDIK',
                                        '2. MEDIKAL BEDAH',
                                        '3. GAWAT DARURAT',
                                        '4. JIWA',
                                        '5. KELUARGA KOMUNITAS',
                                        '6. GERONTIK',
                                        '7. MATERNITAS',
                                        '8. ANAK',
                                        '9. TUMBUH KEMBANG ANAK',
                                    ];
                                @endphp
                                @foreach($labs as $lb)
                                    <div class="p-2 rounded flex items-center justify-between cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important; font-weight: 800;"
                                         @click="openDetail(
                                             '{{ $lb }}',
                                             'Ruang Praktikum Laboratorium Keperawatan',
                                             1, 1, 'Ideal', [],
                                             'Fasilitas praktikum simulasi medis, manikin keperawatan, dan ujian Objective Structured Clinical Examination (OSCE).'
                                         )">
                                        <span style="color: #1e1b4b !important; font-size: 11px;">{{ $lb }}</span>
                                        <span style="color: #4f46e5 !important; font-weight: 800; font-size: 9.5px; font-family: monospace;">Detail ➔</span>
                                    </div>
                                @endforeach
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
