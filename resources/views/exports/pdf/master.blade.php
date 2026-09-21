<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title', 'Dokumen Kepegawaian')</title>
    <style>
        /* Setup Halaman Resmi Kedinasan Sesuai Permendikti Saintek No. 42 Tahun 2025 */
        @page {
            /* Pasal 47: Kiri min 3cm (30mm), Kanan min 2cm (20mm), Bawah min 2.5cm (25mm), Atas 15-20mm */
            margin: 15mm 20mm 25mm 30mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #000;
            margin: 0;
            padding: 0;
        }

        /* STYLING KOP SURAT SESUAI LAMPIRAN PERMENDIKTI SAINTEK NO. 42 TAHUN 2025 */
        .kop-surat-table {
            width: 100%;
            border-collapse: collapse;
            /* Garis penutup kop: Garis tebal tunggal solid (Lampiran hal. 76 angka 9 & 10) */
            border-bottom: 2.5px solid #000000;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }

        .kop-surat-table td {
            vertical-align: middle;
            padding: 0;
        }

        .kop-logo-cell {
            /* Lambang PTN ukuran tinggi 3 cm dan lebar 3 cm (Lampiran hal. 76 angka 1) */
            width: 32mm;
            text-align: left;
        }

        .kop-logo-cell img {
            width: 30mm;
            height: 30mm;
            display: block;
        }

        .kop-text-cell {
            text-align: center;
            padding-right: 15px; /* Menyeimbangkan posisi teks dengan logo di sebelah kiri */
        }

        /* Baris 1: KEMENTERIAN - Times New Roman 16 pt, kapital, reguler */
        .kop-text-cell .kop-kemdikti {
            margin: 0;
            font-size: 15pt;
            font-weight: normal;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.2px;
        }

        /* Baris 2: NAMA PTN - Times New Roman 14 pt, kapital, dicetak tebal/bold */
        .kop-text-cell .kop-ptn {
            margin: 2px 0 0 0;
            font-size: 13.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.4px;
        }

        /* Baris 3: NAMA FAKULTAS - Times New Roman 14 pt, kapital, dicetak tebal/bold */
        .kop-text-cell .kop-fakultas {
            margin: 2px 0 0 0;
            font-size: 13.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
        }

        /* Baris 4 & 5: Alamat, Telepon, Laman, Pos-el - Times New Roman 10-12 pt */
        .kop-text-cell .kop-alamat {
            margin: 4px 0 0 0;
            font-size: 9.5pt;
            font-family: 'Times New Roman', Times, serif;
            font-weight: normal;
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
            font-weight: normal;
            text-decoration: none;
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
                    $candidatePaths = [
                        public_path('images/logo-unri-bw.png'),
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
                <div class="kop-kemdikti">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div class="kop-ptn">UNIVERSITAS RIAU</div>
                <div class="kop-fakultas">FAKULTAS KEPERAWATAN</div>
                <div class="kop-alamat">
                    Kampus Bina Widya Gedung Health Studies Complex Km. 12,5 Simpang Baru, Pekanbaru 28293<br>
                    Laman keperawatan.unri.ac.id Pos-el keperawatan@unri.ac.id
                </div>
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
                    Wakil Dekan Bidang Keuangan dan Umum<br>Fakultas Keperawatan Universitas Riau
                </p>
                <p class="nama">Ns. Safri, M.Kep., Sp.Kep.M.B</p>
                <p class="nip">NIP. 19850909 201404 1 001</p>
            </div>
            <div class="clear"></div>
        </div>
    @endif

</body>
</html>