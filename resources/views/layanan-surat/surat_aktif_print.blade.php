<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Keterangan Aktif Bekerja - {{ $pegawai->nama_lengkap ?? $pegawai->nama }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 18mm 12mm 18mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.35;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .kop-instansi {
            font-size: 10.5pt;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.2;
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
            font-size: 8pt;
            text-align: center;
            font-family: Arial, sans-serif;
            line-height: 1.2;
            margin-top: 3px;
        }
        .title-surat {
            text-align: center;
            margin: 10px 0 14px 0;
        }
        .title-surat h1 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
        }
        .title-surat p {
            font-size: 10pt;
            margin: 2px 0 0 0;
        }
        .content-p {
            text-align: justify;
            text-indent: 28px;
            margin-bottom: 7px;
            font-size: 10.5pt;
        }
        table.identitas-table {
            width: 100%;
            margin: 4px 0 8px 18px;
            border-collapse: collapse;
            font-size: 10.5pt;
        }
        table.identitas-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .ttd-table {
            width: 100%;
            margin-top: 14px;
            page-break-inside: avoid;
            border-collapse: collapse;
        }
        .paraf-box {
            border: 1px solid #1e293b;
            padding: 5px 6px;
            border-radius: 4px;
            background-color: #f8fafc;
            font-family: 'Times New Roman', Times, serif;
            margin-bottom: 6px;
        }
        .paraf-header {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 3px;
            color: #0f172a;
            border-bottom: 1px solid #94a3b8;
            padding-bottom: 2px;
            font-size: 7pt;
            letter-spacing: 0.2px;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT RESMI UNIVERSITAS RIAU -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('logo-unri.png') }}" style="height: 65px;" onerror="this.style.display='none'">
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
            <td>
                @if(str_contains(strtolower($surat->penandatangan_jabatan), 'wakil dekan'))
                    Wakil Dekan Bidang Keuangan dan Umum Fakultas Keperawatan Universitas Riau
                @else
                    {{ $surat->penandatangan_jabatan }} Universitas Riau
                @endif
            </td>
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

    <!-- AREA TANDA TANGAN & PARAF DIGITAL KOORDINASI TATA NASKAH (KONSEP RESMI SIKAP) -->
    <table class="ttd-table">
        <tr>
            <!-- SISI KIRI: PARAF DIGITAL KOORDINASI TATA NASKAH & QR OTENTIKASI -->
            <td style="width: 53%; vertical-align: top; padding-right: 12px;">
                <div class="paraf-box">
                    <div class="paraf-header">
                        PARAF DIGITAL KOORDINASI TATA NASKAH:
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 7pt; line-height: 1.15;">
                        <!-- 1. Kepala Bagian Umum -->
                        <tr style="border-bottom: 1px dashed #cbd5e1;">
                            <td style="width: 32px; vertical-align: middle; padding: 2px 4px 2px 0; text-align: center;">
                                @if(isset($qrKabagUri))
                                    <img src="{{ $qrKabagUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding: 2px 0;">
                                <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">1. Kepala Bagian Umum</div>
                                <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKabag->nama_lengkap ?? ($pejabatKabag->nama ?? 'Bakhtiar, S.Sos., M.Si') }}</div>
                                <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                    [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafKabagAt }}
                                </div>
                            </td>
                        </tr>
                        <!-- 2. Ka Pokja Keu-Kepeg -->
                        <tr>
                            <td style="width: 32px; vertical-align: middle; padding: 3px 4px 2px 0; text-align: center;">
                                @if(isset($qrPokjaUri))
                                    <img src="{{ $qrPokjaUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding: 3px 0;">
                                <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">2. Ka Pokja Keu-Kepeg</div>
                                <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKaPokja->nama_lengkap ?? ($pejabatKaPokja->nama ?? 'Dolli Vita Zenitha Harning Arivina, SE') }}</div>
                                <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                    [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafPokjaAt }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- KOTAK OTENTIKASI RESMI DOKUMEN ELEKTRONIK -->
                <div style="border: 1px dashed #0284c7; padding: 4px 6px; border-radius: 4px; background-color: #f0f9ff; font-family: Arial, sans-serif;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="width: 44px; vertical-align: middle; text-align: center; padding-right: 6px;">
                                <img src="{{ $qrCodeDataUri }}" style="width: 40px; height: 40px; display: block; margin: 0 auto;" alt="QR Otentikasi">
                            </td>
                            <td style="vertical-align: middle; font-size: 6.2pt; line-height: 1.2; color: #0c4a6e;">
                                <strong style="color: #0369a1; font-size: 6.8pt; display: block; margin-bottom: 1px;">DOKUMEN RESMI ELEKTRONIK SIKAP</strong>
                                Dokumen ini sah diterbitkan secara elektronik melalui SIKAP FKP UNRI. Validitas dokumen dapat diverifikasi secara daring.<br>
                                <span style="font-family: monospace; font-size: 5.6pt; color: #0284c7;">Kode: {{ substr($surat->qr_token, 0, 30) }}...</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            <!-- SISI KANAN: TANDA TANGAN PEJABAT -->
            <td style="width: 47%; text-align: left; vertical-align: top; padding-left: 10px;">
                <div style="font-size: 10pt; line-height: 1.25;">
                    Pekanbaru, {{ \Carbon\Carbon::parse($surat->tanggal_surat)->locale('id')->translatedFormat('d F Y') }}<br>
                    @if(str_contains(strtolower($surat->penandatangan_jabatan), 'wakil dekan'))
                        a.n. Dekan Fakultas Keperawatan,<br>
                        Wakil Dekan Bidang Keuangan dan Umum,
                    @else
                        {{ $surat->penandatangan_jabatan }},
                    @endif
                    <div style="height: 48px;"></div>
                    <strong style="text-decoration: underline;">{{ $surat->penandatangan_nama }}</strong><br>
                    <span>NIP. {{ $surat->penandatangan_nip }}</span>
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
