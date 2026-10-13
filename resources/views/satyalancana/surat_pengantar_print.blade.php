<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Surat Pengantar Usulan Tanda Kehormatan Satyalancana Karya Satya - FKP UNRI</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm 15mm 10mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
        }

        /* KOP SURAT FORMULIR CUTI / KGB */
        .kop-surat-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 12px;
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

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10pt;
        }
        .meta-table td {
            vertical-align: top;
            padding: 1.5px 0;
        }

        .content-p {
            text-align: justify;
            text-indent: 32px;
            margin: 8px 0;
            line-height: 1.35;
        }

        /* TABEL LAMPIRAN DAFTAR NOMINASI */
        table.nominasi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin: 10px 0 14px 0;
        }
        table.nominasi-table th, table.nominasi-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.nominasi-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }

        /* AREA TTD & PARAF KOORDINASI */
        .ttd-area-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            page-break-inside: avoid;
        }
        .ttd-area-table td {
            vertical-align: top;
            padding: 0;
            border: none;
        }

        /* KOTAK PARAF DIGITAL */
        .paraf-box {
            border: 1px solid #1e293b;
            padding: 4px 6px;
            border-radius: 4px;
            background-color: #f8fafc;
            font-family: 'Times New Roman', Times, serif;
            width: 95%;
        }

        /* OTENTIKASI DIGITAL DI BAGIAN PALING BAWAH */
        .otentikasi-bar {
            margin-top: 12px;
            border: 1px dashed #0284c7;
            padding: 4px 6px;
            border-radius: 4px;
            font-size: 6.5pt;
            line-height: 1.25;
            color: #0c4a6e;
            background-color: #f0f9ff;
        }
    </style>
