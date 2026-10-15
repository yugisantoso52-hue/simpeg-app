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
        /* KOP SURAT RESMI SESUAI STANDAR PERMENDIKTI SAINTEK NO. 42 TAHUN 2025 (SEPERTI KGB) */
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
            font-weight: normal;
            line-height: 1.25;
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

    <!-- KOP SURAT INSTANSI RESMI (FORMAT KENAIKAN GAJI BERKALA) -->
    <table class="kop-surat-table">
        <tr>
            <td class="kop-logo-cell">
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
                                @if(!empty($sudahParafKabag) && isset($qrKabagUri))
                                    <img src="{{ $qrKabagUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                                @else
                                    <div style="font-size: 5.5pt; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px 1px; border-radius: 2px; line-height: 1.1;">Belum<br>Paraf</div>
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding: 2px 0;">
                                <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">1. Kepala Bagian Umum</div>
                                <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKabag->nama_lengkap ?? ($pejabatKabag->nama ?? 'Bakhtiar, S.Sos., M.Si') }}</div>
                                @if(!empty($sudahParafKabag))
                                    <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                        [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafKabagAt }}
                                    </div>
                                @else
                                    <div style="color: #b45309; font-weight: bold; font-size: 6.2pt;">
                                        [ Menunggu Telaah Tata Naskah & Paraf Kabag ]
                                    </div>
                                @endif
                            </td>
                        </tr>
                        <!-- 2. Ka Pokja Keu-Kepeg -->
                        <tr>
                            <td style="width: 32px; vertical-align: middle; padding: 3px 4px 2px 0; text-align: center;">
                                @if(!empty($sudahParafPokja) && isset($qrPokjaUri))
                                    <img src="{{ $qrPokjaUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                                @else
                                    <div style="font-size: 5.5pt; color: #94a3b8; border: 1px dashed #cbd5e1; padding: 4px 1px; border-radius: 2px; line-height: 1.1;">Belum<br>Paraf</div>
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding: 3px 0;">
                                <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">2. Ka Pokja Keu-Kepeg</div>
                                <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKaPokja->nama_lengkap ?? ($pejabatKaPokja->nama ?? 'Dolli Vita Zenitha Harning Arivina, SE') }}</div>
                                @if(!empty($sudahParafPokja))
                                    <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                        [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafPokjaAt }}
                                    </div>
                                @else
                                    <div style="color: #b45309; font-weight: bold; font-size: 6.2pt;">
                                        [ Menunggu Verifikasi Keaktifan & Paraf Ka Pokja ]
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- KOTAK OTENTIKASI RESMI DOKUMEN ELEKTRONIK -->
                @if(!empty($isDisahkan) && isset($qrCodeDataUri))
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
                @else
                    <div style="border: 1px dashed #e11d48; padding: 5px 8px; border-radius: 4px; background-color: #fff1f2; font-family: Arial, sans-serif;">
                        <div style="font-size: 6.8pt; font-weight: bold; color: #be123c;">STATUS: DRAF SURAT KETERANGAN (BELUM DISAHKAN)</div>
                        <div style="font-size: 5.8pt; color: #9f1239; line-height: 1.2; margin-top: 1px;">
                            Surat ini masih dalam proses alur verifikasi berkas dan belum disahkan secara elektronik oleh Pimpinan Fakultas Keperawatan UNRI.
                        </div>
                    </div>
                @endif
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
                    @if(!empty($isDisahkan))
                        <div style="height: 48px;"></div>
                        <strong style="text-decoration: underline;">{{ $surat->penandatangan_nama }}</strong><br>
                        <span>NIP. {{ $surat->penandatangan_nip }}</span>
                    @else
                        <div style="margin: 8px 0 6px 0;">
                            <span style="display: inline-block; padding: 2px 6px; border: 1px dashed #e11d48; background-color: #fff1f2; color: #be123c; font-size: 7pt; font-weight: bold; border-radius: 3px;">
                                [ DRAF - MENUNGGU PENGESAHAN ]
                            </span>
                        </div>
                        <div style="color: #64748b; font-weight: bold; text-decoration: underline;">( {{ $surat->penandatangan_nama }} )</div>
                        <div style="color: #64748b;">NIP. {{ $surat->penandatangan_nip }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
