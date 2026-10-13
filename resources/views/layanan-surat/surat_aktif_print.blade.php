<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Aktif Bekerja - {{ $pegawai->nama_lengkap ?? $pegawai->nama }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 15mm 20mm 15mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
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
        }
        .kop-univ {
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.2;
        }
        .kop-fakultas {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.2;
        }
        .kop-alamat {
            font-size: 8.5pt;
            text-align: center;
            font-family: Arial, sans-serif;
            line-height: 1.2;
            margin-top: 4px;
        }
        .title-surat {
            text-align: center;
            margin: 16px 0 20px 0;
        }
        .title-surat h1 {
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
        }
        .title-surat p {
            font-size: 10.5pt;
            margin: 2px 0 0 0;
        }
        .content-p {
            text-align: justify;
            text-indent: 30px;
            margin-bottom: 10px;
        }
        table.identitas-table {
            width: 100%;
            margin: 6px 0 12px 20px;
            border-collapse: collapse;
            font-size: 11pt;
        }
        table.identitas-table td {
            padding: 2.5px 4px;
            vertical-align: top;
        }
        .ttd-container {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .ttd-box {
            float: right;
            width: 48%;
            text-align: left;
        }
        .qr-section {
            float: left;
            width: 48%;
            text-align: center;
            padding-top: 10px;
        }
        .qr-img {
            width: 85px;
            height: 85px;
        }
        .qr-desc {
            font-family: Arial, sans-serif;
            font-size: 7.5pt;
            color: #475569;
            margin-top: 4px;
            line-height: 1.2;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI UNIVERSITAS RIAU -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('logo-unri.png') }}" style="height: 70px;" onerror="this.style.display='none'">
            </td>
            <td style="width: 85%;">
                <div class="kop-instansi">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div class="kop-univ">UNIVERSITAS RIAU</div>
                <div class="kop-fakultas">FAKULTAS KEPERAWATAN</div>
                <div class="kop-alamat">
                    Kampus Bina Widya Km. 12,5 Simpang Baru Pekanbaru Kode Pos 28293<br>
                    Telepon (0761) 63276 Laman: fkep.unri.ac.id Pos-el: keperawatan@unri.ac.id
                </div>
            </td>
        </tr>
    </table>

    <!-- JUDUL DAN NOMOR SURAT -->
    <div class="title-surat">
        <h1>SURAT KETERANGAN AKTIF BEKERJA</h1>
        <p>Nomor: {{ $surat->nomor_surat }}</p>
    </div>

    <!-- PARAGRAF PEMBUKA -->
    <p class="content-p">
        Yang bertanda tangan di bawah ini:
    </p>

    <!-- DATA PEJABAT PENANDATANGAN -->
    <table class="identitas-table">
        <tr>
            <td style="width: 25%;">Nama</td>
            <td style="width: 2%;">:</td>
            <td style="width: 73%; font-weight: bold;">{{ $surat->penandatangan_nama }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $surat->penandatangan_nip }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $surat->penandatangan_jabatan }} Universitas Riau</td>
        </tr>
    </table>

    <p class="content-p">
        Dengan ini menerangkan dengan sesungguhnya bahwa:
    </p>

    <!-- DATA PEGAWAI BERSANGKUTAN -->
    <table class="identitas-table">
        <tr>
            <td style="width: 25%;">Nama Lengkap</td>
            <td style="width: 2%;">:</td>
            <td style="width: 73%; font-weight: bold;">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</td>
        </tr>
        <tr>
            <td>NIP / NIDN</td>
            <td>:</td>
            <td>{{ $pegawai->nip }} {{ $pegawai->nidn_nuptk ? ' / ' . $pegawai->nidn_nuptk : '' }}</td>
        </tr>
        <tr>
            <td>Pangkat / Golongan</td>
            <td>:</td>
            <td>{{ $pegawai->golongan->nama_golongan ?? '-' }} ({{ $pegawai->golongan->kode_golongan ?? '-' }})</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>:</td>
            <td>{{ $pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }} Universitas Riau</td>
        </tr>
        <tr>
            <td>Status Kepegawaian</td>
            <td>:</td>
            <td>{{ $pegawai->status_asn ?? 'PNS' }} (Status: {{ $pegawai->status_pegawai ?? 'Aktif' }})</td>
        </tr>
    </table>

    <!-- PARAGRAF PERNYATAAN AKTIF & KEPERLUAN -->
    <p class="content-p">
        Adalah benar yang bersangkutan merupakan Dosen / Tenaga Kependidikan tetap pada Fakultas Keperawatan Universitas Riau dan sampai saat surat keterangan ini diterbitkan masih <strong>Aktif Bekerja</strong> menjalankan tugas dan kewajibannya sebagaimana mestinya.
    </p>

    <p class="content-p">
        Surat keterangan ini diberikan atas permohonan yang bersangkutan untuk keperluan: <em>"{{ $surat->keperluan }}"</em>.
    </p>

    <p class="content-p">
        Demikian surat keterangan ini kami buat dengan sebenarnya agar dapat dipergunakan sebagaimana mestinya oleh pihak yang berkepentingan.
    </p>

    <!-- BLOK TANDA TANGAN & QR CODE VERIFIKASI -->
    <div class="ttd-container">
        <!-- QR Verifikasi Digital -->
        <div class="qr-section">
            <img src="{{ $qrCodeDataUri }}" class="qr-img" alt="QR Verifikasi">
            <div class="qr-desc">
                <strong>DOKUMEN RESMI ELEKTRONIK</strong><br>
                Validitas dokumen dapat diverifikasi secara daring<br>
                melalui pemindaian QR Code di atas.<br>
                <em>SIKAP FKP UNRI</em>
            </div>
        </div>

        <!-- Tanda Tangan Dekan -->
        <div class="ttd-box">
            Pekanbaru, {{ \Carbon\Carbon::parse($surat->tanggal_surat)->locale('id')->translatedFormat('d F Y') }}<br>
            {{ $surat->penandatangan_jabatan }},
            <br><br><br><br><br>
            <strong><u>{{ $surat->penandatangan_nama }}</u></strong><br>
            NIP. {{ $surat->penandatangan_nip }}
        </div>

        <div class="clear"></div>
    </div>

</body>
</html>
