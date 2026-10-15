<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Panduan & Landasan Regulasi Tata Naskah Dinas & Paraf Digital</title>
    <style>
        @page {
            margin: 1.5cm 2cm 1.5cm 2cm;
            font-family: 'Times New Roman', Times, serif;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #111;
        }
        .header-table {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
            border: none !important;
            padding: 0;
        }
        .logo-kemdikbud {
            width: 85px;
            height: auto;
        }
        .header-text {
            text-align: center;
            line-height: 1.15;
        }
        .instansi-title {
            font-size: 11pt;
            font-weight: normal;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .univ-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0;
        }
        .fakultas-title {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin: 2px 0;
        }
        .alamat-text {
            font-size: 8pt;
            font-weight: normal;
            margin-top: 3px;
            color: #222;
        }
        .line-separator {
            border-top: 2.5px solid #000;
            border-bottom: 0.75px solid #000;
            height: 2px;
            margin-bottom: 16px;
        }
        .doc-title {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            text-decoration: underline;
            margin-bottom: 2px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 9.5pt;
            font-weight: bold;
            margin-bottom: 18px;
            color: #333;
        }
        .section-header {
            font-size: 10.5pt;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 5px 8px;
            border-left: 3.5px solid #1e3a8a;
            margin-top: 14px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        p {
            margin: 0 0 8px 0;
            text-align: justify;
        }
        .table-data {
            width: 100% !important;
            border-collapse: collapse !important;
            margin-top: 6px;
            margin-bottom: 12px;
            font-size: 9pt;
        }
        .table-data th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            padding: 5px 6px;
            border: 1px solid #1e3a8a;
        }
        .table-data td {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: top;
        }
        .table-data tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .badge-primary { background: #e0e7ff; color: #3730a3; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        
        .box-warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            padding: 8px 10px;
            font-size: 9pt;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .box-info {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            padding: 8px 10px;
            font-size: 9pt;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .page-break {
            page-break-after: always;
        }
        .footer-note {
            font-size: 7.5pt;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI SURAT -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: left;">
                @if(file_exists(public_path('assets/images/logo-kemdikbud.png')))
                    <img src="{{ public_path('assets/images/logo-kemdikbud.png') }}" class="logo-kemdikbud" alt="Logo Kemdikbud">
                @elseif(file_exists(public_path('logo-unri.png')))
                    <img src="{{ public_path('logo-unri.png') }}" class="logo-kemdikbud" alt="Logo UNRI">
                @endif
            </td>
            <td style="width: 85%;">
                <div class="header-text">
                    <div class="instansi-title">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                    <div class="univ-title">UNIVERSITAS RIAU</div>
                    <div class="fakultas-title">FAKULTAS KEPERAWATAN</div>
                    <div class="alamat-text">
                        Kampus Bina Widya Km. 12,5 Simpang Baru, Pekanbaru 28293<br>
                        Laman: fkep.unri.ac.id | Pos-el: keperawatan@unri.ac.id
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="line-separator"></div>

    <div class="doc-title">PANDUAN YURIDIS & TATA KELOLA ADMINISTRASI ELEKTRONIK</div>
    <div class="doc-subtitle">STANDARISASI PENANDATANGANAN MANDAT & KOORDINASI PARAF DIGITAL DOKUMEN KEPEGAWAIAN</div>

    <div class="box-info">
        <strong>Pernyataan Kebijakan:</strong> Panduan ini diterbitkan sebagai pedoman resmi tata naskah dinas elektronik dan acuan bagi Pejabat Penandatangan, Pejabat Pemeriksa Paraf Digital (Katim TU / Subkoordinator), serta seluruh ASN di lingkungan Fakultas Keperawatan Universitas Riau dalam rangka menjamin kepastian hukum, validitas mandat, dan integritas dokumen kepegawaian digital.
    </div>

    <!-- 1. LANDASAN HUKUM UTAMA -->
    <div class="section-header">1. Landasan Hukum & Regulasi Pengikat</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 28%;">Dasar Regulasi</th>
                <th style="width: 32%;">Substansi Pengaturan</th>
                <th style="width: 40%;">Implikasi Teknis di Aplikasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>UU No. 30 Tahun 2014</strong><br><span style="color:#64748b;">(Administrasi Pemerintahan)</span></td>
                <td>Mengatur perbedaan wewenang Atribusi, Delegasi, dan <strong>Mandat</strong> (Pasal 14). Pejabat penerima mandat bertindak atas nama pemberi mandat. Tanggung jawab dan tanggung gugat akhir tetap berada pada pemberi mandat.</td>
                <td>Bila Wakil Dekan menandatangani naskah atas penugasan Dekan, <strong>wajib</strong> mencantumkan formula mandat resmi <code>a.n. Dekan</code> di atas sebutan jabatannya.</td>
            </tr>
            <tr>
                <td><strong>PermenPAN-RB No. 1/2023 & Permendikbudristek No. 21/2024</strong></td>
                <td>Pedoman umum & operasional tata naskah dinas instansi pemerintah/PTN. Mengatur hierarki naskah, tata letak tanda tangan a.n., susunan vertikal jabatan, dan kedudukan paraf koordinasi.</td>
                <td>Format naskah dinas (SK KGB, Usulan KP, Satyalancana, Formulir Cuti) diseragamkan mengikuti format baku nasional, penataan spasi proporsional 1 lembar.</td>
            </tr>
            <tr>
                <td><strong>UU No. 11/2008 jo. UU No. 1/2024 (ITE) & PP No. 71/2019</strong></td>
                <td>Legalitas Dokumen Elektronik & Tanda Tangan Elektronik (TTE). Informasi elektronik diakui sebagai alat bukti hukum yang sah apabila memiliki otentikasi integritas data.</td>
                <td>Setiap naskah cetak dilengkapi <strong>Kotak Otentikasi Digital Resmi</strong> dengan kode verifikasi SHA-256 unik dan QR code validasi real-time.</td>
            </tr>
            <tr>
                <td><strong>PP 11/2017 jo. PP 17/2020 & Perbkn 24/2017 jo. 7/2021</strong></td>
                <td>Manajemen PNS & Tata Cara Pemberian Cuti PNS. Rektor bertindak selaku PyB/PPK; kewenangan cuti didelegasikan kepada pimpinan unit kerja setingkat eselon/fakultas.</td>
                <td>Kolom VII (Pertimbangan Atasan Langsung) dan Kolom VIII (Keputusan Pejabat yang Berwenang Memberikan Cuti) dipetakan otomatis sesuai hierarki jabatan.</td>
            </tr>
            <tr>
                <td><strong>PP No. 7/1977 jo. PP No. 5/2024</strong></td>
                <td>Peraturan Gaji Pokok PNS & Kenaikan Gaji Berkala (KGB). Pejabat penerbit surat pemberitahuan KGB adalah kepala unit pelaksana administrasi kepegawaian.</td>
                <td>Surat Pemberitahuan KGB diterbitkan oleh Dekan dan ditandatangani oleh Wakil Dekan Bidang Keuangan dan Umum atas mandat Dekan.</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- 2. MATRIKS WEWENANG PENANDATANGANAN NASKAH -->
    <div class="section-header">2. Matriks Wewenang & Tata Cara Penandatanganan Mandat</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25%;">Jenis Dokumen / Naskah</th>
                <th style="width: 20%;">Wewenang Asal</th>
                <th style="width: 25%;">Pelaksana Penandatangan</th>
                <th style="width: 30%;">Rumusan Tanda Tangan Resmi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>SK Kenaikan Gaji Berkala (KGB)</strong></td>
                <td>Dekan FKp UNRI</td>
                <td>Wakil Dekan Bidang Keuangan dan Umum (Mandat)</td>
                <td><code>a.n. Dekan Fakultas Keperawatan<br>Wakil Dekan Bidang Keuangan dan Umum</code></td>
            </tr>
            <tr>
                <td><strong>Surat Usulan Kenaikan Pangkat (KP)</strong></td>
                <td>Dekan FKp UNRI</td>
                <td>Wakil Dekan Bidang Keuangan dan Umum (Mandat)</td>
                <td><code>a.n. Dekan Fakultas Keperawatan<br>Wakil Dekan Bidang Keuangan dan Umum</code></td>
            </tr>
            <tr>
                <td><strong>Surat Pengantar Satyalancana Karya Satya</strong></td>
                <td>Dekan FKp UNRI</td>
                <td>Wakil Dekan Bidang Keuangan dan Umum (Mandat)</td>
                <td><code>a.n. Dekan Fakultas Keperawatan<br>Wakil Dekan Bidang Keuangan dan Umum</code></td>
            </tr>
            <tr>
                <td><strong>Formulir Permintaan & Pemberian Cuti</strong></td>
                <td>PyBMC (Rektor / Dekan)</td>
                <td>Wakil Dekan (Pelaksana Delegasi/Mandat Dekan)</td>
                <td>Kolom VIII PyBMC:<br><code>a.n. Dekan Fakultas Keperawatan<br>Wakil Dekan Bidang Keuangan dan Umum</code></td>
            </tr>
            <tr>
                <td><strong>Surat Keterangan Aktif Bekerja</strong></td>
                <td>Dekan FKp UNRI</td>
                <td>Dekan Fakultas Keperawatan (Definitif)</td>
                <td><code>Dekan Fakultas Keperawatan</code></td>
            </tr>
        </tbody>
    </table>

    <!-- 3. KEDUDUKAN & ALUR KOORDINASI PARAF DIGITAL -->
    <div class="section-header">3. Standar Operasional Prosedur (SOP) Paraf Digital Koordinasi</div>
    <p>
        Berdasarkan Permendikbudristek No. 21/2024 Bab IV, paraf koordinasi naskah dinas berfungsi sebagai jaminan keabsahan materiil dan formil bahwa konsep surat telah diperiksa secara seksama oleh pejabat struktural/fungsional di bawah pejabat penandatangan sebelum naskah ditandatangani.
    </p>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 15%;">Tahapan</th>
                <th style="width: 30%;">Aktor Pelaksana</th>
                <th style="width: 55%;">Uraian Tugas & Tanggung Jawab Digital</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;"><strong>Tahap 1</strong></td>
                <td><strong>Analis / Operator Kepegawaian</strong></td>
                <td>Menghimpun berkas, memvalidasi masa kerja, SKP 2 tahun terakhir, dan mengunggah draf konsep surat ke dalam sistem aplikasi SIMPEG.</td>
            </tr>
            <tr>
                <td style="text-align: center;"><strong>Tahap 2</strong></td>
                <td><strong>Kepala Bagian TU / Katim Tata Usaha</strong></td>
                <td>Melakukan telaah tata naskah (nomor naskah, susunan perihal, kebenaran data kepegawaian). Memberikan <strong>Paraf Koordinasi Digital</strong> sebagai wujud verifikasi persetujuan formil.</td>
            </tr>
            <tr>
                <td style="text-align: center;"><strong>Tahap 3</strong></td>
                <td><strong>Dekan / Wakil Dekan</strong></td>
                <td>Memeriksa catatan paraf verifikasi dari Katim TU, kemudian membubuhkan <strong>Tanda Tangan Digital / Pengesahan Elektronik</strong> resmi. Dokumen terkunci dan tidak dapat diubah kembali.</td>
            </tr>
        </tbody>
    </table>

    <!-- 4. PANDUAN PENGGUNAAN & EDUKASI -->
    <div class="section-header">4. Panduan Edukasi Bagi Pejabat & Pegawai</div>
    <div class="box-warning">
        <strong>Penting bagi Pejabat Penandatangan:</strong> Pastikan kotak paraf digital Katim TU telah terisi dan tercentang hijau sebelum menerbitkan dokumen resmi. Penandatanganan dokumen mandat wajib mencantumkan klausul <code>a.n. Dekan Fakultas Keperawatan</code> untuk menghindari cacat kewenangan formil berdasarkan Pasal 14 UU No. 30/2014.
    </div>

    <table style="width: 100% !important; border-collapse: collapse !important; margin-top: 10px;">
        <tr>
            <td style="width: 48%; vertical-align: top; border: 1px solid #cbd5e1; padding: 8px; font-size: 8.5pt; background-color: #f8fafc;">
                <strong style="color: #1e3a8a; font-size: 9.5pt;">Pedoman Bagi Pejabat Penandatangan / Pemeriksa:</strong>
                <ul style="margin: 4px 0 0 16px; padding: 0;">
                    <li>Teliti riwayat masa kerja, jabatan, dan golongan ruang pemohon sebelum menandatangani.</li>
                    <li>Sistem secara otomatis mengunci naskah setelah ditandatangani guna mencegah manipulasi data.</li>
                    <li>QR Code otentikasi pada fisik naskah dinas menghubungkan langsung ke basis data validasi kampus.</li>
                </ul>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top; border: 1px solid #cbd5e1; padding: 8px; font-size: 8.5pt; background-color: #f8fafc;">
                <strong style="color: #166534; font-size: 9.5pt;">Pedoman Bagi Pegawai / Pemohon:</strong>
                <ul style="margin: 4px 0 0 16px; padding: 0;">
                    <li>Setiap naskah digital yang diunduh dari SIMPEG adalah dokumen elektronik sah sesuai UU ITE.</li>
                    <li>Cetak dokumen pada kertas ukuran A4 dengan orientasi Portrait dan skala 100% (tidak melebihi 1 halaman).</li>
                    <li>Pihak ketiga atau verifikator eksternal dapat memindai QR Code untuk membuktikan keaslian dokumen.</li>
                </ul>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis oleh Sistem Informasi Kepegawaian (SIMPEG) Fakultas Keperawatan Universitas Riau.<br>
        Dicetak pada: {{ date('d F Y H:i:s') }} WIB | ID Autentikasi: REG-FKP-{{ strtoupper(substr(md5(time()), 0, 8)) }}
    </div>

</body>
</html>
