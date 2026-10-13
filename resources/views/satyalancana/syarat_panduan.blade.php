<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Petunjuk Teknis & Persyaratan Pengusulan Satyalancana Karya Satya - FKP UNRI</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 16mm 12mm 16mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }
        .kop-logo {
            width: 25mm;
            text-align: left;
        }
        .kop-logo img {
            width: 24mm;
            height: 24mm;
        }
        .kop-text {
            text-align: center;
            padding-right: 25mm;
        }
        .kop-instansi {
            font-size: 13pt;
            font-weight: normal;
            text-transform: uppercase;
        }
        .kop-univ {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-fakultas {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-alamat {
            font-size: 8.5pt;
            margin-top: 2px;
        }
        .title-box {
            text-align: center;
            margin: 10px 0 16px 0;
        }
        .title-box h1 {
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 0;
        }
        .title-box p {
            font-size: 9.5pt;
            font-weight: bold;
            margin: 3px 0 0 0;
        }
        .section-h {
            font-weight: bold;
            font-size: 10.5pt;
            margin-top: 10px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        ol, ul {
            margin: 3px 0 8px 18px;
            padding: 0;
        }
        li {
            margin-bottom: 3.5px;
            text-align: justify;
        }
        table.syarat-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
            font-size: 9pt;
        }
        table.syarat-table th, table.syarat-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        table.syarat-table th {
            background-color: #f1f5f9;
            text-align: center;
            font-weight: bold;
        }
        .notice-box {
            background-color: #f8fafc;
            border-left: 3px solid #0284c7;
            padding: 6px 10px;
            font-size: 8.5pt;
            margin-top: 12px;
            line-height: 1.3;
        }
    </style>
</head>
<body>

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

    <table class="header-table">
        <tr>
            <td class="kop-logo">
                @if($foundLogo)
                    @php
                        $mimeType = str_ends_with(strtolower($foundLogo), '.jpg') || str_ends_with(strtolower($foundLogo), '.jpeg') ? 'image/jpeg' : 'image/png';
                    @endphp
                    <img src="data:{{ $mimeType }};base64,{{ base64_encode(file_get_contents($foundLogo)) }}" alt="Logo UNRI">
                @endif
            </td>
            <td class="kop-text">
                <div class="kop-instansi">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div class="kop-univ">UNIVERSITAS RIAU</div>
                <div class="kop-fakultas">FAKULTAS KEPERAWATAN</div>
                <div class="kop-alamat">
                    Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru 28293<br>
                    Laman : www.keperawatan.unri.ac.id | Email : keperawatan@unri.co.id
                </div>
            </td>
        </tr>
    </table>

    <div class="title-box">
        <h1>PERSYARATAN & PETUNJUK TEKNIS PENGUSULAN</h1>
        <p>TANDA KEHORMATAN SATYALANCANA KARYA SATYA (X, XX, XXX TAHUN)</p>
    </div>

    <div class="section-h">A. DASAR HUKUM REGULASI RESMI</div>
    <ol>
        <li><strong>Undang-Undang Republik Indonesia Nomor 20 Tahun 2009</strong> tentang Gelar, Tanda Jasa, dan Tanda Kehormatan.</li>
        <li><strong>Peraturan Pemerintah Republik Indonesia Nomor 35 Tahun 2010</strong> tentang Pelaksanaan UU No. 20 Tahun 2009.</li>
        <li><strong>Peraturan Menteri Pendidikan, Kebudayaan, Riset, dan Teknologi / Kemendiktisaintek</strong> tentang Tata Naskah dan Pedoman Penganugerahan Tanda Kehormatan.</li>
        <li><strong>Surat Edaran BKN dan Sekretariat Militer Presiden (Setmilpres)</strong> perihal Tata Cara Pengusulan Penganugerahan Tanda Kehormatan Satyalancana Karya Satya.</li>
    </ol>

    <div class="section-h">B. SYARAT UMUM & KRITERIA KELAYAKAN</div>
    <ul>
        <li>Pegawai Negeri Sipil (PNS) yang telah bekerja terus-menerus sekurang-kurangnya:
            <ul>
                <li><strong>10 (Sepuluh) Tahun</strong> untuk Satyalancana Karya Satya X Tahun (Perunggu).</li>
                <li><strong>20 (Dua Puluh) Tahun</strong> untuk Satyalancana Karya Satya XX Tahun (Perak).</li>
                <li><strong>30 (Tiga Puluh) Tahun</strong> untuk Satyalancana Karya Satya XXX Tahun (Emas).</li>
            </ul>
        </li>
        <li>Menunjukkan kesetiaan, pengabdian, kecakapan, kejujuran, dan kedisiplinan tinggi dalam melaksanakan tugas kedinasan.</li>
        <li><strong>Tidak pernah dijatuhi hukuman disiplin tingkat sedang maupun berat</strong> berdasarkan ketentuan peraturan perundang-undangan mengenai disiplin PNS.</li>
        <li><strong>Tidak pernah mengambil Cuti di Luar Tanggungan Negara (CLTN)</strong>, atau jika pernah, masa CLTN tidak diperhitungkan sebagai masa kerja pengabdian.</li>
        <li>Penilaian Kinerja Pegawai (SKP) sekurang-kurangnya bernilai <strong>"BAIK"</strong> dalam 2 (dua) tahun terakhir berturut-turut.</li>
    </ul>

    <div class="section-h">C. DAFTAR KELENGKAPAN BERKAS ADMINISTRATIF (DOKUMEN PERSYARATAN)</div>
    <table class="syarat-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 34%;">Nama Berkas / Dokumen</th>
                <th style="width: 40%;">Keterangan & Sumber Data di SIKAP</th>
                <th style="width: 20%;">Format Berkas</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1</td>
                <td><strong>Daftar Riwayat Hidup (DRH) Resmi</strong></td>
                <td>Format standar 12 Poin Setmilpres/BKN ber-TTE dan paraf digital koordinasi tata naskah.</td>
                <td style="text-align: center;">PDF (Cetak SIKAP)</td>
            </tr>
            <tr>
                <td style="text-align: center;">2</td>
                <td><strong>Fotokopi / Scan SK CPNS</strong></td>
                <td>SK Pengangkatan Pertama CPNS (tersimpan pada Riwayat SK Pertama di profil pegawai).</td>
                <td style="text-align: center;">PDF Scan Asli</td>
            </tr>
            <tr>
                <td style="text-align: center;">3</td>
                <td><strong>Scan SK Pangkat Terakhir</strong></td>
                <td>SK Kenaikan Pangkat (KP) periode terakhir yang telah dilegalisir / e-Sign BKN.</td>
                <td style="text-align: center;">PDF Scan Asli</td>
            </tr>
            <tr>
                <td style="text-align: center;">4</td>
                <td><strong>Scan SK Jabatan Terakhir</strong></td>
                <td>SK Pengangkatan Jabatan Fungsional Dosen / PLP / Tenaga Kependidikan terakhir.</td>
                <td style="text-align: center;">PDF Scan Asli</td>
            </tr>
            <tr>
                <td style="text-align: center;">5</td>
                <td><strong>Penilaian SKP 2 Tahun Terakhir</strong></td>
                <td>Dokumen SKP tahun N-1 dan N-2 dengan predikat kinerja minimal "BAIK".</td>
                <td style="text-align: center;">PDF e-Kinerja BKN</td>
            </tr>
            <tr>
                <td style="text-align: center;">6</td>
                <td><strong>Surat Pernyataan Tidak Pernah Dijatuhi Hukdis</strong></td>
                <td>Surat Keterangan resmi bertandatangan Dekan/Pimpinan menyatakan bebas hukuman disiplin.</td>
                <td style="text-align: center;">PDF Resmi</td>
            </tr>
            <tr>
                <td style="text-align: center;">7</td>
                <td><strong>Scan Piagam Satyalancana Sebelumnya</strong></td>
                <td>Khusus bagi yang mengusulkan kategori 20 Tahun (lampirkan piagam 10 thn) atau 30 Tahun (lampirkan piagam 20 thn).</td>
                <td style="text-align: center;">PDF Piagam/SK</td>
            </tr>
        </tbody>
    </table>

    <div class="notice-box">
        <strong>CATATAN PENTING SISTEM INFORMASI KEPEGAWAIAN (SIKAP FKP UNRI):</strong><br>
        Seluruh berkas persyaratan yang tersimpan di sistem SIKAP (Menu Kepegawaian, Riwayat Pangkat, Riwayat Jabatan, dan Arsip Digital Pegawai) dapat diunduh secara mandiri oleh pegawai bersangkutan atau dihimpun langsung secara kolektif oleh Admin Kepegawaian untuk diteruskan ke Biro Umum, Perencanaan, dan Kepegawaian (BUPK) Rektorat Universitas Riau.
    </div>

</body>
</html>
