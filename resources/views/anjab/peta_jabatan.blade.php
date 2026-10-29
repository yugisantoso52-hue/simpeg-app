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
                {{-- Tombol SOP Peta Jabatan & ABK --}}
                <x-sop-modal title="SOP Analisis Jabatan & Beban Kerja (Anjab - ABK)" 
                             buttonLabel="SOP Anjab & ABK" 
                             badge="PermenPAN-RB No. 1/2020" 
                             color="indigo">
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200">
                            <h4 class="font-bold text-indigo-950 text-xs mb-1 flex items-center gap-1.5">
                                <x-icon name="network" class="w-4 h-4 text-indigo-700" />
                                Dasar Hukum &amp; Tujuan Penyusunan
                            </h4>
                            <p class="text-indigo-900 text-[11px] leading-relaxed">
                                Berdasarkan <strong>PermenPAN-RB No. 1 Tahun 2020</strong>, peta jabatan dan ABK menyajikan gambaran komprehensif formasi jabatan struktural, fungsional dosen, dan pelaksana untuk menghitung kebutuhan riil aparatur berdasarkan beban kerja 1.250 jam/tahun.
                            </p>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px]">1</span>
                                Cara Membaca Indikator Formasi Bagan
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-indigo-200">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Bezetting (Kondisi Riil)</strong>
                                    <span class="text-slate-600 text-[11px]">Jumlah ASN (PNS &amp; PPPK) yang secara aktual menduduki jabatan tersebut saat ini.</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Kebutuhan Formasi (Hasil ABK)</strong>
                                    <span class="text-slate-600 text-[11px]">Jumlah ideal personil yang dihitung dari total volume kerja tahunan dibagi jam kerja efektif.</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Status Keseimbangan Formasi</strong>
                                    <span class="text-slate-600 text-[11px]">
                                        <span class="text-emerald-700 font-semibold">🟢 Ideal:</span> Formasi terpenuhi pas.<br>
                                        <span class="text-rose-700 font-semibold">🔴 Kurang (Defisit):</span> Kekurangan SDM, menjadi prioritas usulan formasi baru ke kementerian.<br>
                                        <span class="text-amber-700 font-semibold">🟡 Lebih (Surplus):</span> Kelebihan personil, bahan evaluasi untuk redistribusi/mutasi internal.
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-slate-800 text-xs mb-2 uppercase tracking-wider flex items-center gap-1.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px]">2</span>
                                17 Unsur Dokumen Anjab &amp; Alur Pemutakhiran
                            </h4>
                            <div class="space-y-2 ps-2 border-l-2 border-emerald-200">
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Akses Dokumen Spesifikasi</strong>
                                    <span class="text-slate-600 text-[11px]">Klik salah satu kotak jabatan pada bagan untuk langsung membuka rincian 17 unsur Anjab (kualifikasi, uraian tugas pokok, bahan/perangkat kerja, wewenang, dan risiko).</span>
                                </div>
                                <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                                    <strong class="text-slate-800 block text-xs">Pengusulan Pembaruan</strong>
                                    <span class="text-slate-600 text-[11px]">Setiap terjadi perubahan OTK (Organisasi dan Tata Kerja), Subbagian Tata Usaha / Kepegawaian memperbarui matriks tugas dan disahkan Dekan.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-sop-modal>

                <button onclick="window.print()" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 shadow-sm transition">
                    <span>🖨️</span> Cetak Bagan
                </button>
                @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
                    <a href="{{ route('abk.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition">
                        <span>🧮</span> Rekap ABK & Formasi
                    </a>
                    <a href="{{ route('anjab.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-sm transition">
                        <span>📑</span> Katalog Anjab
                    </a>
                @endif
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
            if (!$anjab) {
                if (str_contains($key, 'keuangan') || str_contains($key, 'keu')) {
                    foreach ($anjabMap as $k => $item) {
                        if (str_contains($k, 'keu') && str_contains($k, 'pokja')) { $anjab = $item; break; }
                    }
                } elseif (str_contains($key, 'sarana') || (str_contains($key, 'umum') && str_contains($key, 'pokja'))) {
                    foreach ($anjabMap as $k => $item) {
                        if (str_contains($k, 'sarana') && str_contains($k, 'pokja')) { $anjab = $item; break; }
                    }
                } elseif (str_contains($key, 'akademik') && str_contains($key, 'pokja')) {
                    foreach ($anjabMap as $k => $item) {
                        if (str_contains($k, 'akademik') && str_contains($k, 'pokja') && !str_contains($k, 'sarana')) { $anjab = $item; break; }
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

        $getKelompok = function($cleanName) use ($kelompokMap) {
            $k = strtolower(trim(preg_replace('/^\d+\.\s*/', '', (string)$cleanName)));
            $found = $kelompokMap[$k] ?? null;
            if (!$found) {
                foreach ($kelompokMap as $name => $item) {
                    if (str_contains($name, $k) || str_contains($k, $name)) {
                        $found = $item;
                        break;
                    }
                }
            }
            $pegawaiList = $found ? $found->penugasan->map(function($p) {
                $peg = $p->pegawai;
                if (!$peg) return null;
                return [
                    'id' => $peg->id,
                    'nama' => $peg->nama,
                    'gelar_depan' => $peg->gelar_depan,
                    'gelar_belakang' => $peg->gelar_belakang,
                    'nip' => $peg->nip,
                    'foto' => $peg->foto,
                    'peran' => $p->peran ?: 'Anggota',
                    'keterangan' => $p->keterangan,
                ];
            })->filter()->values() : collect();
            $bezetting = $pegawaiList->count();
            $kebutuhan = $found?->kebutuhan ?? 1;
            $selisih = $bezetting - $kebutuhan;
            $status = $selisih < 0 ? ('🔴 Kurang ' . abs($selisih)) : ($selisih > 0 ? ('🟡 Lebih +' . $selisih) : '🟢 Ideal');
            return (object) [
                'item'        => $found,
                'bezetting'   => $bezetting,
                'kebutuhan'   => $kebutuhan,
                'status'      => $status,
                'pegawaiList' => $pegawaiList,
            ];
        };

        $getPejabatFormasi = function(array $jabatanKeywords, array $kelompokKeywords, int $targetKebutuhan = 1) use ($kelompokPenugasan) {
            $fromKelompok = collect();
            foreach ($kelompokPenugasan as $kel) {
                $kName = strtolower(trim($kel->nama));
                $match = false;
                foreach ($kelompokKeywords as $kw) {
                    if (str_contains($kName, strtolower(trim($kw)))) {
                        $match = true;
                        break;
                    }
                }
                if ($match) {
                    foreach ($kel->penugasan as $pen) {
                        $p = $pen->pegawai;
                        if ($p && ($p->status_pegawai === 'Aktif' || empty($p->status_pegawai))) {
                            $namaGelar = trim(($p->gelar_depan ? $p->gelar_depan . ' ' : '') . $p->nama . ($p->gelar_belakang ? ', ' . $p->gelar_belakang : ''));
                            $fromKelompok->push([
                                'id'             => $p->id,
                                'nama'           => $p->nama,
                                'gelar_depan'    => $p->gelar_depan,
                                'gelar_belakang' => $p->gelar_belakang,
                                'nama_lengkap'   => $namaGelar ?: $p->nama,
                                'nip'            => $p->nip,
                                'foto'           => $p->foto,
                                'peran'          => $pen->peran ?: 'Anggota',
                                'keterangan'     => $pen->keterangan,
                            ]);
                        }
                    }
                }
            }

            $fromJabatan = \App\Models\Pegawai::where('status_pegawai', 'Aktif')
                ->where(function($q) use ($jabatanKeywords) {
                    $q->whereHas('jabatan', function($sq) use ($jabatanKeywords) {
                        $sq->where(function($ssq) use ($jabatanKeywords) {
                            foreach ($jabatanKeywords as $kw) {
                                $ssq->orWhere('nama_jabatan', 'like', "%{$kw}%");
                            }
                        });
                    })->orWhereHas('riwayatJabatan', function($sq) use ($jabatanKeywords) {
                        $sq->where('status', 'aktif')
                           ->whereHas('jabatan', function($ssq) use ($jabatanKeywords) {
                               $ssq->where(function($sssq) use ($jabatanKeywords) {
                                   foreach ($jabatanKeywords as $kw) {
                                       $sssq->orWhere('nama_jabatan', 'like', "%{$kw}%");
                                   }
                               });
                           });
                    });
                })
                ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto'])
                ->map(function($p) {
                    $namaGelar = trim(($p->gelar_depan ? $p->gelar_depan . ' ' : '') . $p->nama . ($p->gelar_belakang ? ', ' . $p->gelar_belakang : ''));
                    return [
                        'id'             => $p->id,
                        'nama'           => $p->nama,
                        'gelar_depan'    => $p->gelar_depan,
                        'gelar_belakang' => $p->gelar_belakang,
                        'nama_lengkap'   => $namaGelar ?: $p->nama,
                        'nip'            => $p->nip,
                        'foto'           => $p->foto,
                        'peran'          => 'Pejabat',
                        'keterangan'     => null,
                    ];
                });

            $pegawaiList = $fromKelompok->concat($fromJabatan)->unique('id')->values();
            $bezetting = $pegawaiList->count();
            $kebutuhan = $targetKebutuhan;
            $selisih = $bezetting - $kebutuhan;
            $status = $selisih < 0 ? ('🔴 Kurang ' . abs($selisih)) : ($selisih > 0 ? ('🟡 Lebih +' . $selisih) : '🟢 Ideal');

            return (object) [
                'bezetting'    => $bezetting,
                'kebutuhan'    => $kebutuhan,
                'selisih'      => $selisih,
                'status'       => $status,
                'pegawaiList'  => $pegawaiList,
                'pejabatUtama' => $pegawaiList->first(),
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
        <div class="w-full max-w-[1920px] mx-auto space-y-6 px-3 sm:px-6 lg:px-8 xl:px-10">

            {{-- Indikator Legenda --}}
            <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm flex flex-wrap items-center justify-between gap-4 text-xs">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-gray-700 uppercase tracking-wider">Status Formasi ABK:</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                        🟢 Ideal (Cukup)
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-rose-100 text-rose-800 border border-rose-300">
                        🔴 Kurang Pegawai
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold bg-amber-100 text-amber-800 border border-amber-300">
                        🟡 Kelebihan Pegawai
                    </span>
                </div>
                <div class="text-gray-500 font-medium">
                    💡 <span class="italic">Klik pada kotak jabatan atau unit untuk melihat daftar pejabat, foto, NIP & rincian tugas</span>
                </div>
            </div>

            {{-- Alert Pengajuan Jabatan Struktural Menunggu Verifikasi --}}
            @if(isset($pengajuanMenunggu) && $pengajuanMenunggu->isNotEmpty())
                <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-xl flex flex-col md:flex-row md:items-center justify-between gap-3 text-xs text-amber-900 shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-xl">⏳</span>
                        <div>
                            <strong>Terdapat {{ $pengajuanMenunggu->count() }} pengajuan jabatan struktural</strong> yang sedang menunggu verifikasi SK.
                            Posisi struktur organisasi akan otomatis terisi dan bezetting bertambah setelah berkas SK disetujui.
                        </div>
                    </div>
                    @if(auth()->user()->canVerifyJabatan())
                        <a href="{{ route('pengajuan-jabatan.index', ['status' => 'menunggu']) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg transition shrink-0">
                            Verifikasi Sekarang ➔
                        </a>
                    @endif
                </div>
            @endif

            {{-- KANVAS BAGAN STRUKTUR ORGANISASI (SESUAI DOKUMEN RESMI TERBARU FKP UNRI) --}}
            <div class="bg-gradient-to-b from-sky-50/50 via-white to-gray-50 rounded-2xl border-2 border-blue-200/80 p-6 md:p-8 shadow-md overflow-x-auto">

                {{-- Header Resmi Bagan --}}
                <div class="text-center pb-6 border-b-2 border-blue-200 mb-8 flex items-center justify-between px-4">
                    {{-- Logo UNRI --}}
                    <div class="shrink-0 flex items-center">
                        <img src="{{ asset('logo-unri.png') }}" 
                             alt="Logo UNRI" 
                             class="h-14 sm:h-16 md:h-20 w-auto object-contain drop-shadow-sm">
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

                    {{-- Logo KEMDIKTI (Kemendiktisaintek) --}}
                    <div class="shrink-0 flex items-center">
                        <img src="{{ asset('logo-kemendiktisaintek.png') }}" 
                             alt="Logo Kemendiktisaintek" 
                             class="h-14 sm:h-16 md:h-20 w-auto object-contain drop-shadow-sm">
                    </div>
                </div>

                {{-- TREE DIAGRAM CONTAINER --}}
                <div class="min-w-[1150px] flex flex-col items-center gap-6">

                    {{-- ========================================================================= --}}
                    {{-- LEVEL 1: DEKAN, SATUAN PENJAMINAN MUTU (SPMF), DAN SENAT FAKULTAS         --}}
                    {{-- ========================================================================= --}}
                    <div class="w-full flex items-center justify-center gap-4 relative">

                        {{-- Sayap Kiri: SATUAN PENJAMINAN MUTU (SPMF) dengan GPM S1, S2, NERS --}}
                        @php
                            $gpmS1 = $getPejabatFormasi(['GPM S1', 'GPM%S1', 'GPM S1 Keperawatan'], ['GPM S1'], 1);
                            $gpmS2 = $getPejabatFormasi(['GPM S2', 'GPM%S2', 'GPM S2 Keperawatan'], ['GPM S2'], 1);
                            $gpmNers = $getPejabatFormasi(['GPM Profesi Ners', 'GPM NERS', 'GPM%Ners', 'GPM%Profesi Ners'], ['GPM NERS', 'GPM-NERS', 'GPM Profesi Ners'], 1);

                            $spmfTotalBezetting = $gpmS1->bezetting + $gpmS2->bezetting + $gpmNers->bezetting;
                            $spmfTotalKebutuhan = 3;
                            $spmfSelisih = $spmfTotalBezetting - $spmfTotalKebutuhan;
                            $spmfStatus = $spmfSelisih < 0 ? ('🔴 Kurang ' . abs($spmfSelisih)) : ($spmfSelisih > 0 ? ('🟡 Lebih +' . $spmfSelisih) : '🟢 Ideal');
                            $spmfListAll = $gpmS1->pegawaiList->concat($gpmS2->pegawaiList)->concat($gpmNers->pegawaiList)->unique('id')->values();
                        @endphp
                        <div class="w-80">
                            <div class="p-3.5 rounded-xl shadow-md transition"
                                 style="background-color: #ffffff !important; border: 2px solid #0284c7 !important;">
                                <div class="cursor-pointer hover:opacity-95 transition"
                                     style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11px; letter-spacing: 0.3px; padding: 6px 8px; border-radius: 6px; text-align: center; text-transform: uppercase;"
                                     @click="openDetail(
                                         'SATUAN PENJAMINAN MUTU (SPMF)',
                                         'Unsur Penjaminan Mutu & Gugus Penjamin Mutu (GPM S1, S2, Profesi Ners)',
                                         {{ $spmfTotalBezetting }},
                                         {{ $spmfTotalKebutuhan }},
                                         '{{ $spmfStatus }}',
                                         {{ $spmfListAll }},
                                         'Menyelenggarakan penjaminan mutu akademik secara konsisten dan berkelanjutan di tingkat program studi S1, S2, dan Profesi Ners.'
                                     )">
                                    SATUAN PENJAMINAN MUTU (SPMF)
                                </div>
                                <div class="mt-2.5 space-y-2 text-xs">
                                    {{-- GPM S1 (1 org) --}}
                                    <div class="p-2 rounded-lg cursor-pointer hover:scale-[1.01] transition"
                                         style="background-color: #f0f9ff !important; border: 1.5px solid #bae6fd !important;"
                                         @click.stop="openDetail(
                                             'Gugus Penjamin Mutu (GPM) S1 Keperawatan',
                                             'Petugas Penjaminan Mutu Program Studi S1 Keperawatan (Formasi: 1 Org)',
                                             {{ $gpmS1->bezetting }},
                                             1,
                                             '{{ $gpmS1->status }}',
                                             {{ $gpmS1->pegawaiList }},
                                             'Melaksanakan penjaminan mutu proses pembelajaran, kurikulum, dan evaluasi capaian pembelajaran lulusan Prodi S1.'
                                         )">
                                        <div class="flex items-center justify-between">
                                            <span class="font-black text-[11px]" style="color: #0369a1 !important;">GPM S1</span>
                                            <span class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full {{ $gpmS1->bezetting >= 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                                {{ $gpmS1->status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[10.5px] font-medium text-slate-700 truncate">
                                            @if($gpmS1->pejabatUtama)
                                                <span class="text-blue-700 font-bold">👤 {{ $gpmS1->pejabatUtama['nama_lengkap'] }}</span>
                                            @else
                                                <span class="italic text-slate-400">👤 (Belum diisi / Butuh 1 org)</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-[9.5px] text-slate-500">
                                            Bezetting: <strong class="text-slate-800">{{ $gpmS1->bezetting }}</strong> / Butuh: <strong>1</strong> org
                                        </div>
                                    </div>

                                    {{-- GPM S2 (1 org) --}}
                                    <div class="p-2 rounded-lg cursor-pointer hover:scale-[1.01] transition"
                                         style="background-color: #f0f9ff !important; border: 1.5px solid #bae6fd !important;"
                                         @click.stop="openDetail(
                                             'Gugus Penjamin Mutu (GPM) S2 Keperawatan',
                                             'Petugas Penjaminan Mutu Program Studi Magister (S2) Keperawatan (Formasi: 1 Org)',
                                             {{ $gpmS2->bezetting }},
                                             1,
                                             '{{ $gpmS2->status }}',
                                             {{ $gpmS2->pegawaiList }},
                                             'Melaksanakan penjaminan mutu proses pembelajaran, riset tesis, dan kurikulum Prodi S2 Keperawatan.'
                                         )">
                                        <div class="flex items-center justify-between">
                                            <span class="font-black text-[11px]" style="color: #0369a1 !important;">GPM S2</span>
                                            <span class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full {{ $gpmS2->bezetting >= 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                                {{ $gpmS2->status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[10.5px] font-medium text-slate-700 truncate">
                                            @if($gpmS2->pejabatUtama)
                                                <span class="text-blue-700 font-bold">👤 {{ $gpmS2->pejabatUtama['nama_lengkap'] }}</span>
                                            @else
                                                <span class="italic text-slate-400">👤 (Belum diisi / Butuh 1 org)</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-[9.5px] text-slate-500">
                                            Bezetting: <strong class="text-slate-800">{{ $gpmS2->bezetting }}</strong> / Butuh: <strong>1</strong> org
                                        </div>
                                    </div>

                                    {{-- GPM NERS (1 org) --}}
                                    <div class="p-2 rounded-lg cursor-pointer hover:scale-[1.01] transition"
                                         style="background-color: #f0f9ff !important; border: 1.5px solid #bae6fd !important;"
                                         @click.stop="openDetail(
                                             'Gugus Penjamin Mutu (GPM) Profesi Ners',
                                             'Petugas Penjaminan Mutu Program Studi Profesi Ners (Formasi: 1 Org)',
                                             {{ $gpmNers->bezetting }},
                                             1,
                                             '{{ $gpmNers->status }}',
                                             {{ $gpmNers->pegawaiList }},
                                             'Melaksanakan penjaminan mutu stase klinik, uji kompetensi, dan evaluasi praktik Profesi Ners.'
                                         )">
                                        <div class="flex items-center justify-between">
                                            <span class="font-black text-[11px]" style="color: #0369a1 !important;">GPM NERS</span>
                                            <span class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full {{ $gpmNers->bezetting >= 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                                {{ $gpmNers->status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[10.5px] font-medium text-slate-700 truncate">
                                            @if($gpmNers->pejabatUtama)
                                                <span class="text-blue-700 font-bold">👤 {{ $gpmNers->pejabatUtama['nama_lengkap'] }}</span>
                                            @else
                                                <span class="italic text-slate-400">👤 (Belum diisi / Butuh 1 org)</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-[9.5px] text-slate-500">
                                            Bezetting: <strong class="text-slate-800">{{ $gpmNers->bezetting }}</strong> / Butuh: <strong>1</strong> org
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer Total Bezetting SPMF --}}
                                <div class="mt-2.5 pt-2 flex items-center justify-between text-[10px] border-t border-sky-100 text-slate-600">
                                    <span>Total: <strong>{{ $spmfTotalBezetting }}</strong> / Butuh: <strong>3</strong></span>
                                    <span class="font-bold px-2 py-0.5 rounded-full {{ $spmfTotalBezetting >= 3 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $spmfStatus }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Garis Solid Penghubung Kiri ke Dekan --}}
                        <div class="h-0.5 w-12" style="background-color: #0284c7 !important;"></div>

                        {{-- Pusat Puncak: KOTAK DEKAN --}}
                        @php
                            $dekanAnjab = $getAnjab('Dekan');
                            $dekanPejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->whereRaw('LOWER(nama_jabatan) = ?', ['dekan']))->first();
                            $dekanList = \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->whereRaw('LOWER(nama_jabatan) = ?', ['dekan']))->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                        @endphp
                        <div class="w-80">
                            <div class="p-4 rounded-2xl shadow-xl hover:scale-105 transition cursor-pointer"
                                 style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important; color: #ffffff !important; border: 2.5px solid #fbbf24 !important; box-shadow: 0 6px 15px rgba(30, 58, 138, 0.4) !important;"
                                 @click="openDetail(
                                     'DEKAN',
                                     'Pimpinan Tertinggi Fakultas Keperawatan UNRI (Grade 15)',
                                     {{ $dekanAnjab->bezetting }},
                                     {{ $dekanAnjab->kebutuhan }},
                                     '{{ $dekanAnjab->status }}',
                                     {{ $dekanList }},
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
                                @if($dekanPejabat)
                                    <div class="text-[11px] text-amber-300 font-bold text-center truncate mt-0.5" title="{{ $dekanPejabat->nama_lengkap }}">
                                        👤 {{ $dekanPejabat->nama_lengkap }}
                                    </div>
                                @endif
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

                        {{-- Sayap Kanan: SENAT FAKULTAS (Ketua, Sekretaris, Komisi Akademik & Etik) --}}
                        @php
                            $ketuaSenat = $getPejabatFormasi(['Ketua Senat', 'Ketua Senat Fakultas'], ['Ketua Senat'], 1);
                            $sekretarisSenat = $getPejabatFormasi(['Sekretaris Senat', 'Sekretaris Senat Fakultas'], ['Sekretaris Senat'], 1);
                            $komisiSenat = $getPejabatFormasi(['Komisi Akademik & Etik', 'Komisi Akademik', 'Akademik & Etik Senat'], ['Komisi Akademik & Etik', 'Komisi Akademik'], 3);

                            $senatTotalBezetting = $ketuaSenat->bezetting + $sekretarisSenat->bezetting + $komisiSenat->bezetting;
                            $senatTotalKebutuhan = 1 + 1 + $komisiSenat->kebutuhan;
                            $senatSelisih = $senatTotalBezetting - $senatTotalKebutuhan;
                            $senatStatus = $senatSelisih < 0 ? ('🔴 Kurang ' . abs($senatSelisih)) : ($senatSelisih > 0 ? ('🟡 Lebih +' . $senatSelisih) : '🟢 Ideal');
                            $senatListAll = $ketuaSenat->pegawaiList->concat($sekretarisSenat->pegawaiList)->concat($komisiSenat->pegawaiList)->unique('id')->values();
                        @endphp
                        <div class="w-80">
                            <div class="p-3.5 rounded-xl shadow-md transition"
                                 style="background-color: #ffffff !important; border: 2px dashed #475569 !important;">
                                <div class="cursor-pointer hover:opacity-95 transition"
                                     style="background: linear-gradient(135deg, #334155 0%, #1e293b 100%) !important; color: #ffffff !important; font-weight: 900; font-size: 11px; letter-spacing: 0.3px; padding: 6px 8px; border-radius: 6px; text-align: center; text-transform: uppercase;"
                                     @click="openDetail(
                                         'SENAT FAKULTAS',
                                         'Badan Pertimbangan Normatif Fakultas (Ketua, Sekretaris, Komisi Akademik & Etik)',
                                         {{ $senatTotalBezetting }},
                                         {{ $senatTotalKebutuhan }},
                                         '{{ $senatStatus }}',
                                         {{ $senatListAll }},
                                         'Merumuskan kebijakan akademik, memberikan pertimbangan dan pengawasan terhadap pelaksanaan proses akademik di lingkungan Fakultas Keperawatan.'
                                     )">
                                    SENAT FAKULTAS
                                </div>
                                <div class="mt-2.5 space-y-2 text-xs">
                                    {{-- Ketua Senat (1 org) --}}
                                    <div class="p-2 rounded-lg cursor-pointer hover:scale-[1.01] transition"
                                         style="background-color: #f8fafc !important; border: 1.5px solid #cbd5e1 !important;"
                                         @click.stop="openDetail(
                                             'Ketua Senat Fakultas Keperawatan',
                                             'Pimpinan Badan Normatif Senat Fakultas (Formasi: 1 Org)',
                                             {{ $ketuaSenat->bezetting }},
                                             1,
                                             '{{ $ketuaSenat->status }}',
                                             {{ $ketuaSenat->pegawaiList }},
                                             'Memimpin rapat senat, mengoordinasikan perumusan norma akademik, dan memberikan pertimbangan kepada pimpinan fakultas.'
                                         )">
                                        <div class="flex items-center justify-between">
                                            <span class="font-black text-[11px]" style="color: #1e293b !important;">KETUA SENAT</span>
                                            <span class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full {{ $ketuaSenat->bezetting >= 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                                {{ $ketuaSenat->status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[10.5px] font-medium text-slate-700 truncate">
                                            @if($ketuaSenat->pejabatUtama)
                                                <span class="text-indigo-900 font-bold">👤 {{ $ketuaSenat->pejabatUtama['nama_lengkap'] }}</span>
                                            @else
                                                <span class="italic text-slate-400">👤 (Belum diisi / Butuh 1 org)</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-[9.5px] text-slate-500">
                                            Bezetting: <strong class="text-slate-800">{{ $ketuaSenat->bezetting }}</strong> / Butuh: <strong>1</strong> org
                                        </div>
                                    </div>

                                    {{-- Sekretaris Senat (1 org) --}}
                                    <div class="p-2 rounded-lg cursor-pointer hover:scale-[1.01] transition"
                                         style="background-color: #f8fafc !important; border: 1.5px solid #cbd5e1 !important;"
                                         @click.stop="openDetail(
                                             'Sekretaris Senat Fakultas Keperawatan',
                                             'Sekretariat & Administrasi Pertimbangan Senat Fakultas (Formasi: 1 Org)',
                                             {{ $sekretarisSenat->bezetting }},
                                             1,
                                             '{{ $sekretarisSenat->status }}',
                                             {{ $sekretarisSenat->pegawaiList }},
                                             'Mengelola administrasi persidangan senat, notulensi rapat, dan dokumentasi pertimbangan senat.'
                                         )">
                                        <div class="flex items-center justify-between">
                                            <span class="font-black text-[11px]" style="color: #1e293b !important;">SEKRETARIS SENAT</span>
                                            <span class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full {{ $sekretarisSenat->bezetting >= 1 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                                {{ $sekretarisSenat->status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[10.5px] font-medium text-slate-700 truncate">
                                            @if($sekretarisSenat->pejabatUtama)
                                                <span class="text-indigo-900 font-bold">👤 {{ $sekretarisSenat->pejabatUtama['nama_lengkap'] }}</span>
                                            @else
                                                <span class="italic text-slate-400">👤 (Belum diisi / Butuh 1 org)</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-[9.5px] text-slate-500">
                                            Bezetting: <strong class="text-slate-800">{{ $sekretarisSenat->bezetting }}</strong> / Butuh: <strong>1</strong> org
                                        </div>
                                    </div>

                                    {{-- Komisi Akademik & Etik (beberapa org) --}}
                                    <div class="p-2 rounded-lg cursor-pointer hover:scale-[1.01] transition"
                                         style="background-color: #f8fafc !important; border: 1.5px solid #cbd5e1 !important;"
                                         @click.stop="openDetail(
                                             'Komisi Akademik & Etik Senat Fakultas',
                                             'Komisi Penegakan Kode Etik & Standar Akademik Senat (Formasi: {{ $komisiSenat->kebutuhan }} Org)',
                                             {{ $komisiSenat->bezetting }},
                                             {{ $komisiSenat->kebutuhan }},
                                             '{{ $komisiSenat->status }}',
                                             {{ $komisiSenat->pegawaiList }},
                                             'Membahas dan memberikan pertimbangan terkait integritas akademik, kode etik civitas akademika, dan kurikulum fakultas.'
                                         )">
                                        <div class="flex items-center justify-between">
                                            <span class="font-black text-[11px]" style="color: #1e293b !important;">KOMISI AKADEMIK &amp; ETIK</span>
                                            <span class="text-[9.5px] font-bold px-1.5 py-0.5 rounded-full {{ $komisiSenat->selisih >= 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                                {{ $komisiSenat->status }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-[10.5px] font-medium text-slate-700 truncate">
                                            @if($komisiSenat->bezetting > 0)
                                                <span class="text-indigo-900 font-bold">👥 {{ $komisiSenat->pegawaiList->pluck('nama_lengkap')->join(', ') }}</span>
                                            @else
                                                <span class="italic text-slate-400">👥 (Belum ada anggota / Butuh {{ $komisiSenat->kebutuhan }} org)</span>
                                            @endif
                                        </div>
                                        <div class="mt-0.5 text-[9.5px] text-slate-500">
                                            Bezetting: <strong class="text-slate-800">{{ $komisiSenat->bezetting }}</strong> / Butuh: <strong>{{ $komisiSenat->kebutuhan }}</strong> org
                                        </div>
                                    </div>
                                </div>

                                {{-- Footer Total Bezetting Senat --}}
                                <div class="mt-2.5 pt-2 flex items-center justify-between text-[10px] border-t border-slate-200 text-slate-600">
                                    <span>Total: <strong>{{ $senatTotalBezetting }}</strong> / Butuh: <strong>{{ $senatTotalKebutuhan }}</strong></span>
                                    <span class="font-bold px-2 py-0.5 rounded-full {{ $senatTotalBezetting >= $senatTotalKebutuhan ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                        {{ $senatStatus }}
                                    </span>
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
                                @php
                                    $wd1Anjab = $getAnjab('Wakil Dekan I (Bid. Akademik)');
                                    $wd1Pejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Wakil Dekan I%')
                                                                ->orWhere('nama_jabatan', 'like', '%Wakil Dekan Bidang Akademik%')
                                                                ->orWhere('nama_jabatan', 'like', '%Wakil Dekan Akedemik%'))
                                    )->first();
                                    $wd1List = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Wakil Dekan I%')
                                                                ->orWhere('nama_jabatan', 'like', '%Wakil Dekan Bidang Akademik%')
                                                                ->orWhere('nama_jabatan', 'like', '%Wakil Dekan Akedemik%'))
                                    )->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                                @endphp
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG AKADEMIK (WD I)',
                                         'Unsur Pimpinan Bidang Pendidikan, Kurikulum & Penjaminan Mutu (Grade 13)',
                                         {{ $wd1Anjab->bezetting }},
                                         {{ $wd1Anjab->kebutuhan }},
                                         '{{ $wd1Anjab->status }}',
                                         {{ $wd1List }},
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
                                    @if($wd1Pejabat)
                                        <div class="text-[10.5px] text-sky-200 font-bold text-center truncate mt-1" title="{{ $wd1Pejabat->nama_lengkap }}">
                                            👤 {{ $wd1Pejabat->nama_lengkap }}
                                        </div>
                                    @else
                                        <div class="text-[9.5px] text-sky-200/80 italic text-center mt-0.5">
                                            (Menunggu SK Penetapan)
                                        </div>
                                    @endif
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">{{ $wd1Anjab->bezetting }}</strong> / Butuh: <strong style="color: #ffffff !important;">{{ $wd1Anjab->kebutuhan }}</strong></span>
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" style="background-color: {{ $wd1Anjab->selisih < 0 ? '#e11d48' : '#10b981' }} !important; color: #ffffff !important;">
                                            {{ $wd1Anjab->status }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Garis Turun ke Ketua Jurusan & Sek. Jurusan --}}
                                <div class="w-0.5 h-4" style="background-color: #0d9488 !important;"></div>

                                {{-- KOTAK TERPADU: PIMPINAN JURUSAN (KETUA & SEKRETARIS JURUSAN DISATUKAN) --}}
                                @php
                                    $kajurAnjab = $getAnjab('Ketua Jurusan (Kajur)');
                                    $sekjurAnjab = $getAnjab('Sekretaris Jurusan');

                                    $kajurFormasi = $getPejabatFormasi(['Ketua Jurusan', 'Kajur', 'Ketua Jurusan Preklinik', 'Ketua Jurusan Klinik'], ['Ketua Jurusan', 'Kajur'], 1);
                                    $sekjurFormasi = $getPejabatFormasi(['Sekretaris Jurusan', 'Sekjur', 'Sekretaris Jurusan Preklinik', 'Sekretaris Jurusan Klinik'], ['Sekretaris Jurusan', 'Sekjur'], 1);

                                    $kajurPejabat = $kajurFormasi->pejabatUtama;
                                    $kajurBezetting = $kajurFormasi->bezetting;
                                    $kajurList = $kajurFormasi->pegawaiList;

                                    $sekjurPejabat = $sekjurFormasi->pejabatUtama;
                                    $sekjurBezetting = $sekjurFormasi->bezetting;
                                    $sekjurList = $sekjurFormasi->pegawaiList;

                                    // Fallback jika belum di formasi tapi ada di jabatan langsung
                                    if (!$kajurPejabat) {
                                        $fallbackKajur = \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Ketua Jurusan%'))->first();
                                        if ($fallbackKajur) {
                                            $kajurPejabat = ['nama_lengkap' => $fallbackKajur->nama_lengkap];
                                            $kajurBezetting = 1;
                                            $kajurList = collect([$fallbackKajur]);
                                        }
                                    }
                                    if (!$sekjurPejabat) {
                                        $fallbackSekjur = \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Sekretaris Jurusan%'))->first();
                                        if ($fallbackSekjur) {
                                            $sekjurPejabat = ['nama_lengkap' => $fallbackSekjur->nama_lengkap];
                                            $sekjurBezetting = 1;
                                            $sekjurList = collect([$fallbackSekjur]);
                                        }
                                    }
                                @endphp
                                <div class="w-full p-3 rounded-xl shadow-md transition"
                                     style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%) !important; color: #ffffff !important; border: 2px solid #2dd4bf !important; box-shadow: 0 4px 10px rgba(15, 118, 110, 0.3) !important;">
                                    <div class="flex justify-between items-center text-[10px] pb-1.5 border-b border-teal-500/40">
                                        <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 2px 8px; border-radius: 4px;">PIMPINAN JURUSAN</span>
                                        <span style="background-color: #fbbf24 !important; color: #0f766e !important; font-weight: 900; padding: 2px 8px; border-radius: 4px; font-family: monospace;">Grade 11 &amp; 10</span>
                                    </div>

                                    <div class="mt-2.5 space-y-2 text-xs">
                                        {{-- Atas: KETUA JURUSAN --}}
                                        <div class="p-2.5 rounded-lg bg-teal-900/40 border border-teal-400/30 cursor-pointer hover:bg-teal-900/60 transition text-center"
                                             @click="openDetail(
                                                 'KETUA JURUSAN (KAJUR)',
                                                 'Pimpinan Jurusan Keperawatan (Grade 11)',
                                                 {{ $kajurBezetting }},
                                                 1,
                                                 '{{ $kajurBezetting > 0 ? 'Ideal' : 'Kurang 1' }}',
                                                 {{ $kajurList }},
                                                 '{{ addslashes($kajurAnjab->ikhtisar) }}',
                                                 '{{ $kajurAnjab->anjab_url }}',
                                                 '{{ $kajurAnjab->abk_url }}'
                                             )">
                                            <div class="flex justify-between items-center text-[9px] mb-1">
                                                <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 1px 6px; border-radius: 3px;">KAJUR</span>
                                                <span style="background-color: #fbbf24 !important; color: #0f766e !important; font-weight: 900; padding: 1px 6px; border-radius: 3px; font-family: monospace;">Grade 11</span>
                                            </div>
                                            <div class="font-black text-[11.5px] uppercase tracking-wide text-white">
                                                KETUA JURUSAN
                                            </div>
                                            @if($kajurPejabat)
                                                <div class="text-[10.5px] text-amber-300 font-bold truncate mt-1" title="{{ is_array($kajurPejabat) ? $kajurPejabat['nama_lengkap'] : $kajurPejabat->nama_lengkap }}">
                                                    👤 {{ is_array($kajurPejabat) ? $kajurPejabat['nama_lengkap'] : $kajurPejabat->nama_lengkap }}
                                                </div>
                                            @else
                                                <div class="text-[9.5px] text-teal-200/70 italic mt-0.5">
                                                    (Menunggu SK Penetapan)
                                                </div>
                                            @endif
                                            <div class="text-[9.5px] mt-1.5 pt-1.5 border-t border-teal-500/30 text-teal-100 flex justify-between items-center">
                                                <span>Bezetting: <strong>{{ $kajurBezetting }}</strong> / 1</span>
                                                <span class="text-[8.5px] px-2 py-0.5 rounded font-bold {{ $kajurBezetting > 0 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' }}">
                                                    {{ $kajurBezetting > 0 ? 'Terisi' : 'Butuh SK' }}
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Garis Hubung Vertikal Antara Kajur dan Sekjur --}}
                                        <div class="flex justify-center -my-1">
                                            <div class="w-0.5 h-2 bg-teal-400/50"></div>
                                        </div>

                                        {{-- Bawah: SEKRETARIS JURUSAN --}}
                                        <div class="p-2.5 rounded-lg bg-teal-900/40 border border-teal-400/30 cursor-pointer hover:bg-teal-900/60 transition text-center"
                                             @click="openDetail(
                                                 'SEKRETARIS JURUSAN (SEKJUR)',
                                                 'Pimpinan Administrasi Jurusan Keperawatan (Grade 10)',
                                                 {{ $sekjurBezetting }},
                                                 1,
                                                 '{{ $sekjurBezetting > 0 ? 'Ideal' : 'Kurang 1' }}',
                                                 {{ $sekjurList }},
                                                 '{{ addslashes($sekjurAnjab->ikhtisar) }}',
                                                 '{{ $sekjurAnjab->anjab_url }}',
                                                 '{{ $sekjurAnjab->abk_url }}'
                                             )">
                                            <div class="flex justify-between items-center text-[9px] mb-1">
                                                <span style="background-color: rgba(255,255,255,0.2) !important; color: #ffffff !important; font-weight: 800; padding: 1px 6px; border-radius: 3px;">SEKJUR</span>
                                                <span style="background-color: #fbbf24 !important; color: #0d9488 !important; font-weight: 900; padding: 1px 6px; border-radius: 3px; font-family: monospace;">Grade 10</span>
                                            </div>
                                            <div class="font-black text-[11.5px] uppercase tracking-wide text-white">
                                                SEKRETARIS JURUSAN
                                            </div>
                                            @if($sekjurPejabat)
                                                <div class="text-[10.5px] text-amber-300 font-bold truncate mt-1" title="{{ is_array($sekjurPejabat) ? $sekjurPejabat['nama_lengkap'] : $sekjurPejabat->nama_lengkap }}">
                                                    👤 {{ is_array($sekjurPejabat) ? $sekjurPejabat['nama_lengkap'] : $sekjurPejabat->nama_lengkap }}
                                                </div>
                                            @else
                                                <div class="text-[9.5px] text-teal-200/70 italic mt-0.5">
                                                    (Menunggu SK Penetapan)
                                                </div>
                                            @endif
                                            <div class="text-[9.5px] mt-1.5 pt-1.5 border-t border-teal-500/30 text-teal-100 flex justify-between items-center">
                                                <span>Bezetting: <strong>{{ $sekjurBezetting }}</strong> / 1</span>
                                                <span class="text-[8.5px] px-2 py-0.5 rounded font-bold {{ $sekjurBezetting > 0 ? 'bg-emerald-500 text-white' : 'bg-amber-400 text-amber-900' }}">
                                                    {{ $sekjurBezetting > 0 ? 'Terisi' : 'Butuh SK' }}
                                                </span>
                                            </div>
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
                                @php
                                    $wd2Pejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Wakil Dekan%')
                                                                ->orWhere('nama_jabatan', 'like', '%WD%'))
                                          ->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Keuangan%')
                                                                ->orWhere('nama_jabatan', 'like', '%Keu%'))
                                          ->where('nama_jabatan', 'not like', '%Kemahasiswaan%')
                                    )->first();
                                    $wd2List = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Wakil Dekan%')
                                                                ->orWhere('nama_jabatan', 'like', '%WD%'))
                                          ->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Keuangan%')
                                                                ->orWhere('nama_jabatan', 'like', '%Keu%'))
                                          ->where('nama_jabatan', 'not like', '%Kemahasiswaan%')
                                    )->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                                    $wd2Bezetting = $wd2Pejabat ? 1 : ($wd2Anjab->bezetting > 0 ? 1 : 0);
                                    $wd2Status = $wd2Bezetting >= 1 ? 'Ideal' : 'Kurang 1';
                                @endphp
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG KEUANGAN DAN UMUM (WD II)',
                                         'Unsur Pimpinan Bidang Perencanaan, Anggaran & Kepegawaian (Grade 13)',
                                         {{ $wd2Bezetting }},
                                         1,
                                         '{{ $wd2Status }}',
                                         {{ $wd2List }},
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
                                    @if($wd2Pejabat)
                                        <div class="text-[10.5px] text-sky-200 font-bold text-center truncate mt-1" title="{{ $wd2Pejabat->nama_lengkap }}">
                                            👤 {{ $wd2Pejabat->nama_lengkap }}
                                        </div>
                                    @else
                                        <div class="text-[9.5px] text-sky-200/80 italic text-center mt-0.5">
                                            (Menunggu SK Penetapan)
                                        </div>
                                    @endif
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">{{ $wd2Bezetting }}</strong> / Butuh: <strong style="color: #ffffff !important;">1</strong></span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $wd2Bezetting < 1 ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white' }}">
                                            {{ $wd2Status }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Garis Turun ke Kepala Bagian Umum --}}
                                <div class="w-0.5 h-4" style="background-color: #d97706 !important;"></div>

                                {{-- Kotak KEPALA BAGIAN UMUM --}}
                                @php
                                    $kabagPejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where('nama_jabatan', 'like', '%Kepala Bagian Umum%')
                                          ->orWhere('nama_jabatan', 'like', '%Kabag Umum%')
                                    )->first();
                                    $kabagList = \App\Models\Pegawai::whereHas('jabatan', fn($q) => $q->where('nama_jabatan', 'like', '%Bagian Umum%'))
                                        ->orWhereHas('unitKerja', fn($q) => $q->where('nama_unit', 'like', '%Bagian Umum%'))
                                        ->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                                    $kabagBezetting = $kabagPejabat ? 1 : ($kabagAnjab->bezetting > 0 ? 1 : 0);
                                    $kabagStatus = $kabagBezetting >= 1 ? 'Ideal' : 'Kurang 1';
                                @endphp
                                <div class="w-full p-2.5 rounded-lg shadow-sm cursor-pointer hover:opacity-95 transition"
                                     style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important; color: #ffffff !important; border: 1.5px solid #fde68a !important;"
                                     @click="openDetail(
                                         'KEPALA BAGIAN UMUM',
                                         'Kepala Bagian Tata Usaha Fakultas Keperawatan (Grade 11)',
                                         {{ $kabagBezetting }},
                                         1,
                                         '{{ $kabagStatus }}',
                                         {{ $kabagList }},
                                         '{{ addslashes($kabagAnjab->ikhtisar) }}',
                                         '{{ $kabagAnjab->anjab_url }}',
                                         '{{ $kabagAnjab->abk_url }}'
                                     )">
                                    <div class="text-center font-black text-xs uppercase tracking-wide" style="color: #ffffff !important;">
                                        KEPALA BAGIAN UMUM
                                    </div>
                                    @if($kabagPejabat)
                                        <div class="text-[10px] text-amber-100 font-bold text-center truncate mt-0.5" title="{{ $kabagPejabat->nama_lengkap }}">
                                            👤 {{ $kabagPejabat->nama_lengkap }}
                                        </div>
                                    @else
                                        <div class="text-[9.5px] text-amber-200/80 italic text-center mt-0.5">
                                            (Menunggu SK Penetapan)
                                        </div>
                                    @endif
                                    <div class="text-[9.5px] mt-1.5 pt-1 border-t border-white/20 text-amber-100 flex justify-between items-center px-1">
                                        <span>Bezetting: <strong>{{ $kabagBezetting }}</strong>/1</span>
                                        <span class="text-[8.5px] px-1.5 py-0.2 rounded font-bold {{ $kabagBezetting < 1 ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white' }}">
                                            {{ $kabagStatus }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Garis Turun ke 3 Pokja --}}
                                <div class="w-0.5 h-3" style="background-color: #d97706 !important;"></div>

                                {{-- 3 Kotak Ka. POKJA --}}
                                @php
                                    // 1. Pokja Akademik
                                    $pokjaAkadPejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Pokja%')
                                                                ->orWhere('nama_jabatan', 'like', '%Kelompok Kerja%'))
                                          ->where('nama_jabatan', 'like', '%Akademik%')
                                          ->where('nama_jabatan', 'not like', '%Sarana%')
                                          ->where('nama_jabatan', 'not like', '%Umum%')
                                          ->where('nama_jabatan', 'not like', '%Staff%')
                                          ->where('nama_jabatan', 'not like', '%Staf%')
                                    )->first();
                                    $pokjaAkadList = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where('nama_jabatan', 'like', '%Pokja%')
                                          ->where('nama_jabatan', 'like', '%Akademik%')
                                          ->where('nama_jabatan', 'not like', '%Sarana%')
                                          ->where('nama_jabatan', 'not like', '%Umum%')
                                    )->orWhereHas('unitKerja', fn($q) => 
                                        $q->where('nama_unit', 'like', '%Pokja%')
                                          ->where('nama_unit', 'like', '%Akademik%')
                                          ->where('nama_unit', 'not like', '%Sarana%')
                                    )->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                                    $pokjaAkadBezetting = $pokjaAkadPejabat ? 1 : 0;
                                    $pokjaAkadStatus = $pokjaAkadBezetting >= 1 ? 'Ideal' : 'Kurang 1';

                                    // 2. Pokja Keuangan & Kepegawaian
                                    $pokjaKeuPejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Pokja%')
                                                                ->orWhere('nama_jabatan', 'like', '%Kelompok Kerja%'))
                                          ->where(fn($sq2) => $sq2->where('nama_jabatan', 'like', '%Keuangan%')->orWhere('nama_jabatan', 'like', '%Keu%'))
                                          ->where('nama_jabatan', 'not like', '%Staff%')
                                          ->where('nama_jabatan', 'not like', '%Staf%')
                                    )->first();
                                    $pokjaKeuList = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where('nama_jabatan', 'like', '%Pokja%')
                                          ->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Keuangan%')->orWhere('nama_jabatan', 'like', '%Keu%'))
                                    )->orWhereHas('unitKerja', fn($q) => 
                                        $q->where('nama_unit', 'like', '%Pokja%')
                                          ->where(fn($sq) => $sq->where('nama_unit', 'like', '%Keuangan%')->orWhere('nama_unit', 'like', '%Keu%'))
                                    )->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                                    $pokjaKeuBezetting = $pokjaKeuPejabat ? 1 : 0;
                                    $pokjaKeuStatus = $pokjaKeuBezetting >= 1 ? 'Ideal' : 'Kurang 1';

                                    // 3. Pokja Umum & Sarana Akademik
                                    $pokjaUmumPejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Pokja%')
                                                                ->orWhere('nama_jabatan', 'like', '%Kelompok Kerja%'))
                                          ->where(fn($sq2) => $sq2->where('nama_jabatan', 'like', '%Sarana%')->orWhere('nama_jabatan', 'like', '%Umum Sarana%'))
                                          ->where('nama_jabatan', 'not like', '%Staff%')
                                          ->where('nama_jabatan', 'not like', '%Staf%')
                                    )->first();
                                    $pokjaUmumList = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                        $q->where('nama_jabatan', 'like', '%Pokja%')
                                          ->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Sarana%')->orWhere('nama_jabatan', 'like', '%Umum Sarana%'))
                                    )->orWhereHas('unitKerja', fn($q) => 
                                        $q->where('nama_unit', 'like', '%Pokja%')
                                          ->where(fn($sq) => $sq->where('nama_unit', 'like', '%Sarana%')->orWhere('nama_unit', 'like', '%Umum Sarana%'))
                                    )->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                                    $pokjaUmumBezetting = $pokjaUmumPejabat ? 1 : 0;
                                    $pokjaUmumStatus = $pokjaUmumBezetting >= 1 ? 'Ideal' : 'Kurang 1';
                                @endphp
                                <div class="w-full space-y-2 mt-1">
                                    {{-- Pokja 1: Akademik & Kemahasiswaan --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ketua Pokja Akademik dan Kemahasiswaan',
                                             'Kelompok Kerja Layanan Registrasi, Perkuliahan & Kemahasiswaan (Grade 9 & 6)',
                                             {{ $pokjaAkadBezetting }},
                                             1,
                                             '{{ $pokjaAkadStatus }}',
                                             {{ $pokjaAkadList }},
                                             '{{ addslashes($pokjaAkadAnjab->ikhtisar) }}',
                                             '{{ $pokjaAkadAnjab->anjab_url }}',
                                             '{{ $pokjaAkadAnjab->abk_url }}'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA AKADEMIK DAN KEMAHASISWAAN</div>
                                        @if($pokjaAkadPejabat)
                                            <div class="text-[10px] text-amber-900 font-bold truncate mt-0.5" title="{{ $pokjaAkadPejabat->nama_lengkap }}">
                                                👤 {{ $pokjaAkadPejabat->nama_lengkap }}
                                            </div>
                                        @else
                                            <div class="text-[9px] text-amber-700/80 italic mt-0.5">
                                                (Belum ada pejabat definitif - Butuh SK)
                                            </div>
                                        @endif
                                        <div class="text-[9.5px] text-gray-600 mt-0.5 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $pokjaAkadBezetting }}</strong>/1 ({{ $pokjaAkadList->count() }} staf)</span>
                                            <span class="text-[8.5px] font-bold px-1.5 rounded {{ $pokjaAkadBezetting < 1 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $pokjaAkadStatus }}</span>
                                        </div>
                                    </div>

                                    {{-- Pokja 2: Keuangan & Kepegawaian --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ketua Pokja Keuangan dan Kepegawaian',
                                             'Kelompok Kerja Pengelolaan Anggaran, Presensi & Karir ASN (Grade 9 & 6)',
                                             {{ $pokjaKeuBezetting }},
                                             1,
                                             '{{ $pokjaKeuStatus }}',
                                             {{ $pokjaKeuList }},
                                             '{{ addslashes($pokjaKeuAnjab->ikhtisar) }}',
                                             '{{ $pokjaKeuAnjab->anjab_url }}',
                                             '{{ $pokjaKeuAnjab->abk_url }}'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA KEUANGAN DAN KEPEGAWAIAN</div>
                                        @if($pokjaKeuPejabat)
                                            <div class="text-[10px] text-amber-900 font-bold truncate mt-0.5" title="{{ $pokjaKeuPejabat->nama_lengkap }}">
                                                👤 {{ $pokjaKeuPejabat->nama_lengkap }}
                                            </div>
                                        @else
                                            <div class="text-[9px] text-amber-700/80 italic mt-0.5">
                                                (Belum ada pejabat definitif - Butuh SK)
                                            </div>
                                        @endif
                                        <div class="text-[9.5px] text-gray-600 mt-0.5 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $pokjaKeuBezetting }}</strong>/1 ({{ $pokjaKeuList->count() }} staf)</span>
                                            <span class="text-[8.5px] font-bold px-1.5 rounded {{ $pokjaKeuBezetting < 1 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $pokjaKeuStatus }}</span>
                                        </div>
                                    </div>

                                    {{-- Pokja 3: Umum dan Sarana Akademik --}}
                                    <div class="p-2.5 rounded-lg cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #fffbeb !important; border: 1.5px solid #fde68a !important;"
                                         @click="openDetail(
                                             'Ketua Pokja Umum dan Sarana Akademik',
                                             'Kelompok Kerja Pengelolaan BMN, Perlengkapan & Sarana (Grade 9 & 6)',
                                             {{ $pokjaUmumBezetting }},
                                             1,
                                             '{{ $pokjaUmumStatus }}',
                                             {{ $pokjaUmumList }},
                                             '{{ addslashes($pokjaUmumAnjab->ikhtisar) }}',
                                             '{{ $pokjaUmumAnjab->anjab_url }}',
                                             '{{ $pokjaUmumAnjab->abk_url }}'
                                         )">
                                        <div class="font-black text-[11px]" style="color: #78350f !important;">Ka. POKJA UMUM DAN SARANA AKADEMIK</div>
                                        @if($pokjaUmumPejabat)
                                            <div class="text-[10px] text-amber-900 font-bold truncate mt-0.5" title="{{ $pokjaUmumPejabat->nama_lengkap }}">
                                                👤 {{ $pokjaUmumPejabat->nama_lengkap }}
                                            </div>
                                        @else
                                            <div class="text-[9px] text-amber-700/80 italic mt-0.5">
                                                (Belum ada pejabat definitif - Butuh SK)
                                            </div>
                                        @endif
                                        <div class="text-[9.5px] text-gray-600 mt-0.5 flex justify-between items-center">
                                            <span>Bezetting: <strong>{{ $pokjaUmumBezetting }}</strong>/1 ({{ $pokjaUmumList->count() }} staf)</span>
                                            <span class="text-[8.5px] font-bold px-1.5 rounded {{ $pokjaUmumBezetting < 1 ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }}">{{ $pokjaUmumStatus }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ================================================================= --}}
                            {{-- SAYAP 3 (KANAN): WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI & KERJASAMA--}}
                            {{-- ================================================================= --}}
                            @php
                                $wd3Anjab = $getAnjab('Wakil Dekan Bidang Kemahasiswaan');
                                $wd3Pejabat = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                    $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Wakil Dekan%')
                                                            ->orWhere('nama_jabatan', 'like', '%WD%'))
                                      ->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Kemahasiswaan%')
                                                            ->orWhere('nama_jabatan', 'like', '%Alumni%')
                                                            ->orWhere('nama_jabatan', 'like', '%Kerjasama%'))
                                )->first();
                                $wd3List = \App\Models\Pegawai::whereHas('jabatan', fn($q) => 
                                    $q->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Wakil Dekan%')
                                                            ->orWhere('nama_jabatan', 'like', '%WD%'))
                                      ->where(fn($sq) => $sq->where('nama_jabatan', 'like', '%Kemahasiswaan%')
                                                            ->orWhere('nama_jabatan', 'like', '%Alumni%')
                                                            ->orWhere('nama_jabatan', 'like', '%Kerjasama%'))
                                )->get(['id', 'nama', 'gelar_depan', 'gelar_belakang', 'nip', 'foto']);
                                $wd3Bezetting = $wd3Pejabat ? 1 : ($wd3Anjab->bezetting > 0 ? 1 : 0);
                                $wd3Status = $wd3Bezetting >= 1 ? 'Ideal' : 'Kurang 1';
                            @endphp
                            <div class="flex flex-col items-center">
                                <div class="w-0.5 h-4" style="background-color: #1e3a8a !important;"></div>

                                {{-- Kartu WD III --}}
                                <div class="w-full p-4 rounded-xl shadow-lg hover:scale-[1.02] transition cursor-pointer"
                                     style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important; color: #ffffff !important; border: 2px solid #60a5fa !important; box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3) !important;"
                                     @click="openDetail(
                                         'WAKIL DEKAN BIDANG KEMAHASISWAAN, ALUMNI & KERJASAMA (WD III)',
                                         'Unsur Pimpinan Bidang Penalaran, Minat Bakat, Tracer Study & Kemitraan (Grade 13)',
                                         {{ $wd3Bezetting }},
                                         1,
                                         '{{ $wd3Status }}',
                                         {{ $wd3List }},
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
                                    @if($wd3Pejabat)
                                        <div class="text-[10.5px] text-sky-200 font-bold text-center truncate mt-1" title="{{ $wd3Pejabat->nama_lengkap }}">
                                            👤 {{ $wd3Pejabat->nama_lengkap }}
                                        </div>
                                    @else
                                        <div class="text-[9.5px] text-sky-200/80 italic text-center mt-0.5">
                                            (Menunggu SK Penetapan)
                                        </div>
                                    @endif
                                    <div class="mt-3 pt-2 flex justify-between items-center text-[11px]" style="border-top: 1px solid rgba(255,255,255,0.3) !important; color: #e0e7ff !important;">
                                        <span>Bezetting: <strong style="color: #ffffff !important;">{{ $wd3Bezetting }}</strong> / Butuh: <strong style="color: #ffffff !important;">1</strong></span>
                                        <span class="text-[10px] px-2 py-0.5 rounded-full font-bold {{ $wd3Bezetting < 1 ? 'bg-rose-500 text-white' : 'bg-emerald-500 text-white' }}">
                                            {{ $wd3Status }}
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
                                    @php
                                        $kInfo = $getKelompok($k);
                                    @endphp
                                    <div class="p-2 rounded flex items-center justify-between cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #f0fdf4 !important; border: 1px solid #86efac !important; font-weight: 800;"
                                         @click="openDetail(
                                             'KJFD Keperawatan: {{ $k }}',
                                             'Kelompok Jabatan Fungsional Dosen (Grade 9-14)',
                                             {{ $kInfo->bezetting }},
                                             {{ $kInfo->kebutuhan }},
                                             '{{ $kInfo->status }}',
                                             {{ $kInfo->pegawaiList }},
                                             'Melaksanakan tridharma perguruan tinggi pada rumpun keahlian {{ $k }}, pembimbingan tugas akhir, praktikum klinik dan riset keperawatan. {{ addslashes($dosenAnjab->ikhtisar) }}',
                                             '{{ $dosenAnjab->anjab_url }}',
                                             '{{ $dosenAnjab->abk_url }}'
                                         )">
                                        <div class="flex flex-col text-left">
                                            <span style="color: #064e3b !important; font-size: 11px;">{{ $k }}</span>
                                            <span class="text-[9.5px] font-semibold text-emerald-700">
                                                Terisi: <strong>{{ $kInfo->bezetting }}</strong> dosen
                                            </span>
                                        </div>
                                        <span class="text-[9.5px] px-1.5 py-0.5 rounded font-bold {{ $kInfo->bezetting > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $kInfo->bezetting > 0 ? $kInfo->bezetting . ' Dosen ➔' : 'Detail ➔' }}
                                        </span>
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
                                    @php
                                        $ufInfo = $getKelompok($uf);
                                    @endphp
                                    <div class="p-2 rounded flex items-center justify-between cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #f0f9ff !important; border: 1px solid #bae6fd !important; font-weight: 800;"
                                         @click="openDetail(
                                             '{{ $uf }}',
                                             'Unit Fungsional Khusus Fakultas',
                                             {{ $ufInfo->bezetting }},
                                             {{ $ufInfo->kebutuhan }},
                                             '{{ $ufInfo->status }}',
                                             {{ $ufInfo->pegawaiList }},
                                             'Melaksanakan fungsi penunjang akademik, kepatuhan etik, pengujian CBT, kemitraan institusi, bimbingan konseling dan layanan keterbukaan informasi publik. {{ addslashes($unitKhususAnjab->ikhtisar) }}',
                                             '{{ $unitKhususAnjab->anjab_url }}',
                                             '{{ $unitKhususAnjab->abk_url }}'
                                         )">
                                        <div class="flex flex-col text-left">
                                            <span style="color: #0c4a6e !important; font-size: 11px;">{{ $uf }}</span>
                                            <span class="text-[9.5px] font-semibold text-sky-700">
                                                Terisi: <strong>{{ $ufInfo->bezetting }}</strong> personil
                                            </span>
                                        </div>
                                        <span class="text-[9.5px] px-1.5 py-0.5 rounded font-bold {{ $ufInfo->bezetting > 0 ? 'bg-sky-100 text-sky-800' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $ufInfo->bezetting > 0 ? $ufInfo->bezetting . ' Orang ➔' : 'Detail ➔' }}
                                        </span>
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
                                    @php
                                        $lbInfo = $getKelompok($lb);
                                    @endphp
                                    <div class="p-2 rounded flex items-center justify-between cursor-pointer hover:opacity-90 transition"
                                         style="background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important; font-weight: 800;"
                                         @click="openDetail(
                                             '{{ $lb }}',
                                             'Ruang Praktikum Laboratorium Keperawatan',
                                             {{ $lbInfo->bezetting }},
                                             {{ $lbInfo->kebutuhan }},
                                             '{{ $lbInfo->status }}',
                                             {{ $lbInfo->pegawaiList }},
                                             'Fasilitas praktikum simulasi medis, manikin keperawatan, dan ujian Objective Structured Clinical Examination (OSCE). {{ addslashes($kepalaLabAnjab->ikhtisar) }}',
                                             '{{ $kepalaLabAnjab->anjab_url }}',
                                             '{{ $kepalaLabAnjab->abk_url }}'
                                         )">
                                        <div class="flex flex-col text-left">
                                            <span style="color: #1e1b4b !important; font-size: 11px;">{{ $lb }}</span>
                                            <span class="text-[9.5px] font-semibold text-indigo-700">
                                                Terisi: <strong>{{ $lbInfo->bezetting }}</strong> personil
                                            </span>
                                        </div>
                                        <span class="text-[9.5px] px-1.5 py-0.5 rounded font-bold {{ $lbInfo->bezetting > 0 ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-500' }}">
                                            {{ $lbInfo->bezetting > 0 ? $lbInfo->bezetting . ' Orang ➔' : 'Detail ➔' }}
                                        </span>
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
                        <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                            <template x-for="p in activeNode.pegawaiList" :key="p.nip">
                                <div class="flex items-center gap-3 p-2 rounded-lg bg-gray-50 border border-gray-200 text-xs">
                                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold flex items-center justify-center text-xs shrink-0 overflow-hidden">
                                        <template x-if="p.foto">
                                            <img :src="'/storage/' + p.foto" class="w-full h-full object-cover">
                                        </template>
                                        <template x-if="!p.foto">
                                            <span x-text="(p.nama || p.nama_lengkap || '').substring(0, 1)"></span>
                                        </template>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-1">
                                            <span class="font-bold text-gray-900 truncate" x-text="(p.gelar_depan ? p.gelar_depan + ' ' : '') + (p.nama || p.nama_lengkap || '') + (p.gelar_belakang ? ', ' + p.gelar_belakang : '')"></span>
                                            <template x-if="p.peran">
                                                <span class="px-2 py-0.5 text-[9.5px] font-bold rounded shrink-0"
                                                      :class="p.peran === 'Koordinator' || p.peran === 'Ketua' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-200'"
                                                      x-text="p.peran"></span>
                                            </template>
                                        </div>
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
                    @if(Auth::user()->canAccessExecutiveKepegawaianMenus())
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
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
