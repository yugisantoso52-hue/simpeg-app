<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Sidang Komite Talenta ASN - Fakultas Keperawatan UNRI</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 20mm 15mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 0;
            font-size: 13pt;
            font-weight: normal;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h1 {
            margin: 2px 0;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 9pt;
            font-style: italic;
        }
        .title {
            text-align: center;
            margin-bottom: 18px;
        }
        .title h4 {
            margin: 0;
            font-size: 12pt;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title p {
            margin: 3px 0 0;
            font-size: 10pt;
        }
        .content {
            text-align: justify;
            margin-bottom: 15px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 20px;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 5px 7px;
        }
        table.data-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
            font-size: 10pt;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 10px 15px;
        }
        .no-print {
            text-align: right;
            margin-bottom: 15px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
        .btn-print {
            background-color: #059669;
            color: #fff;
            border: none;
            padding: 8px 16px;
            font-size: 10pt;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Cetak Berita Acara (PDF / Print)</button>
    </div>

    {{-- Kop Surat Resmi --}}
    <div class="header">
        <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
        <h2>UNIVERSITAS RIAU</h2>
        <h1>FAKULTAS KEPERAWATAN</h1>
        <p>Kampus Bina Widya Km. 12,5 Simpang Baru, Pekanbaru 28293 &bull; Laman: https://fkp.unri.ac.id</p>
    </div>

    {{-- Judul Berita Acara --}}
    <div class="title">
        <h4>BERITA ACARA SIDANG KOMITE MANAJEMEN TALENTA ASN</h4>
        <p>Nomor: B/{{ date('m') }}/UN19.5.1.1/KP.08.01/{{ $tahun }}</p>
    </div>

    {{-- Isi / Pembukaan --}}
    <div class="content">
        <p>
            Pada hari ini, <strong>{{ \Carbon\Carbon::now()->translatedFormat('l') }}</strong> tanggal <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</strong> bertempat di Ruang Rapat Pimpinan Fakultas Keperawatan Universitas Riau, telah diselenggarakan <strong>Sidang Pleno Komite Manajemen Talenta ASN</strong> guna melaksanakan evaluasi, penelaahan kelayakan, dan penetapan resmi Pegawai Aparatur Sipil Negara ke dalam <em>Talent Pool</em> serta Rencana Suksesi Jabatan Tahun {{ $tahun }}, berdasarkan amanat PermenPAN-RB No. 3 Tahun 2020 dan Undang-Undang No. 20 Tahun 2023.
        </p>

        <p>
            Berdasarkan hasil pemetaan objektif matriks 9-kotak (kinerja dan potensi), rekam jejak asesmen kompetensi BKN, serta pertimbangan kualifikasi dan integritas, Komite Manajemen Talenta ASN dengan ini menetapkan daftar pegawai berikut sebagai <strong>Anggota Talent Pool Resmi</strong> Fakultas Keperawatan Universitas Riau:
        </p>
    </div>

    {{-- Tabel Penetapan Talent Pool --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Nama Pegawai &amp; NIP</th>
                <th>Pangkat / Gol.</th>
                <th>Jabatan Saat Ini</th>
                <th>Kuadran Talenta</th>
                <th>Skor Kinerja</th>
                <th>Skor Potensi</th>
                <th>Status Penetapan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($talentPool as $idx => $t)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $t->pegawai?->nama_lengkap ?? $t->pegawai?->nama ?? '-' }}</strong><br>
                        <span style="font-size: 8pt; color: #333;">NIP. {{ $t->pegawai?->nip ?? '-' }}</span>
                    </td>
                    <td style="text-align: center;">{{ $t->pegawai?->golongan?->kode ?? $t->pegawai?->golongan?->nama_golongan ?? '-' }}</td>
                    <td>{{ $t->pegawai?->jabatan?->nama_jabatan ?? '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">Kotak {{ $t->kuadran_box }}</td>
                    <td style="text-align: center;">{{ $t->sumbu_kinerja_nilai }}</td>
                    <td style="text-align: center;">{{ $t->sumbu_potensi_nilai }}</td>
                    <td style="text-align: center; font-weight: bold;">DITETAPKAN</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Belum ada data kandidat talent pool yang ditetapkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Penutup Berita Acara --}}
    <div class="content">
        <p>
            Demikian Berita Acara ini dibuat dan ditandatangani dengan sesungguhnya oleh seluruh Dewan Komite Manajemen Talenta ASN Fakultas Keperawatan Universitas Riau untuk dipergunakan sebagai dasar pertimbangan penugasan, suksesi jabatan, dan pembinaan karir pegawai selanjutnya.
        </p>
    </div>

    {{-- Lembar Pengesahan Dewan Komite --}}
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui &amp; Mengesahkan,<br>
                <strong>Ketua Komite Talenta / Dekan,</strong>
                <br><br><br><br><br>
                <u><strong>Prof. Dr. Ir. H. Pimpinan, M.Kes</strong></u><br>
                NIP. 196801011993031001
            </td>
            <td>
                Pekanbaru, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                <strong>Sekretaris Komite / Wadek II,</strong>
                <br><br><br><br><br>
                <u><strong>Wakil Dekan Bidang Umum &amp; Keuangan</strong></u><br>
                NIP. 197405102001122001
            </td>
        </tr>
        <tr>
            <td style="padding-top: 25px;">
                Anggota Komite,<br>
                <strong>Wakil Dekan I (Bidang Akademik),</strong>
                <br><br><br><br><br>
                <u><strong>Wakil Dekan Bidang Akademik</strong></u><br>
                NIP. 197203151998021001
            </td>
            <td style="padding-top: 25px;">
                Anggota Komite,<br>
                <strong>Wakil Dekan III (Bid. Kemahasiswaan),</strong>
                <br><br><br><br><br>
                <u><strong>Wakil Dekan Bidang Kemahasiswaan</strong></u><br>
                NIP. 197908222005012002
            </td>
        </tr>
    </table>
    
    {{-- OTENTIKASI DIGITAL SIKAP FKP UNRI (DOKUMEN RESMI ELEKTRONIK DENGAN QR CODE) --}}
    @include('exports.pdf.partials.otentikasi_dokumen')

</body>
</html>
