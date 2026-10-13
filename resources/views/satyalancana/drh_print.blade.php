<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Riwayat Hidup Usulan Tanda Kehormatan Satyalancana Karya Satya - {{ $pegawai->nama_lengkap ?? $pegawai->nama }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 18mm 12mm 18mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.25;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .kop-instansi {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.2;
            letter-spacing: 0.5px;
        }
        .kop-univ {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.2;
        }
        .kop-fakultas {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.2;
        }
        .kop-alamat {
            font-size: 7.5pt;
            text-align: center;
            line-height: 1.2;
            margin-top: 3px;
        }
        .title-box {
            text-align: center;
            margin: 10px 0 16px 0;
        }
        .title-box h1 {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            letter-spacing: 0.5px;
        }
        .title-box h2 {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin: 3px 0 0 0;
            padding: 0;
            letter-spacing: 0.5px;
        }
        table.drh-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 16px;
        }
        table.drh-table td {
            border: 1px solid #000;
            padding: 4px 6px;
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
        .ttd-container {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .ttd-box-left {
            float: left;
            width: 50%;
            text-align: left;
            font-size: 9.5pt;
            line-height: 1.25;
        }
        .ttd-box-right {
            float: right;
            width: 48%;
            text-align: left;
            font-size: 9.5pt;
            line-height: 1.25;
        }
        .stempel-img {
            max-width: 135px;
            max-height: 75px;
            margin-top: -20px;
            margin-bottom: -15px;
            margin-left: -10px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI UNIVERSITAS RIAU FAKULTAS KEPERAWATAN -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('logo-unri.png') }}" style="height: 68px;" onerror="this.style.display='none'">
            </td>
            <td style="width: 85%; text-align: center;">
                <div class="kop-instansi">KEMENTERIAN PENDIDIKAN TINGGI,<br>SAINS, DAN TEKNOLOGI</div>
                <div class="kop-univ">UNIVERSITAS RIAU</div>
                <div class="kop-fakultas">FAKULTAS KEPERAWATAN</div>
                <div class="kop-alamat">
                    Kampus Bina Widya Gedung Health Studies Complex Km.12,5 Simpang Baru 28293<br>
                    Laman http://keperawatan.unri.ac.id Email : keperawatan@unri.ac.id
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

        // 7. SK CPNS (TMT)
        $noSkCpns = $pegawai->nomor_sk_pertama ?: 'Nomor: 19597/A4/KP/2011';
        $tglSkCpns = $pegawai->tanggal_sk_pertama ? \Carbon\Carbon::parse($pegawai->tanggal_sk_pertama)->locale('id')->translatedFormat('d F Y') : '14 April 2011';
        $tmtCpnsObj = $pegawai->tmt_sk_pertama ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->locale('id') : ($pegawai->tanggal_masuk ? \Carbon\Carbon::parse($pegawai->tanggal_masuk)->locale('id') : null);
        $tmtCpnsStr = $tmtCpnsObj ? $tmtCpnsObj->translatedFormat('d F Y') : '1 Desember 2010';

        // 8. Jabatan Terakhir (TMT)
        $namaJabatan = $pegawai->jabatan->nama_jabatan ?? '';
        // Ubah label teknis jika perlu, misal PLP
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
                Nomor: {{ $noSkCpns }}, Tanggal {{ $tglSkCpns }}<br>
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

    <!-- TANDA TANGAN (MENGETAHUI WD2 & YANG MEMBUAT) -->
    <div class="ttd-container">
        <!-- Mengetahui Wakil Dekan Bidang Keuangan dan Umum -->
        <div class="ttd-box-left">
            Mengetahui :<br>
            Wakil Dekan Bidang Keuangan dan Umum,
            <br><br><br><br><br>
            <strong><u>{{ $pejabatWd2->nama_lengkap ?? 'Dr. Safri, M.Kep, Sp. Kep.M.B' }}</u></strong><br>
            NIP. {{ $pejabatWd2->nip ?? '198509092014041001' }}
        </div>

        <!-- Yang Membuat (Pegawai Bersangkutan) -->
        <div class="ttd-box-right">
            Pekanbaru, {{ $tglDokumen }}<br><br>
            Yang Membuat,
            <br><br><br><br>
            <strong><u>{{ $namaLengkap }}</u></strong><br>
            NIP. {{ $nip }}
        </div>

        <div class="clear"></div>
    </div>

</body>
</html>