</head>
<body>

    {{-- 1. KOP SURAT FORMULIR RESMI --}}
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

    {{-- 2. METADATA SURAT PENGANTAR (SESUAI DOKUMEN DOCX RESMI) --}}
    <table class="meta-table">
        <tr>
            <td style="width: 12%;">Nomor</td>
            <td style="width: 2%;">:</td>
            <td style="width: 50%;">{{ $nomorSurat ?? '2422/UN19.5.1.1.10/KP/' . date('Y') }}</td>
            <td style="width: 36%; text-align: right;">{{ $tanggalSurat ?? \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td>Lampiran</td>
            <td>:</td>
            <td colspan="2">Satu berkas</td>
        </tr>
        <tr>
            <td>Hal</td>
            <td>:</td>
            <td colspan="2">
                <strong>Pengusulan Pemberian Tanda Kehormatan<br>
                Satyalancana Karya Satya {{ $periodeUsulan ?? ('Periode Mei ' . (date('Y') + 1)) }}</strong>
            </td>
        </tr>
    </table>

    {{-- 3. TUJUAN SURAT --}}
    <div style="margin-top: 6px; margin-bottom: 12px; font-size: 10pt; line-height: 1.3;">
        Yth. <strong>Wakil Rektor Bidang Keuangan dan Umum</strong><br>
        Universitas Riau<br>
        Pekanbaru
    </div>

    {{-- 4. ISI SURAT PENGANTAR (DINAMIS SESUAI SURAT MASUK / RUJUKAN DARI REKTOR) --}}
    <p class="content-p">
        Sehubungan dengan surat nomor : {{ $suratRujukanNomor ?? ('200/DST/UN19.5.2.2.1/KP.11.02/' . date('Y')) }} tanggal {{ $suratRujukanTanggal ?? ('22 Juli ' . date('Y')) }} perihal {{ $suratRujukanPerihal ?? ('Pengusulan Tanda Satyalancana Karya Satya ' . ($periodeUsulan ?? ('Periode Mei ' . (date('Y') + 1)))) }}, berikut ini kami mengajukan usulan tanda kehormatan tersebut untuk PNS tenaga kependidikan dan pendidik (dosen) dari Fakultas Keperawatan Universitas Riau, dengan rincian data terlampir:
    </p>

    {{-- 5. TABEL DAFTAR NOMINASI PEGAWAI --}}
    <table class="nominasi-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 30%;">Nama Lengkap & NIP</th>
                <th style="width: 25%;">Pangkat / Gol. / Jabatan</th>
                <th style="width: 15%;">TMT CPNS</th>
                <th style="width: 13%;">Masa Kerja</th>
                <th style="width: 12%;">Kategori</th>
            </tr>
        </thead>
        <tbody>
            @forelse($daftarPegawai as $p)
                @php
                    $mkgY = $p->tmt_sk_pertama ? \Carbon\Carbon::parse($p->tmt_sk_pertama)->diffInYears(now()) : ($p->tanggal_masuk ? \Carbon\Carbon::parse($p->tanggal_masuk)->diffInYears(now()) : 10);
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $p->nama_lengkap ?? $p->nama }}</strong><br>
                        <span style="font-size: 8pt; color: #475569;">NIP. {{ $p->nip }}</span>
                    </td>
                    <td>
                        {{ $p->golongan->nama_pangkat ?? '-' }} ({{ $p->golongan->nama_golongan ?? '-' }})<br>
                        <span style="font-size: 8pt;">{{ $p->jabatan->nama_jabatan ?? 'Dosen / PLP' }}</span>
                    </td>
                    <td style="text-align: center;">
                        {{ $p->tmt_sk_pertama ? \Carbon\Carbon::parse($p->tmt_sk_pertama)->format('d-m-Y') : ($p->tanggal_masuk ? \Carbon\Carbon::parse($p->tanggal_masuk)->format('d-m-Y') : '-') }}
                    </td>
                    <td style="text-align: center; font-weight: bold;">{{ $mkgY }} Thn</td>
                    <td style="text-align: center;">
                        <strong>{{ $p->kategori_satyalancana ?? '10 Tahun' }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #64748b;">Belum ada daftar pegawai terlampir.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="content-p">
        Demikian disampaikan, atas perhatian, bantuan, dan kerjasamanya kami ucapkan terima kasih.
    </p>

    {{-- 6. TTD WAKIL DEKAN II DENGAN PARAF DIGITAL TATA NASKAH (1. KA POKJA, 2. KABAG UMUM) --}}
    <table class="ttd-area-table">
        <tr>
            {{-- SISI KIRI: PARAF KOORDINASI TATA NASKAH --}}
            <td style="width: 52%; vertical-align: top; padding-right: 10px;">
                <div class="paraf-box">
                    <div style="font-weight: bold; text-transform: uppercase; margin-bottom: 3px; color: #0f172a; border-bottom: 1px solid #94a3b8; padding-bottom: 2px; font-size: 7pt; letter-spacing: 0.2px;">
                        PARAF DIGITAL KOORDINASI TATA NASKAH:
                    </div>
                    <table style="width: 100%; border-collapse: collapse; font-size: 7pt; line-height: 1.15;">
                        {{-- 1. Ka Pokja Keu-Kepeg --}}
                        <tr style="border-bottom: 1px dashed #cbd5e1;">
                            <td style="width: 32px; vertical-align: middle; padding: 2px 4px 2px 0; text-align: center;">
                                @if(isset($qrPokjaUri))
                                    <img src="{{ $qrPokjaUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding: 2px 0;">
                                <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">1. Ka Pokja Keu-Kepeg</div>
                                <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKaPokja->nama_lengkap ?? ($pejabatKaPokja->nama ?? 'Dolli Vita Zenitha Harning Arivina, SE') }}</div>
                                <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                    [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafPokjaAt ?? date('d/m/Y H:i') . ' WIB' }}
                                </div>
                            </td>
                        </tr>
                        {{-- 2. Kepala Bagian Umum --}}
                        <tr>
                            <td style="width: 32px; vertical-align: middle; padding: 3px 4px 2px 0; text-align: center;">
                                @if(isset($qrKabagUri))
                                    <img src="{{ $qrKabagUri }}" style="width: 28px; height: 28px; display: block; margin: 0 auto;" />
                                @endif
                            </td>
                            <td style="vertical-align: middle; padding: 3px 0;">
                                <div style="font-weight: bold; color: #0f172a; font-size: 7pt;">2. Kepala Bagian Umum</div>
                                <div style="color: #1e293b; font-size: 6.8pt;">{{ $pejabatKabag->nama_lengkap ?? ($pejabatKabag->nama ?? 'Bakhtiar, S.Sos., M.Si') }}</div>
                                <div style="color: #047857; font-weight: bold; font-size: 6.2pt;">
                                    [ Terverifikasi & Diparaf Digital ] &bull; {{ $parafKabagAt ?? date('d/m/Y H:i') . ' WIB' }}
                                </div>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>

            {{-- SISI KANAN: TANDA TANGAN WAKIL DEKAN II --}}
            <td style="width: 48%; text-align: left; vertical-align: top; padding-left: 8px;">
                <div style="font-size: 9.5pt; line-height: 1.25;">
                    Wakil Dekan,<br>
                    Bidang Keuangan dan Umum,
                    
                    {{-- QR Code TTE Pimpinan --}}
                    <div style="text-align: left; margin: 4px 0 4px 0;">
                        @if(isset($qrWd2Uri))
                            <img src="{{ $qrWd2Uri }}" style="width: 46px; height: 46px; display: inline-block;" />
                            <div style="font-size: 6pt; color: #15803d; font-weight: bold;">
                                Ditandatangani secara Elektronik (TTE)
                            </div>
                        @else
                            <div style="height: 48px;"></div>
                        @endif
                    </div>

                    <strong style="text-decoration: underline;">{{ $pejabatWd2->nama_lengkap ?? ($pejabatWd2->nama ?? 'Dr. Safri, M.Kep., Sp.Kep.M.B') }}</strong><br>
                    <span>NIP. {{ $pejabatWd2->nip ?? '198509092014041001' }}</span>
                </div>
            </td>
        </tr>
    </table>

    {{-- 7. OTENTIKASI DIGITAL SIKAP FKP UNRI --}}
    <div class="otentikasi-bar">
        <strong style="color: #0369a1; display: block; margin-bottom: 1px; font-size: 7pt;">OTENTIKASI DIGITAL SIKAP FKP UNRI:</strong>
        Dokumen surat pengantar resmi ini telah diverifikasi validitas koordinasi tata naskah dan diterbitkan secara elektronik melalui Sistem Informasi Kepegawaian (SIKAP) Fakultas Keperawatan Universitas Riau.<br>
        @if(isset($verifyUrl))
            Kode Otentikasi: <span style="font-family: monospace; font-size: 6pt; color: #0284c7;">{{ $verifyCode ?? 'SIKAP-SLKS-VALID' }}</span> | 
            Tautan Verifikasi Keabsahan: <a href="{{ $verifyUrl }}" style="color: #0284c7; text-decoration: underline; font-size: 6pt;">{{ $verifyUrl }}</a>
        @endif
    </div>

</body>
</html>
