<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Dokumen Kepegawaian')</title>
    <style>
        /* Setup Halaman Resmi Kedinasan */
        @page {
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* STYLING KOP SURAT PROFESIONAL */
        .kop-surat-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #000000;
            padding-bottom: 6px;
            margin-bottom: 18px;
        }

        .kop-surat-table td {
            vertical-align: middle;
            padding: 0;
        }

        .kop-logo-cell {
            width: 90px;
            text-align: left;
        }

        .kop-logo-cell img {
            width: 82px;
            height: auto;
            display: block;
        }

        .kop-text-cell {
            text-align: center;
            padding-right: 20px; /* Menyeimbangkan posisi teks dengan logo */
        }

        .kop-text-cell h3 {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .kop-text-cell h2 {
            margin: 1px 0;
            font-size: 13.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-text-cell .sub-header {
            margin: 0;
            font-size: 11.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
        }

        .kop-text-cell p {
            margin: 3px 0 0 0;
            font-size: 8.5pt;
            font-style: normal;
            line-height: 1.2;
        }

        /* KONTEN LAPORAN */
        .content {
            width: 100%;
        }

        .document-title {
            text-align: center;
            margin-bottom: 15px;
        }

        .document-title h4 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .document-title p {
            margin: 2px 0 0 0;
            font-size: 10pt;
        }

        /* STYLING TABEL DATA/FORM */
        table.data-table, table.form-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        table.data-table th, 
        table.data-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 10pt;
        }

        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }

        table.form-table td {
            padding: 4px 4px;
            font-size: 10pt;
            vertical-align: top;
        }

        .section-header {
            font-weight: bold;
            font-size: 10.5pt;
            background-color: #e6e6e6;
            padding: 4px 6px;
            margin-top: 10px;
            margin-bottom: 6px;
            border-left: 3px solid #000;
            text-transform: uppercase;
        }

        /* AREA TANDA TANGAN */
        .ttd-container {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .ttd-box {
            float: right;
            width: 45%;
            text-align: left;
        }

        .ttd-box .jabatan {
            font-size: 10pt;
            margin-bottom: 55px;
            line-height: 1.25;
        }

        .ttd-box .nama {
            font-weight: bold;
            text-decoration: underline;
            font-size: 10.5pt;
            margin: 0;
        }

        .ttd-box .nip {
            font-size: 10pt;
            margin: 2px 0 0 0;
        }

        .clear { clear: both; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
    @stack('styles')
</head>
<body>

    <!-- KOP SURAT INSTANSI RESMI -->
    <table class="kop-surat-table">
        <tr>
            <td class="kop-logo-cell">
                @php
                    // Fallback pencarian lokasi file logo UNRI
                    $candidatePaths = [
                        public_path('build/assets/logo-unri.png'),
                        public_path('images/logo-unri.png'),
                        public_path('assets/logo-unri.png'),
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
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($foundLogo)) }}" alt="Logo UNRI">
                @else
                    <div style="font-size: 8pt; color: #666; text-align: center;">[LOGO UNRI]</div>
                @endif
            </td>
            <td class="kop-text-cell">
                <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
                <h2>UNIVERSITAS RIAU</h2>
                <div class="sub-header">FAKULTAS KEPERAWATAN</div>
                <p>
                    Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru 28293<br>
                    Laman: www.keperawatan.unri.ac.id | Email: keperawatan@unri.co.id
                </p>
            </td>
        </tr>
    </table>

    <!-- KONTEN UTAMA LAPORAN / BIODATA -->
    <div class="content">
        @yield('content')
    </div>

    <!-- TANDA TANGAN PEJABAT -->
    @hasSection('ttd')
        @yield('ttd')
    @else
        <div class="ttd-container">
            <div class="ttd-box">
                <p class="jabatan">
                    Ditetapkan di Pekanbaru<br>
                    Pada tanggal {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br><br>
                    <strong>Wakil Dekan Bidang Keuangan dan Umum<br>Fakultas Keperawatan Universitas Riau</strong>
                </p>
                <p class="nama">Ns. Safri, M.Kep., Sp.Kep.M.B</p>
                <p class="nip">NIP. 19850909 201404 1 001</p>
            </div>
            <div class="clear"></div>
        </div>
    @endif

</body>
</html>