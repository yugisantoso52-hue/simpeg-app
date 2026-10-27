<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permintaan dan Pemberian Cuti - {{ $cuti->pegawai->nama ?? 'Pegawai' }}</title>
    <style>
        @page {
            margin: 8mm 12mm 6mm 12mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.25;
        }
        /* STYLING KOP SURAT RESMI SESUAI PERMENDIKTI SAINTEK NO. 42 TAHUN 2025 */
        .kop-surat-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000000;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }
        .kop-surat-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }
        .kop-logo-cell {
            width: 28mm;
            text-align: left;
            vertical-align: middle;
        }
        .kop-logo-cell img {
            width: 25mm;
            height: 25mm;
            display: block;
        }
        .kop-text-cell {
            text-align: center;
            vertical-align: middle;
            padding-left: 5px;
            padding-right: 28mm;
        }
        .kop-text-cell .kop-kemdikti {
            margin: 0;
            font-size: 14pt;
            font-weight: normal;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.2px;
        }
        .kop-text-cell .kop-ptn {
            margin: 1.5px 0 0 0;
            font-size: 12.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.3px;
        }
        .kop-text-cell .kop-fakultas {
            margin: 1.5px 0 0 0;
            font-size: 12.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.3px;
        }
        .kop-text-cell .kop-alamat {
            margin: 3.5px 0 0 0;
            font-size: 9pt;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.25;
        }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 6px 0 10px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        table.bkn-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.bkn-table, table.bkn-table th, table.bkn-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }
        .section-title {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .checkbox-mark { font-family: DejaVu Sans, sans-serif; font-size: 10pt; font-weight: bold; }
        
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 4px;
        }
        .sign-table td {
            border: none;
            padding: 2px 4px;
            vertical-align: top;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT BERLOGO HITAM PUTIH --}}
    @php
        $candidatePaths = [
            public_path('images/logo-unri-bw.png'),
            public_path('images/logo-unri-bw.jpg'),
            public_path('logo-unri-bw.png'),
            public_path('images/logo-unri.png'),
            public_path('logo-unri.png'),
        ];

        $foundLogo = null;
        foreach ($candidatePaths as $path) {
            if (file_exists($path)) {
                $foundLogo = $path;
                break;
            }
        }
    @endphp

    <table class="kop-surat-table">
        <tr>
            <td class="kop-logo-cell">
                @if($foundLogo)
                    @php
                        $mimeType = str_ends_with(strtolower($foundLogo), '.jpg') || str_ends_with(strtolower($foundLogo), '.jpeg') ? 'image/jpeg' : 'image/png';
                    @endphp
                    <img src="data:{{ $mimeType }};base64,{{ base64_encode(file_get_contents($foundLogo)) }}" alt="Logo UNRI">
                @else
                    <div style="font-size: 8pt; color: #666; text-align: center;">[LOGO UNRI]</div>
                @endif
            </td>
            <td class="kop-text-cell">
                <div class="kop-kemdikti">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div class="kop-ptn">UNIVERSITAS RIAU</div>
                <div class="kop-fakultas">FAKULTAS KEPERAWATAN</div>
                <div class="kop-alamat">
                    Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru 28293<br>
                    Laman : www.keperawatan.unri.ac.id &nbsp;&nbsp;|&nbsp;&nbsp; Email : keperawatan@unri.co.id
                </div>
            </td>
        </tr>
    </table>

    <div class="title">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>
    <div style="text-align: center; font-size: 9pt; font-family: 'Times New Roman', Times, serif; margin-top: -6px; margin-bottom: 6px;">
        Nomor : {{ $nomorSuratFormatted ?? ($cuti->nomor_surat ?? '............/UN19.5.1.1.10/KP/' . (date('Y'))) }}
    </div>
    <div style="text-align: right; font-size: 8.5pt; margin-bottom: 6px;">
        Pekanbaru, {{ $cuti->created_at->translatedFormat('d F Y') }}<br>
        Kepada Yth. Dekan Fakultas Keperawatan UNRI
    </div>

    {{-- I. DATA PEGAWAI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="4">I. DATA PEGAWAI</td>
        </tr>
        <tr>
            <td style="width: 18%;">Nama</td>
            <td style="width: 32%;" class="text-bold">{{ $cuti->pegawai?->nama_lengkap ?? $cuti->pegawai?->nama ?? '-' }}</td>
            <td style="width: 18%;">NIP</td>
            <td style="width: 32%;">{{ $cuti->pegawai?->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $cuti->pegawai?->jabatan?->nama_jabatan ?? '-' }}</td>
            <td>Masa Kerja Golongan</td>
            <td>{{ $cuti->pegawai?->mkg_tahun ?? 0 }} Thn {{ $cuti->pegawai?->mkg_bulan ?? 0 }} Bln</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>{{ $cuti->pegawai?->unitKerja?->nama_unit ?? 'Fakultas Keperawatan' }}</td>
            <td>Golongan / Pangkat</td>
            <td>{{ $cuti->pegawai?->golongan?->nama_golongan ?? '-' }} ({{ $cuti->pegawai?->golongan?->nama_pangkat ?? '-' }})</td>
        </tr>
    </table>

    {{-- II. JENIS CUTI YANG DIAMBIL --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="4">II. JENIS CUTI YANG DIAMBIL</td>
        </tr>
        <tr>
            <td style="width: 40%;">1. Cuti Tahunan</td>
            <td style="width: 10%;" class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Tahunan' ? '✓' : '' }}</td>
            <td style="width: 40%;">2. Cuti Besar</td>
            <td style="width: 10%;" class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Besar' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Sakit' ? '✓' : '' }}</td>
            <td>4. Cuti Melahirkan</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Melahirkan' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Alasan Penting' ? '✓' : '' }}</td>
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti di Luar Tanggungan Negara' ? '✓' : '' }}</td>
        </tr>
    </table>

    {{-- III. ALASAN CUTI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td>III. ALASAN CUTI</td>
        </tr>
        <tr>
            <td>{{ $cuti->alasan ?: '-' }}</td>
        </tr>
    </table>

    {{-- IV. LAMANYA CUTI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="6">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td style="width: 15%;">Selama</td>
            <td style="width: 25%;" class="text-bold">{{ $cuti->jumlah_hari }} Hari Kerja</td>
            <td style="width: 18%;">Mulai Tanggal</td>
            <td style="width: 18%;">{{ $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('d/m/Y') : '-' }}</td>
            <td style="width: 6%;" class="text-center">s.d.</td>
            <td style="width: 18%;">{{ $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('d/m/Y') : '-' }}</td>
        </tr>
    </table>

    {{-- V. CATATAN CUTI TAHUNAN & ALAMAT --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="5">V. CATATAN CUTI TAHUNAN</td>
        </tr>
        <tr class="section-title" style="font-size: 8pt;">
            <td style="width: 12%; text-align: center;">Tahun</td>
            <td style="width: 15%; text-align: center;">Sisa Kuota</td>
            <td style="width: 28%;">Keterangan</td>
            <td colspan="2" style="width: 45%;">Alamat Selama Menjalankan Cuti</td>
        </tr>
        <tr>
            <td class="text-center">{{ now()->year }}</td>
            <td class="text-center text-bold">{{ $cuti->pegawai->sisa_cuti_tahunan ?? 12 }} Hari</td>
            <td>Hak Cuti Tahun Berjalan</td>
            <td colspan="2" rowspan="2">
                {{ $cuti->alamat_selama_cuti ?: '-' }}<br>
                <small>No. Telp/WA: {{ $cuti->nomor_telepon ?: '-' }}</small>
            </td>
        </tr>
        <tr>
            <td class="text-center">{{ now()->year - 1 }}</td>
            <td class="text-center">-</td>
            <td>Tahun N-1</td>
        </tr>
    </table>

    {{-- VI. PERTIMBANGAN ATASAN LANGSUNG & PEJABAT YANG BERWENANG --}}
    @php
        $hierarchyService = app(\App\Services\ApprovalHierarchyService::class);
        $pejabatInfo = $hierarchyService->getPejabatCutiInfo($cuti->pegawai);
        
        $atasanNama    = $pejabatInfo['atasan_nama'];
        $atasanJabatan = $pejabatInfo['atasan_jabatan'];
        $atasanNip     = $pejabatInfo['atasan_nip'];

        // Hanya gunakan relasi atasanLangsung->pegawai jika pejabat tersebut benar-benar merupakan Atasan Langsung Cuti yang sah
        // dan BUKAN Ka Pokja Keu-Kepeg (karena Ka Pokja Keu-Kepeg hanya memberikan Paraf Koordinasi Tata Naskah, bukan TTE Pertimbangan Atasan)
        if (!empty($cuti->pertimbangan_atasan) && $cuti->atasanLangsung && $cuti->atasanLangsung->pegawai) {
            $actualAtasanPegawai = $cuti->atasanLangsung->pegawai;
            $actualAtasanJabatan = strtoupper(trim((string)($actualAtasanPegawai->jabatan->nama_jabatan ?? '')));
            $isActualAtasanValid = !str_contains($actualAtasanJabatan, 'POKJA')
                && !$cuti->atasanLangsung->isKaPokjaKepeg()
                && ($cuti->pegawai ? $hierarchyService->isAtasanLangsungCuti($actualAtasanPegawai, $cuti->pegawai) : true);

            if ($isActualAtasanValid) {
                $atasanNama = $actualAtasanPegawai->nama_lengkap ?? $actualAtasanPegawai->nama;
                $atasanNip  = $actualAtasanPegawai->nip;
            }
        }

        $pybmcNama    = $pejabatInfo['pybmc_nama'];
        $pybmcJabatan = $pejabatInfo['pybmc_jabatan'];
        $pybmcNip     = $pejabatInfo['pybmc_nip'];

        if (in_array($cuti->status, ['Disetujui', 'Ditolak']) && $cuti->approvedBy && $cuti->approvedBy->pegawai) {
            $actualPybmcPegawai = $cuti->approvedBy->pegawai;
            $actualPybmcJabatan = strtoupper(trim((string)($actualPybmcPegawai->jabatan->nama_jabatan ?? '')));
            if (!str_contains($actualPybmcJabatan, 'POKJA') && !$cuti->approvedBy->isKaPokjaKepeg()) {
                $pybmcNama = $actualPybmcPegawai->nama_lengkap ?? $actualPybmcPegawai->nama;
                $pybmcNip  = $actualPybmcPegawai->nip;
            }
        }

        // TTE Atasan Langsung
        $isAtasanSigned = (!empty($cuti->pertimbangan_atasan) && $cuti->pertimbangan_atasan !== 'Menunggu Persetujuan')
            || (!empty($cuti->paraf_kabag_at) && ($pejabatInfo['kategori'] ?? '') === 'tendik');
        $qrAtasanDataUri = null;
        $atasanSignedAt = '';
        if ($isAtasanSigned && isset($verifyUrl)) {
            $qrAtasanDataUri = \App\Services\QrCodeService::generateDataUri($verifyUrl . '?tte=atasan&nip=' . ($atasanNip ?: ''));
            $atasanTimestamp = (($pejabatInfo['kategori'] ?? '') === 'tendik' && !empty($cuti->paraf_kabag_at))
                ? $cuti->paraf_kabag_at
                : ($cuti->pertimbangan_atasan_at ?: $cuti->updated_at);
            $atasanSignedAt = $atasanTimestamp ? $atasanTimestamp->translatedFormat('d/m/Y H:i') . ' WIB' : '';
        }

        // TTE Pejabat Berwenang
        $isPejabatSigned = in_array($cuti->status, ['Disetujui', 'Ditolak']);
        $qrPejabatDataUri = null;
        $pejabatSignedAt = '';
        if ($isPejabatSigned && isset($verifyUrl)) {
            $qrPejabatDataUri = \App\Services\QrCodeService::generateDataUri($verifyUrl . '?tte=pybmc&nip=' . ($pybmcNip ?: ''));
            $pejabatSignedAt = $cuti->approved_at ? $cuti->approved_at->translatedFormat('d/m/Y H:i') . ' WIB' : ($cuti->updated_at ? $cuti->updated_at->translatedFormat('d/m/Y H:i') . ' WIB' : '');
        }

        // Paraf Elektronik Ka Pokja Keu-Kepeg (Paraf Koordinasi Tata Naskah & Verifikasi Kepegawaian)
        $sudahParafPokja = !empty($sudahParafPokja)
            || !empty($cuti->paraf_kapokja_at)
            || !empty($cuti->paraf_kabag_at)
            || in_array($cuti->status, ['Disetujui', 'Disetujui Atasan (Menunggu PYBMC)', 'Disetujui Atasan (Menunggu Keputusan Akhir)']);
        $parafPokjaTimestamp = $cuti->paraf_kapokja_at ?: ($cuti->verifikasi_admin_at ?: ($cuti->paraf_kabag_at ?: $cuti->updated_at));
        $parafPokjaAt = !empty($parafPokjaAt)
            ? $parafPokjaAt
            : ($parafPokjaTimestamp ? $parafPokjaTimestamp->format('d/m/Y H:i') . ' WIB' : '');
        $kaPokjaNama = $pejabatKaPokja->nama_lengkap ?? ($pejabatKaPokja->nama ?? 'Dolli Vita Zenitha Harning Arivina, SE');
        $kaPokjaNip  = $pejabatKaPokja->nip ?? '197605212014092001';
        if ($sudahParafPokja && empty($qrPokjaUri) && isset($verifyUrl)) {
            $qrPokjaUri = \App\Services\QrCodeService::generateDataUri($verifyUrl . '?paraf=kapokja&nip=' . $kaPokjaNip);
        }
    @endphp
    <table class="bkn-table">
        <tr class="section-title">
            <td style="width: 50%;">VI. PERTIMBANGAN ATASAN LANGSUNG</td>
            <td style="width: 50%;">VII. KEPUTUSAN PEJABAT YANG BERWENANG</td>
        </tr>
        <tr>
            {{-- VI: Jabatan atas → TTE / spasi ttd → nama → NIP → Status+Catatan pojok bawah kiri --}}
            <td style="vertical-align: top; padding: 5px 6px;">
                {{-- Komponen Terisolasi TTE Atasan Langsung --}}
                <x-pdf.tte-signature
                    :jabatan="$atasanJabatan"
                    :mandatPrefix="''"
                    :pejabatNama="$atasanNama"
                    :pejabatNip="$atasanNip"
                    :isSigned="$isAtasanSigned"
                    :signedAt="$atasanSignedAt"
                    :qrDataUri="$qrAtasanDataUri"
                    align="center"
                    :showDraftBadge="false"
                    manualSpaceHeight="58px"
                />
                {{-- Status dan Catatan di pojok bawah kiri --}}
                <div style="margin-top: 6px; font-size: 8.5pt; text-align: left;">
                    <div>Status: <strong>{{ $cuti->pertimbangan_atasan ?: ($cuti->status === 'Menunggu Persetujuan' ? 'Menunggu Pertimbangan' : $cuti->status) }}</strong></div>
                    <div>Catatan: {{ $cuti->catatan_atasan_langsung ?: ($cuti->catatan_pimpinan ?: '-') }}</div>
                </div>
            </td>
            {{-- VII: Jabatan atas → TTE / spasi ttd → nama → NIP → Status+No.SK pojok bawah kiri --}}
            <td style="vertical-align: top; padding: 5px 6px;">
                {{-- Komponen Terisolasi TTE Pejabat Yang Berwenang --}}
                <x-pdf.tte-signature
                    :jabatan="$pybmcJabatan"
                    :mandatPrefix="str_contains(strtolower($pybmcJabatan ?? ''), 'wakil dekan') ? 'a.n. Dekan Fakultas Keperawatan' : ''"
                    :pejabatNama="$pybmcNama"
                    :pejabatNip="$pybmcNip"
                    :isSigned="$isPejabatSigned"
                    :signedAt="$pejabatSignedAt"
                    :qrDataUri="$qrPejabatDataUri"
                    align="center"
                    :showDraftBadge="false"
                    manualSpaceHeight="58px"
                />
                {{-- Status dan No. SK di pojok bawah kiri --}}
                <div style="margin-top: 6px; font-size: 8.5pt; text-align: left;">
                    <div>Status: <strong>{{ in_array($cuti->status, ['Disetujui', 'Ditolak']) ? $cuti->status : 'Menunggu Keputusan' }}</strong></div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Tabel Paraf Koordinasi Tata Naskah & Verifikasi Kepegawaian (Komponen Terisolasi Paraf Elektronik) --}}
    @if(!empty($sudahParafPokja) || !empty($cuti->verifikasi_admin_at))
    @php
        $parafCutiItems = [
            app(\App\Services\ElectronicSignatureService::class)->buildParafItemProps(
                roleLabel: 'Ka Pokja Keu-Kepeg',
                pejabatNama: $kaPokjaNama,
                pejabatNip: $kaPokjaNip,
                isSigned: (bool) $sudahParafPokja,
                signedAt: $parafPokjaAt,
                verifyUrl: $verifyUrl ?? null,
                parafCode: 'kapokja',
                qrDataUri: $qrPokjaUri ?? null
            )
        ];
    @endphp
    <table style="width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 4px; font-size: 7.5pt;">
        <tr>
            <td style="width: 54%; vertical-align: top; border: 1px solid #1e293b; background-color: #f8fafc; padding: 4px 6px;">
                <x-pdf.paraf-elektronik
                    title="Paraf Koordinasi Tata Naskah & Verifikasi Kepegawaian:"
                    variant="cuti"
                    :items="$parafCutiItems"
                />
            </td>
            <td style="width: 46%; vertical-align: middle; border: 1px solid #1e293b; background-color: #f8fafc; padding: 4px 6px; font-size: 7pt; color: #334155;">
                <div>Admin Screening: <strong>{{ $cuti->verifikasi_admin_at ? 'Terverifikasi Berkas Sah (' . $cuti->verifikasi_admin_at->format('d/m/Y H:i') . ')' : 'Proses Screening' }}</strong></div>
                @if($cuti->catatan_verifikator)
                    <div style="font-style: italic; color: #334155; font-size: 6.5pt; margin-top: 2px;">Catatan: {{ $cuti->catatan_verifikator }}</div>
                @endif
            </td>
        </tr>
    </table>
    @endif

    @if($isPejabatSigned)
        @include('exports.pdf.partials.otentikasi_dokumen')
    @else
        <div style="border: 1px dashed #f59e0b; background-color: #fffbeb; padding: 4px 8px; margin-top: 6px; font-size: 7pt; color: #b45309; text-align: center; font-style: italic;">
            [ DOKUMEN DRAF FORMULIR CUTI / BELUM DISETUJUI PEJABAT YANG BERWENANG - TTE AKAN TERBIT SETELAH PERMOHONAN CUTI DISETUJUI ]
        </div>
    @endif

</body>
</html>
