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

    @php
        $getAnjab = function($namaKey) use ($anjabMap) {
            $key = strtolower(trim($namaKey));
            $anjab = $anjabMap[$key] ?? null;
            if (!$anjab) {
                foreach ($anjabMap as $k => $item) {
                    if (str_contains($k, $key) || str_contains($key, $k)) {
                        $anjab = $item;
                        break;
                    }
                }
            }
            $bezetting = $anjab ? (int) $anjab->bezetting : 0;
            $kebutuhan = $anjab ? (int) $anjab->formasi_pembulatan : 1;
            $selisih = $bezetting - $kebutuhan;
            $status = $selisih < 0 ? ('🔴 Kurang ' . abs($selisih)) : ($selisih > 0 ? ('🟡 Lebih +' . $selisih) : '🟢 Ideal');
            return (object) [
                'kebutuhan'      => $kebutuhan,
                'bezetting'      => $bezetting,
                'selisih'        => $selisih,
                'status'         => $status,
                'status_raw'     => $anjab ? $anjab->status_formasi : 'Ideal',
                'status_color'   => $anjab ? $anjab->status_color : 'emerald',
                'ikhtisar'       => $anjab->ikhtisar_jabatan ?? 'Melaksanakan tugas pokok dan fungsi sesuai mandat organisasi.',
                'anjab_url'      => $anjab ? route('anjab.show', $anjab->id) : '#',
                'abk_url'        => $anjab ? route('abk.edit', $anjab->id) : route('abk.index'),
                'id'             => $anjab?->id,
            ];
        };
    @endphp

    <div class="py-6" x-data="{
        showModal: false,
        activeNode: null,
        loading: false,
        openDetail(title, subtitle, bezetting, kebutuhan, status, pegawaiList, ikhtisar, anjabUrl, abkUrl) {
            this.activeNode = {
                title: title,
                subtitle: subtitle,
                bezetting: bezetting,
                kebutuhan: kebutuhan,
                status: status,
                pegawaiList: pegawaiList || [],
                ikhtisar: ikhtisar || 'Belum ada ikhtisar tugas.',
                anjabUrl: anjabUrl || '#',
                abkUrl: abkUrl || '#'
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
                        {{-- Sayap Kiri: SATUAN PENJAMINAN MUTU (SPMF) dengan GPM S1, S2, NERS --}}
                        @php $spmfAnjab = $getAnjab('Kepala SPMF / GPM'); @endphp
                        <div class="w-72">
                            <div class="p-3.5 rounded-xl shadow-md cursor-pointer hover:scale-[1.02] transition"
                                 style="background-color: #ffffff !important; border: 2px solid #0284c7 !important;"
                                 @click="openDetail(
                                     'SATUAN PENJAMINAN MUTU (SPMF)',
                                     'Unsur Penjaminan Mutu & Gugus Penjamin Mutu (GPM) (Grade 11)',
                                     {{ $spmfAnjab->bezetting }},
                                     {{ $spmfAnjab->kebutuhan }},
                                     '{{ $spmfAnjab->status }}',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%SPMF%')->orWhere('nama_jabatan', 'like', '%GPM%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     '{{ addslashes($spmfAnjab->ikhtisar) }}',
                                     '{{ $spmfAnjab->anjab_url }}',
                                     '{{ $spmfAnjab->abk_url }}'
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
                        @php $dekanAnjab = $getAnjab('Dekan'); @endphp
                        <div class="w-80">
                            <div class="p-4 rounded-2xl shadow-xl hover:scale-105 transition cursor-pointer"
                                 style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important; color: #ffffff !important; border: 2.5px solid #fbbf24 !important; box-shadow: 0 6px 15px rgba(30, 58, 138, 0.4) !important;"
                                 @click="openDetail(
                                     'DEKAN',
                                     'Pimpinan Tertinggi Fakultas Keperawatan UNRI (Grade 15)',
                                     {{ $dekanAnjab->bezetting }},
                                     {{ $dekanAnjab->kebutuhan }},
                                     '{{ $dekanAnjab->status }}',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'Dekan'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     '{{ addslashes($dekanAnjab->ikhtisar) }}',
                                     '{{ $dekanAnjab->anjab_url }}',
                                     '{{ $dekanAnjab->abk_url }}'
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
                                    <span>Bezetting: <strong style="color: #ffffff !important;">{{ $dekanAnjab->bezetting }}</strong> / Butuh: <strong style="color: #ffffff !important;">{{ $dekanAnjab->kebutuhan }}</strong></span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" style="background-color: {{ $dekanAnjab->selisih < 0 ? '#e11d48' : '#10b981' }} !important; color: #ffffff !important;">
                                        {{ $dekanAnjab->status }}
                                    </span>
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
                                @php $wd1Anjab = $getAnjab('Wakil Dekan I (Bid. Akademik)'); @endphp
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG AKADEMIK (WD I)',
                                         'Unsur Pimpinan Bidang Pendidikan, Kurikulum & Penjaminan Mutu (Grade 13)',
                                         {{ $wd1Anjab->bezetting }},
                                         {{ $wd1Anjab->kebutuhan }},
                                         '{{ $wd1Anjab->status }}',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan I%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         '{{ addslashes($wd1Anjab->ikhtisar) }}',
                                         '{{ $wd1Anjab->anjab_url }}',
                                         '{{ $wd1Anjab->abk_url }}'
                                     )">
                                    <div class="flex justify-between items-center text-[10px]">
                                        <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px;">UNSUR PIMPINAN</span>
                                        <span style="background-color: #fbbf24 !important; color: #1e3a8a !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 13</span>
                                    </div>
                                    <div class="font-black text-xs uppercase mt-2 text-center" style="color: #ffffff !important; letter-spacing: 0.3px; font-size: 12.5px;">
                                        WAKIL DEKAN BIDANG AKADEMIK
                                    </div>
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">{{ $wd1Anjab->bezetting }}</strong> / Butuh: <strong style="color: #ffffff !important;">{{ $wd1Anjab->kebutuhan }}</strong></span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" style="background-color: {{ $wd1Anjab->selisih < 0 ? '#e11d48' : '#10b981' }} !important; color: #ffffff !important;">
                                            {{ $wd1Anjab->status }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Garis Turun ke Ketua Jurusan & Sek. Jurusan --}}
                                <div class="w-0.5 h-4" style="background-color: #0d9488 !important;"></div>

                                {{-- Kotak KETUA JURUSAN & SEK. JURUSAN (Di bawah WD I, Membawahi 2 Jurusan) --}}
                                @php
                                    $kajurAnjab = $getAnjab('Ketua Jurusan (Kajur)');
                                    $sekjurAnjab = $getAnjab('Sekretaris Jurusan');
                                @endphp
                                <div class="w-full grid grid-cols-2 gap-2">
                                    {{-- Ketua Jurusan --}}
                                    <div class="p-2 rounded-lg shadow-sm cursor-pointer hover:scale-[1.02] transition text-center"
                                         style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important; color: #ffffff !important; border: 1.5px solid #2dd4bf !important;"
                                         @click="openDetail(
                                             'KETUA JURUSAN (KAJUR)',
                                             'Pimpinan Jurusan Keperawatan (Grade 11)',
                                             {{ $kajurAnjab->bezetting }},
                                             {{ $kajurAnjab->kebutuhan }},
                                             '{{ $kajurAnjab->status }}',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Ketua Jurusan%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             '{{ addslashes($kajurAnjab->ikhtisar) }}',
                                             '{{ $kajurAnjab->anjab_url }}',
                                             '{{ $kajurAnjab->abk_url }}'
                                         )">
                                        <div class="flex justify-between items-center text-[9px] mb-1">
                                            <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 1px 4px; border-radius: 3px;">KAJUR</span>
                                            <span style="background-color: #fbbf24 !important; color: #0f766e !important; font-weight: 900; padding: 1px 4px; border-radius: 3px; font-family: monospace;">Grade 11</span>
                                        </div>
                                        <div class="font-black text-[11px] uppercase tracking-wide" style="color: #ffffff !important;">
                                            KETUA JURUSAN
                                        </div>
                                        <div class="text-[9.5px] mt-1 pt-1 border-t border-white/20 text-teal-100 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $kajurAnjab->bezetting }}</strong>/{{ $kajurAnjab->kebutuhan }}</span>
                                            <span class="text-[8.5px] px-1.5 py-0.2 rounded font-bold {{ $kajurAnjab->bezetting > 0 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' }}">
                                                {{ $kajurAnjab->bezetting > 0 ? 'Terisi' : 'Butuh SK' }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Sekretaris Jurusan --}}
                                    <div class="p-2 rounded-lg shadow-sm cursor-pointer hover:scale-[1.02] transition text-center"
                                         style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%) !important; color: #ffffff !important; border: 1.5px solid #5eead4 !important;"
                                         @click="openDetail(
                                             'SEKRETARIS JURUSAN (SEKJUR)',
                                             'Pimpinan Administrasi Jurusan Keperawatan (Grade 10)',
                                             {{ $sekjurAnjab->bezetting }},
                                             {{ $sekjurAnjab->kebutuhan }},
                                             '{{ $sekjurAnjab->status }}',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Sekretaris Jurusan%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             '{{ addslashes($sekjurAnjab->ikhtisar) }}',
                                             '{{ $sekjurAnjab->anjab_url }}',
                                             '{{ $sekjurAnjab->abk_url }}'
                                         )">
                                        <div class="flex justify-between items-center text-[9px] mb-1">
                                            <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 1px 4px; border-radius: 3px;">SEKJUR</span>
                                            <span style="background-color: #fbbf24 !important; color: #0d9488 !important; font-weight: 900; padding: 1px 4px; border-radius: 3px; font-family: monospace;">Grade 10</span>
                                        </div>
                                        <div class="font-black text-[11px] uppercase tracking-wide" style="color: #ffffff !important;">
                                            SEKRETARIS JURUSAN
                                        </div>
                                        <div class="text-[9.5px] mt-1 pt-1 border-t border-white/20 text-teal-100 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $sekjurAnjab->bezetting }}</strong>/{{ $sekjurAnjab->kebutuhan }}</span>
                                            <span class="text-[8.5px] px-1.5 py-0.2 rounded font-bold {{ $sekjurAnjab->bezetting > 0 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' }}">
                                                {{ $sekjurAnjab->bezetting > 0 ? 'Terisi' : 'Butuh SK' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Garis Turun Bercabang ke 2 Jurusan --}}
                                <div class="w-0.5 h-3" style="background-color: #0d9488 !important;"></div>
                                <div class="w-4/5 h-0.5" style="background-color: #0d9488 !important;"></div>

                                {{-- Cabang 2 Jurusan: Preklinik & Klinik Komunitas --}}
                                @php
                                    $prodiS1Anjab = $getAnjab('Koordinator Prodi S1 Keperawatan');
                                    $prodiS2Anjab = $getAnjab('Koordinator Prodi S2 Keperawatan');
                                    $prodiS3Anjab = $getAnjab('Koordinator Prodi S3 Keperawatan');
                                    $prodiNersAnjab = $getAnjab('Koordinator Prodi Ners');
                                @endphp
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
                                                         {{ $prodiS1Anjab->bezetting }},
                                                         {{ $prodiS1Anjab->kebutuhan }},
                                                         '{{ $prodiS1Anjab->status }}',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S1%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         '{{ addslashes($prodiS1Anjab->ikhtisar) }}',
                                                         '{{ $prodiS1Anjab->anjab_url }}',
                                                         '{{ $prodiS1Anjab->abk_url }}'
                                                     )">
                                                    <div class="font-black text-[10.5px]" style="color: #064e3b !important;">1. S1 KEPERAWATAN</div>
                                                    <div class="text-[9.5px] text-gray-600">Bezetting: {{ $prodiS1Anjab->bezetting }} / Butuh: {{ $prodiS1Anjab->kebutuhan }} ({{ $prodiS1Anjab->status }})</div>
                                                </div>
                                                {{-- S2 --}}
                                                <div class="p-1.5 rounded cursor-pointer hover:opacity-90 transition"
                                                     style="background-color: #f0fdf4 !important; border: 1px solid #86efac !important;"
                                                     @click="openDetail(
                                                         'Koordinator Program Studi S2 Keperawatan',
                                                         'Program Studi Magister Keperawatan (Grade 10)',
                                                         {{ $prodiS2Anjab->bezetting }},
                                                         {{ $prodiS2Anjab->kebutuhan }},
                                                         '{{ $prodiS2Anjab->status }}',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S2%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         '{{ addslashes($prodiS2Anjab->ikhtisar) }}',
                                                         '{{ $prodiS2Anjab->anjab_url }}',
                                                         '{{ $prodiS2Anjab->abk_url }}'
                                                     )">
                                                    <div class="font-black text-[10.5px]" style="color: #064e3b !important;">2. S2 KEPERAWATAN</div>
                                                    <div class="text-[9.5px] text-gray-600">Bezetting: {{ $prodiS2Anjab->bezetting }} / Butuh: {{ $prodiS2Anjab->kebutuhan }} ({{ $prodiS2Anjab->status }})</div>
                                                </div>
                                                {{-- S3 --}}
                                                <div class="p-1.5 rounded cursor-pointer hover:opacity-90 transition"
                                                     style="background-color: #f0fdf4 !important; border: 1px solid #86efac !important;"
                                                     @click="openDetail(
                                                         'Koordinator Program Studi S3 Keperawatan',
                                                         'Program Studi Doktor Keperawatan (Grade 10)',
                                                         {{ $prodiS3Anjab->bezetting }},
                                                         {{ $prodiS3Anjab->kebutuhan }},
                                                         '{{ $prodiS3Anjab->status }}',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi S3%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         '{{ addslashes($prodiS3Anjab->ikhtisar) }}',
                                                         '{{ $prodiS3Anjab->anjab_url }}',
                                                         '{{ $prodiS3Anjab->abk_url }}'
                                                     )">
                                                    <div class="font-black text-[10.5px]" style="color: #064e3b !important;">3. S3 KEPERAWATAN</div>
                                                    <div class="text-[9.5px] text-gray-600">Bezetting: {{ $prodiS3Anjab->bezetting }} / Butuh: {{ $prodiS3Anjab->kebutuhan }} ({{ $prodiS3Anjab->status }})</div>
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
                                                         {{ $prodiNersAnjab->bezetting }},
                                                         {{ $prodiNersAnjab->kebutuhan }},
                                                         '{{ $prodiNersAnjab->status }}',
                                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Koordinator Prodi Ners%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                                         '{{ addslashes($prodiNersAnjab->ikhtisar) }}',
                                                         '{{ $prodiNersAnjab->anjab_url }}',
                                                         '{{ $prodiNersAnjab->abk_url }}'
                                                     )">
                                                    <div class="font-black text-[11px]" style="color: #115e59 !important;">1. NERS</div>
                                                    <div class="text-[9.5px] text-gray-600 mt-0.5">Bezetting: {{ $prodiNersAnjab->bezetting }} / Butuh: {{ $prodiNersAnjab->kebutuhan }} ({{ $prodiNersAnjab->status }})</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- ================================================================= --}}
                            {{-- SAYAP 2 (TENGAH): WAKIL DEKAN BIDANG KEUANGAN DAN UMUM             --}}
                            {{-- ================================================================= --}}
                            @php
                                $wd2Anjab = $getAnjab('Wakil Dekan Bidang Keuangan dan Umum');
                                $kabagAnjab = $getAnjab('Kepala Bagian Umum');
                                $pokjaAkadAnjab = $getAnjab('Ketua Pokja Akademik');
                                $pokjaKeuAnjab = $getAnjab('Ketua Pokja Keuangan');
                                $pokjaUmumAnjab = $getAnjab('Ketua Pokja Umum');
                            @endphp
                            <div class="flex flex-col items-center">
                                <div class="w-0.5 h-4" style="background-color: #1e3a8a !important;"></div>

                                {{-- Kartu WD II --}}
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG KEUANGAN DAN UMUM (WD II)',
                                         'Unsur Pimpinan Bidang Perencanaan, Anggaran & Kepegawaian (Grade 13)',
                                         {{ $wd2Anjab->bezetting }},
                                         {{ $wd2Anjab->kebutuhan }},
                                         '{{ $wd2Anjab->status }}',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan II%')->orWhere('nama_jabatan', 'like', '%Keuangan dan Umum%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         '{{ addslashes($wd2Anjab->ikhtisar) }}',
                                         '{{ $wd2Anjab->anjab_url }}',
                                         '{{ $wd2Anjab->abk_url }}'
                                     )">
                                    <div class="flex justify-between items-center text-[10px]">
                                        <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px;">UNSUR PIMPINAN</span>
                                        <span style="background-color: #fbbf24 !important; color: #1e3a8a !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 13</span>
                                    </div>
                                    <div class="font-black text-xs uppercase mt-2 text-center" style="color: #ffffff !important; letter-spacing: 0.3px; font-size: 12.5px;">
                                        WAKIL DEKAN BIDANG KEUANGAN DAN UMUM
                                    </div>
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">{{ $wd2Anjab->bezetting }}</strong> / Butuh: <strong style="color: #ffffff !important;">{{ $wd2Anjab->kebutuhan }}</strong></span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $wd2Anjab->selisih < 0 ? 'bg-rose-500 text-white' : ($wd2Anjab->selisih > 0 ? 'bg-amber-400 text-amber-950' : 'bg-emerald-500 text-white') }}">
                                            {{ $wd2Anjab->status }}
                                        </span>
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
                                         {{ $kabagAnjab->bezetting }},
                                         {{ $kabagAnjab->kebutuhan }},
                                         '{{ $kabagAnjab->status }}',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Kepala Bagian Umum%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         '{{ addslashes($kabagAnjab->ikhtisar) }}',
                                         '{{ $kabagAnjab->anjab_url }}',
                                         '{{ $kabagAnjab->abk_url }}'
                                     )">
                                    <div class="text-center font-black text-xs uppercase tracking-wide" style="color: #ffffff !important;">
                                        KEPALA BAGIAN UMUM
                                    </div>
                                    <div class="text-[9.5px] mt-1.5 pt-1 border-t border-white/20 text-amber-100 flex justify-between items-center px-1">
                                        <span>Bezetting: <strong>{{ $kabagAnjab->bezetting }}</strong>/{{ $kabagAnjab->kebutuhan }}</span>
                                        <span class="text-[8.5px] px-1.5 py-0.2 rounded font-bold {{ $kabagAnjab->selisih < 0 ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white' }}">
                                            {{ $kabagAnjab->status }}
                                        </span>
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
                                             'Ketua Pokja Akademik dan Kemahasiswaan',
                                             'Kelompok Kerja Layanan Registrasi, Perkuliahan & Kemahasiswaan (Grade 9 & 6)',
                                             {{ $pokjaAkadAnjab->bezetting }},
                                             {{ $pokjaAkadAnjab->kebutuhan }},
                                             '{{ $pokjaAkadAnjab->status }}',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 11)->orWhereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Pokja Akademik%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             '{{ addslashes($pokjaAkadAnjab->ikhtisar) }}',
                                             '{{ $pokjaAkadAnjab->anjab_url }}',
                                             '{{ $pokjaAkadAnjab->abk_url }}'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA AKADEMIK DAN KEMAHASISWAAN</div>
                                        <div class="text-[9.5px] text-gray-600 mt-0.5 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $pokjaAkadAnjab->bezetting }}</strong>/{{ $pokjaAkadAnjab->kebutuhan }}</span>
                                            <span class="text-[8.5px] font-bold px-1.5 rounded {{ $pokjaAkadAnjab->selisih < 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $pokjaAkadAnjab->status }}</span>
                                        </div>
                                    </div>

                                    {{-- Pokja 2: Keuangan & Kepegawaian --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ketua Pokja Keuangan dan Kepegawaian',
                                             'Kelompok Kerja Pengelolaan Anggaran, Presensi & Karir ASN (Grade 9 & 6)',
                                             {{ $pokjaKeuAnjab->bezetting }},
                                             {{ $pokjaKeuAnjab->kebutuhan }},
                                             '{{ $pokjaKeuAnjab->status }}',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 12)->orWhereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Pokja Keuangan%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             '{{ addslashes($pokjaKeuAnjab->ikhtisar) }}',
                                             '{{ $pokjaKeuAnjab->anjab_url }}',
                                             '{{ $pokjaKeuAnjab->abk_url }}'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA KEUANGAN DAN KEPEGAWAIAN</div>
                                        <div class="text-[9.5px] text-gray-600 mt-0.5 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $pokjaKeuAnjab->bezetting }}</strong>/{{ $pokjaKeuAnjab->kebutuhan }}</span>
                                            <span class="text-[8.5px] font-bold px-1.5 rounded {{ $pokjaKeuAnjab->selisih < 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $pokjaKeuAnjab->status }}</span>
                                        </div>
                                    </div>

                                    {{-- Pokja 3: Umum dan Sarana Akademik --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ketua Pokja Umum dan Sarana Akademik',
                                             'Kelompok Kerja Pengelolaan BMN, Perlengkapan & Sarana (Grade 9 & 6)',
                                             {{ $pokjaUmumAnjab->bezetting }},
                                             {{ $pokjaUmumAnjab->kebutuhan }},
                                             '{{ $pokjaUmumAnjab->status }}',
                                             {{ \App\Models\Pegawai::where('unit_kerja_id', 13)->orWhereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Pokja Umum%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             '{{ addslashes($pokjaUmumAnjab->ikhtisar) }}',
                                             '{{ $pokjaUmumAnjab->anjab_url }}',
                                             '{{ $pokjaUmumAnjab->abk_url }}'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA UMUM DAN SARANA AKADEMIK</div>
                                        <div class="text-[9.5px] text-gray-600 mt-0.5 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $pokjaUmumAnjab->bezetting }}</strong>/{{ $pokjaUmumAnjab->kebutuhan }}</span>
                                            <span class="text-[8.5px] font-bold px-1.5 rounded {{ $pokjaUmumAnjab->selisih < 0 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $pokjaUmumAnjab->status }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ================================================================= --}}
                            {{-- SAYAP 3 (KANAN): WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI & KERJASAMA--}}
                            {{-- ================================================================= --}}
                            @php
                                $wd3Anjab = $getAnjab('Wakil Dekan Bidang Kemahasiswaan');
                            @endphp
                            <div class="flex flex-col items-center">
                                <div class="w-0.5 h-4" style="background-color: #1e3a8a !important;"></div>

                                {{-- Kartu WD III --}}
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI & KERJASAMA (WD III)',
                                         'Unsur Pimpinan Bidang Penalaran, Minat Bakat, Tracer Study & Kemitraan (Grade 13)',
                                         {{ $wd3Anjab->bezetting }},
                                         {{ $wd3Anjab->kebutuhan }},
                                         '{{ $wd3Anjab->status }}',
                                         {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Wakil Dekan III%')->orWhere('nama_jabatan', 'like', '%Kemahasiswaan%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                         '{{ addslashes($wd3Anjab->ikhtisar) }}',
                                         '{{ $wd3Anjab->anjab_url }}',
                                         '{{ $wd3Anjab->abk_url }}'
                                     )">
                                    <div class="flex justify-between items-center text-[10px]">
                                        <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px;">UNSUR PIMPINAN</span>
                                        <span style="background-color: #fbbf24 !important; color: #1e3a8a !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 13</span>
                                    </div>
                                    <div class="font-black text-xs uppercase mt-2 text-center" style="color: #ffffff !important; letter-spacing: 0.3px; font-size: 12px; line-height: 1.3;">
                                        WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI DAN KERJASAMA
                                    </div>
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">{{ $wd3Anjab->bezetting }}</strong> / Butuh: <strong style="color: #ffffff !important;">{{ $wd3Anjab->kebutuhan }}</strong></span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $wd3Anjab->selisih < 0 ? 'bg-rose-500 text-white' : ($wd3Anjab->selisih > 0 ? 'bg-amber-400 text-amber-950' : 'bg-emerald-500 text-white') }}">
                                            {{ $wd3Anjab->status }}
                                        </span>
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
                    @php
                        $dosenAnjab = $getAnjab('Dosen');
                        $unitKhususAnjab = $getAnjab('Unit Khusus');
                        $kepalaLabAnjab = $getAnjab('Kepala Laboratorium');
                        $plpAnjab = $getAnjab('Pranata Laboratorium');
                    @endphp
                    <div class="w-full grid grid-cols-3 gap-6 items-start">

                        {{-- --------------------------------------------------------------------- --}}
                        {{-- PILAR 1 (KIRI): KELOMPOK JABATAN FUNGSIONAL DOSEN (KJFD - 9 BIDANG)   --}}
                        {{-- --------------------------------------------------------------------- --}}
                        <div class="p-4 rounded-xl shadow-sm" style="background-color: #ffffff !important; border: 2.5px solid #059669 !important;">
                            <div style="background: linear-gradient(135deg, #059669 0%, #047857 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11.5px; letter-spacing: 0.5px; padding: 8px 10px; border-radius: 8px; text-align: center; text-transform: uppercase; box-shadow: 0 2px 4px rgba(5, 150, 105, 0.3); margin-bottom: 6px;">
                                KELOMPOK JABATAN FUNGSIONAL DOSEN (KJFD)
                            </div>
                            <div class="text-[9.5px] p-1.5 mb-2 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 flex justify-between items-center font-bold">
                                <span>Bezetting: <strong>{{ $dosenAnjab->bezetting }}</strong> / Butuh: <strong>{{ $dosenAnjab->kebutuhan }}</strong></span>
                                <span class="px-1.5 py-0.2 rounded {{ $dosenAnjab->selisih < 0 ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white' }}">{{ $dosenAnjab->status }}</span>
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
                                             'KJFD Keperawatan: {{ $k }}',
                                             'Kelompok Jabatan Fungsional Dosen (Grade 9-14)',
                                             {{ $dosenAnjab->bezetting }},
                                             {{ $dosenAnjab->kebutuhan }},
                                             '{{ $dosenAnjab->status }}',
                                             {{ \App\Models\Pegawai::where('jenis_pegawai', 'like', '%Dosen%')->take(6)->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             'Melaksanakan tridharma perguruan tinggi pada rumpun keahlian {{ $k }}, pembimbingan tugas akhir, praktikum klinik dan riset keperawatan. {{ addslashes($dosenAnjab->ikhtisar) }}',
                                             '{{ $dosenAnjab->anjab_url }}',
                                             '{{ $dosenAnjab->abk_url }}'
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
                            <div style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11.5px; letter-spacing: 0.5px; padding: 8px 10px; border-radius: 8px; text-align: center; text-transform: uppercase; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.3); margin-bottom: 6px;">
                                UNIT-UNIT FUNGSIONAL
                            </div>
                            <div class="text-[9.5px] p-1.5 mb-2 rounded bg-sky-50 border border-sky-200 text-sky-800 flex justify-between items-center font-bold">
                                <span>Bezetting: <strong>{{ $unitKhususAnjab->bezetting }}</strong> / Butuh: <strong>{{ $unitKhususAnjab->kebutuhan }}</strong></span>
                                <span class="px-1.5 py-0.2 rounded {{ $unitKhususAnjab->selisih < 0 ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white' }}">{{ $unitKhususAnjab->status }}</span>
                            </div>
                            <div class="space-y-1.5 text-xs">
                                @php
                                    $unitFung = [
                                        '1. ETIK RISET',
                                        '2. KOMITE ETIK',
                                        '3. COMPUTER BASED TEST (CBT)',
                                        '4. KERJASAMA',
                                        '5. PENELITIAN DAN PENGABMASY',
                                        '6. NURSING EDUCATION DEVELOPMENT UNIT (NEDU)',
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
                                             {{ $unitKhususAnjab->bezetting }},
                                             {{ $unitKhususAnjab->kebutuhan }},
                                             '{{ $unitKhususAnjab->status }}',
                                             [],
                                             'Melaksanakan fungsi penunjang akademik, kepatuhan etik, pengujian CBT, kemitraan institusi, bimbingan konseling dan layanan keterbukaan informasi publik. {{ addslashes($unitKhususAnjab->ikhtisar) }}',
                                             '{{ $unitKhususAnjab->anjab_url }}',
                                             '{{ $unitKhususAnjab->abk_url }}'
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
                                     {{ $plpAnjab->bezetting }},
                                     {{ $plpAnjab->kebutuhan }},
                                     '{{ $plpAnjab->status }}',
                                     {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Laboran%')->orWhere('nama_jabatan', 'like', '%PLP%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                     '{{ addslashes($plpAnjab->ikhtisar) }}',
                                     '{{ $plpAnjab->anjab_url }}',
                                     '{{ $plpAnjab->abk_url }}'
                                 )">
                                <div class="font-black text-[11px]" style="color: #312e81 !important;">Kepala Lab & Pranata Lab (PLP)</div>
                                <span class="font-extrabold text-[9.5px] block mt-0.5" style="color: {{ $plpAnjab->selisih < 0 ? '#dc2626' : '#059669' }} !important;">
                                    Bezetting: {{ $plpAnjab->bezetting }} / Kebutuhan: {{ $plpAnjab->kebutuhan }} ({{ $plpAnjab->status }})
                                </span>
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
                                             {{ $plpAnjab->bezetting }},
                                             {{ $plpAnjab->kebutuhan }},
                                             '{{ $plpAnjab->status }}',
                                             {{ \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Laboran%')->orWhere('nama_jabatan', 'like', '%PLP%'))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']) }},
                                             'Fasilitas praktikum simulasi medis, manikin keperawatan, dan ujian Objective Structured Clinical Examination (OSCE). {{ addslashes($kepalaLabAnjab->ikhtisar) }}',
                                             '{{ $kepalaLabAnjab->anjab_url }}',
                                             '{{ $kepalaLabAnjab->abk_url }}'
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

                <div class="mt-6 pt-4 border-t border-gray-200 flex flex-wrap items-center justify-end gap-2">
                    <button @click="showModal = false" class="px-3.5 py-2 text-xs font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                        Tutup
                    </button>
                    <template x-if="activeNode && activeNode.anjabUrl && activeNode.anjabUrl !== '#'">
                        <a :href="activeNode.anjabUrl" class="px-3.5 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                            📑 Dokumen Anjab
                        </a>
                    </template>
                    <template x-if="activeNode && activeNode.abkUrl && activeNode.abkUrl !== '#'">
                        <a :href="activeNode.abkUrl" class="px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            🧮 Rincian / Formasi ABK ➔
                        </a>
                    </template>
                    <template x-if="!activeNode || !activeNode.abkUrl || activeNode.abkUrl === '#'">
                        <a :href="'{{ route('abk.index') }}'" class="px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                            Buka Rekap ABK ➔
                        </a>
                    </template>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
