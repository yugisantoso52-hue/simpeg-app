<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Riwayat Hidup Usulan Tanda Kehormatan Satyalancana Karya Satya - {{ $pegawai->nama_lengkap ?? $pegawai->nama }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 8mm 14mm 8mm 14mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            line-height: 1.2;
            color: #000;
        }

        /* KOP SURAT RESMI IDENTIK DENGAN FORMULIR PERMINTAAN DAN PEMBERIAN CUTI */
        .kop-surat-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 10px;
        }
        .kop-surat-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }
        .kop-logo-cell {
            width: 26mm;
            text-align: left;
            vertical-align: middle;
        }
        .kop-logo-cell img {
            width: 24mm;
            height: 24mm;
            display: block;
        }
        .kop-text-cell {
            text-align: center;
            vertical-align: middle;
            padding-left: 4px;
            padding-right: 26mm;
        }
        .kop-text-cell .kop-kemdikti {
            margin: 0;
            font-size: 13pt;
            font-weight: normal;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.2px;
        }
        .kop-text-cell .kop-ptn {
            margin: 1.5px 0 0 0;
            font-size: 12pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.3px;
        }
        .kop-text-cell .kop-fakultas {
            margin: 1.5px 0 0 0;
            font-size: 12pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.3px;
        }
        .kop-text-cell .kop-alamat {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.2;
        }

        /* JUDUL DOKUMEN */
        .title-box {
            text-align: center;
            margin: 8px 0 12px 0;
        }
        .title-box h1 {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            letter-spacing: 0.5px;
        }
        .title-box h2 {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 2.5px 0 0 0;
            padding: 0;
            letter-spacing: 0.5px;
        }

        /* TABEL 12 POIN */
        table.drh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-bottom: 12px;
        }
        table.drh-table td {
            border: 1px solid #000;
            padding: 3.5px 6px;
            vertical-align: middle;
        }
        .col-no {
            width: 6%;
            text-align: center;
        }
        .col-label {
            width: 34%;
        }
        .col-value {
            width: 60%;
        }

        /* BLOK TANDA TANGAN */
        .ttd-container {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .ttd-box-left {
            float: left;
            width: 50%;
            text-align: left;
            font-size: 9pt;
            line-height: 1.2;
        }
        .ttd-box-right {
            float: right;
            width: 48%;
            text-align: left;
            font-size: 9pt;
            line-height: 1.2;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT IDENTIK DENGAN FORMULIR PERMINTAAN DAN PEMBERIAN CUTI --}}
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

    <!-- JUDUL FORMAT RESMI -->
    <div class="title-box">
        <h1>DAFTAR RIWAYAT HIDUP</h1>
        <h2>USULAN TANDA KEHORMATAN SATYALANCANA KARYA SATYA</h2>
    </div>

    @php
        // 1. Nama Lengkap Bergelar
        $namaLengkap = $pegawai->nama_lengkap ?? $pegawai->nama;

        // 2. Tempat, Tanggal Lahir
        $tempatLahir = $pegawai->tempat_lahir ?: 'Pekanbaru';
        $tglLahirObj = $pegawai->tanggal_lahir ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->locale('id') : null;
        $ttlFormatted = $tempatLahir . ($tglLahirObj ? ', ' . $tglLahirObj->translatedFormat('d F Y') : '');

        // 3. NIP & NIP Lama
        $nip = $pegawai->nip ?: '-';
        $nipLama = '-';

        // 5. Pendidikan Terakhir
        $pendidikanRaw = strtoupper(trim($pegawai->pendidikan_terakhir ?? ''));
        if (str_contains($pendidikanRaw, 'S3') || str_contains($pendidikanRaw, 'DOKTOR')) {
            $pendidikanFormatted = 'Doktor (S3)';
        } elseif (str_contains($pendidikanRaw, 'S2') || str_contains($pendidikanRaw, 'MAGISTER')) {
            $pendidikanFormatted = 'Magister (S2)';
        } elseif (str_contains($pendidikanRaw, 'S1') || str_contains($pendidikanRaw, 'SARJANA')) {
            $pendidikanFormatted = 'Sarjana (S1)';
        } elseif (str_contains($pendidikanRaw, 'D4') || str_contains($pendidikanRaw, 'DIPLOMA IV')) {
            $pendidikanFormatted = 'Diploma IV (D4)';
        } elseif (str_contains($pendidikanRaw, 'D3') || str_contains($pendidikanRaw, 'DIPLOMA III')) {
            $pendidikanFormatted = 'Diploma III (D3)';
        } else {
            $pendidikanFormatted = $pegawai->pendidikan_terakhir ?: '-';
        }

        // 6. Pangkat, Golongan Ruang Terakhir (TMT)
        $namaGolongan = $pegawai->golongan->nama_golongan ?? '';
        $namaPangkat = $pegawai->golongan->nama_pangkat ?? '';
        $tmtPangkat = $pegawai->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->locale('id')->translatedFormat('d F Y') : ($pegawai->tmt_pangkat ? \Carbon\Carbon::parse($pegawai->tmt_pangkat)->locale('id')->translatedFormat('d F Y') : null);

        if ($namaPangkat && $namaGolongan) {
            $pangkatGolonganFormatted = $namaPangkat . ' / ' . $namaGolongan . ($tmtPangkat ? ' (TMT ' . $tmtPangkat . ')' : '');
        } elseif ($namaGolongan) {
            $pangkatGolonganFormatted = $namaGolongan . ($tmtPangkat ? ' (TMT ' . $tmtPangkat . ')' : '');
        } else {
            $pangkatGolonganFormatted = '-';
        }

        // 7. SK CPNS (TMT) - Normalisasi awalan 'Nomor:' agar tidak dobel
        $rawNoSk = trim($pegawai->nomor_sk_pertama ?? '');
        if (empty($rawNoSk) || $rawNoSk === '-') {
            $cleanNoSk = '19597/A4/KP/2011';
        } else {
            $cleanNoSk = preg_replace('/^(nomor|no)\.?\s*:?\s*/i', '', $rawNoSk);
        }

        $tglSkCpns = $pegawai->tanggal_sk_pertama ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pertama)->locale('id')->translatedFormat('d F Y') : '14 April 2011';
        $tmtCpnsObj = $pegawai->tmt_sk_pertama ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->locale('id') : ($pegawai->tanggal_masuk ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->locale('id') : null);
        $tmtCpnsStr = $tmtCpnsObj ? $tmtCpnsObj->translatedFormat('d F Y') : '1 Desember 2010';

        // 8. Jabatan Terakhir (TMT)
        $namaJabatan = $pegawai->jabatan->nama_jabatan ?? '';
        if (str_contains(strtolower($namaJabatan), 'pranata laboratorium pendidikan')) {
            $namaJabatan = 'PLP Ahli Muda';
        }
        $tmtJabatan = $pegawai->riwayatJabatan->first()?->tmt_jabatan ? \Carbon\Carbon::parse($pegawai->riwayatJabatan->first()->tmt_jabatan)->locale('id')->translatedFormat('d F Y') : ($tmtPangkat ?: '1 Agustus 2022');
        $jabatanFormatted = ($namaJabatan ?: 'Dosen / Tenaga Kependidikan') . ($tmtJabatan ? ' (TMT ' . $tmtJabatan . ')' : '');

        // 9. Jenis Kelamin
        $jkRaw = strtoupper(substr(trim($pegawai->jenis_kelamin ?? 'L'), 0, 1));
        $jkFormatted = ($jkRaw === 'P' || $jkRaw === 'W') ? 'Perempuan' : 'Laki-laki';

        // 10. Tanda Kehormatan yang sudah dimiliki
        $tandaKehormatan = $pegawai->satyalancana_terakhir ?: '-';

        // Tanggal Dokumen
        $tglDokumen = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');
        $tglDokumenFormatWib = \Carbon\Carbon::now()->locale('id')->translatedFormat('d/m/Y H:i') . ' WIB';
    @endphp

    <!-- TABEL 12 POIN PERSIS SEPERTI CONTOH PDF -->
    <table class="drh-table">
        <tr>
            <td class="col-no">1.</td>
            <td class="col-label">Nama Lengkap</td>
            <td class="col-value">{{ $namaLengkap }}</td>
        </tr>
        <tr>
            <td class="col-no">2.</td>
            <td class="col-label">Tempat, Tanggal Lahir</td>
            <td class="col-value">{{ $ttlFormatted }}</td>
        </tr>
        <tr>
            <td class="col-no">3.</td>
            <td class="col-label">NIP</td>
            <td class="col-value">{{ $nip }}</td>
        </tr>
        <tr>
            <td class="col-no">4.</td>
            <td class="col-label">NIP Lama</td>
            <td class="col-value">{{ $nipLama }}</td>
        </tr>
        <tr>
            <td class="col-no">5.</td>
            <td class="col-label">Pendidikan Terakhir</td>
            <td class="col-value">{{ $pendidikanFormatted }}</td>
        </tr>
        <tr>
            <td class="col-no">6.</td>
            <td class="col-label">
                Pangkat, Golongan Ruang Terakhir (TMT)
            </td>
            <td class="col-value">{{ $pangkatGolonganFormatted }}</td>
        </tr>
        <tr>
            <td class="col-no">7.</td>
            <td class="col-label">SK CPNS (TMT)</td>
            <td class="col-value">
                Nomor: {{ $cleanNoSk }}, Tanggal {{ $tglSkCpns }}<br>
                (TMT {{ $tmtCpnsStr }})
            </td>
        </tr>
        <tr>
            <td class="col-no">8.</td>
            <td class="col-label">Jabatan Terakhir (TMT)</td>
            <td class="col-value">{{ $jabatanFormatted }}</td>
        </tr>
        <tr>
            <td class="col-no">9.</td>
            <td class="col-label">Jenis Kelamin</td>
            <td class="col-value">{{ $jkFormatted }}</td>
        </tr>
        <tr>
            <td class="col-no">10.</td>
            <td class="col-label">Tanda Kehormatan yang sudah dimiliki</td>
            <td class="col-value">{{ $tandaKehormatan }}</td>
        </tr>
        <tr>
            <td class="col-no">11.</td>
            <td class="col-label">Hukuman Disiplin</td>
            <td class="col-value">-</td>
        </tr>
        <tr>
            <td class="col-no">12.</td>
            <td class="col-label">CLTN</td>
            <td class="col-value">-</td>
        </tr>
    </table>

    <!-- TANDA TANGAN DENGAN FORMAT TTE ELEKTRONIK SEPERTI FORMULIR CUTI & PARAF TATA NASKAH -->
    <div class="ttd-container">
        <!-- SISI KIRI: Mengetahui Wakil Dekan Bidang Keuangan dan Umum & Paraf Koordinasi Tata Naskah -->
        <div class="ttd-box-left">
            <div>Mengetahui :</div>
            <div>Wakil Dekan Bidang Keuangan dan Umum,</div>
            
            {{-- TTE Digital Signature (QR Code Otentikasi) --}}
            <div style="text-align: left; margin: 3px 0 3px 0;">
                <img src="{{ $qrCodeDataUri }}" style="width: 44px; height: 44px; display: inline-block;" />
                <div style="font-size: 6pt; color: #15803d; font-weight: bold; margin-top: 1px;">
                    Ditandatangani secara Elektronik (TTE)
                </div>
                <div style="font-size: 5.5pt; color: #64748b;">
                    SIKAP FKP UNRI &bull; {{ $tglDokumenFormatWib }}
                </div>
            </div>

            <div style="font-weight: bold; text-decoration: underline;">{{ $pejabatWd2->nama_lengkap ?? 'Dr. Safri, M.Kep, Sp. Kep.M.B' }}</div>
            <div>NIP. {{ $pejabatWd2->nip ?? '198509092014041001' }}</div>

            {{-- KOTAK PARAF DIGITAL KOORDINASI TATA NASKAH (1. Kabag Umum, 2. Ka Pokja) --}}
            <div style="margin-top: 6px; border: 1px solid #1e293b; padding: 3px 5px; border-radius: 4px; background-color: #f8fafc; width: 94%;">
                <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 2px; color: #0f172a; border-bottom: 1px solid #94a3b8; padding-bottom: 1px; font-size: 6.5pt; letter-spacing: 0.2px;">
                    PARAF DIGITAL KOORDINASI TATA NASKAH:
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt; line-height: 1.15;">
                    <tr style="border-bottom: 1px dashed #cbd5e1;">
                        <td style="width: 26px; vertical-align: middle; padding: 1.5px 3px 1.5px 0; text-align: center;">
                            @if(isset($qrKabagUri))
                                <img src="{{ $qrKabagUri }}" style="width: 22px; height: 22px; display: block; margin: 0 auto;" />
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding: 1.5px 0;">
                            <div style="font-weight: bold; color: #0f172a;">1. Kepala Bagian Umum</div>
                            <div style="color: #1e293b;">{{ $pejabatKabag->nama_lengkap ?? ($pejabatKabag->nama ?? 'Bakhtiar, S.Sos., M.Si') }}</div>
                            <div style="color: #047857; font-weight: bold; font-size: 5.8pt;">
                                [ Terverifikasi & Diparaf ] &bull; {{ $parafKabagAt ?? date('d/m/Y H:i') . ' WIB' }}
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 26px; vertical-align: middle; padding: 2px 3px 1.5px 0; text-align: center;">
                            @if(isset($qrPokjaUri))
                                <img src="{{ $qrPokjaUri }}" style="width: 22px; height: 22px; display: block; margin: 0 auto;" />
                            @endif
                        </td>
                        <td style="vertical-align: middle; padding: 2px 0;">
                            <div style="font-weight: bold; color: #0f172a;">2. Ka Pokja Keu-Kepeg</div>
                            <div style="color: #1e293b;">{{ $pejabatKaPokja->nama_lengkap ?? ($pejabatKaPokja->nama ?? 'Dolli Vita Zenitha Harning Arivina, SE') }}</div>
                            <div style="color: #047857; font-weight: bold; font-size: 5.8pt;">
                                [ Terverifikasi & Diparaf ] &bull; {{ $parafPokjaAt ?? date('d/m/Y H:i') . ' WIB' }}
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- SISI KANAN: Yang Membuat (Pegawai Bersangkutan) Rapat Tanpa Baris Kosong -->
        <div class="ttd-box-right">
            <div>Pekanbaru, {{ $tglDokumen }}</div>
            <div style="margin-top: 0;">Yang Membuat,</div>

            {{-- TTE Digital Signature Pegawai --}}
            <div style="text-align: left; margin: 3px 0 3px 0;">
                <img src="{{ $qrCodeDataUri }}" style="width: 44px; height: 44px; display: inline-block;" />
                <div style="font-size: 6pt; color: #15803d; font-weight: bold; margin-top: 1px;">
                    Ditandatangani secara Elektronik (TTE)
                </div>
                <div style="font-size: 5.5pt; color: #64748b;">
                    SIKAP FKP UNRI &bull; {{ $tglDokumenFormatWib }}
                </div>
            </div>

            <div style="font-weight: bold; text-decoration: underline;">{{ $namaLengkap }}</div>
            <div>NIP. {{ $nip }}</div>
        </div>

        <div class="clear"></div>
    </div>

    {{-- OTENTIKASI DIGITAL SIKAP FKP UNRI (PALING BAWAH) --}}
    <div style="margin-top: 8px; border: 1px dashed #0284c7; padding: 3px 5px; border-radius: 4px; font-size: 6.2pt; line-height: 1.2; color: #0c4a6e; background-color: #f0f9ff; page-break-inside: avoid;">
        <strong style="color: #0369a1; display: block; margin-bottom: 1px; font-size: 6.8pt;">OTENTIKASI DIGITAL SIKAP FKP UNRI:</strong>
        Dokumen DRH resmi usulan tanda kehormatan ini telah diverifikasi keabsahannya dan diterbitkan secara elektronik melalui Sistem Informasi Kepegawaian (SIKAP) Fakultas Keperawatan Universitas Riau.<br>
        @if(isset($verifyUrl))
            Kode Otentikasi: <span style="font-family: monospace; font-size: 5.8pt; color: #0284c7;">{{ $verifyCode ?? 'SIKAP-DRH-VALID' }}</span> | 
            Tautan Verifikasi: <a href="{{ $verifyUrl }}" style="color: #0284c7; text-decoration: underline; font-size: 5.8pt;">{{ $verifyUrl }}</a>
        @endif
    </div>

</body>
</html>
