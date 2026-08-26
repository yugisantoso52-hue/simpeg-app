<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Formulir Permintaan dan Pemberian Cuti - {{ $cuti->pegawai->nama ?? 'Pegawai' }}</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.25;
        }
        .header-kop {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .header-kop h3 { margin: 0; font-size: 9pt; font-weight: normal; text-transform: uppercase; }
        .header-kop h2 { margin: 1px 0; font-size: 11pt; font-weight: bold; text-transform: uppercase; }
        .header-kop h1 { margin: 1px 0; font-size: 12pt; font-weight: bold; text-transform: uppercase; }
        .header-kop p { margin: 1px 0; font-size: 8pt; }

        .title {
            text-align: center;
            font-weight: bold;
            font-size: 11pt;
            margin: 6px 0 10px 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        table.bkn-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        table.bkn-table, table.bkn-table th, table.bkn-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }
        .section-title {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9pt;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .checkbox-mark { font-family: DejaVu Sans, sans-serif; font-size: 10pt; font-weight: bold; }
        
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            margin-top: 4px;
        }
        .sign-table td {
            border: none;
            padding: 2px 4px;
            vertical-align: top;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="header-kop">
        <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
        <h2>UNIVERSITAS RIAU</h2>
        <h1>FAKULTAS KEPERAWATAN</h1>
        <p>Kampus Bina Widya Gedung Health Studies Complex Km. 12,5 Simpang Baru Pekanbaru 28293</p>
        <p>Laman: http://keperawatan.unri.ac.id | Email: keperawatan@unri.ac.id</p>
    </div>

    <div class="title">FORMULIR PERMINTAAN DAN PEMBERIAN CUTI</div>
    <div style="text-align: right; font-size: 8.5pt; margin-bottom: 6px;">
        Pekanbaru, {{ $cuti->created_at->translatedFormat('d F Y') }}<br>
        Kepada Yth. Dekan Fakultas Keperawatan UNRI<br>
        di Pekanbaru
    </div>

    {{-- I. DATA PEGAWAI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="4">I. DATA PEGAWAI</td>
        </tr>
        <tr>
            <td style="width: 18%;">Nama</td>
            <td style="width: 32%;" class="text-bold">{{ $cuti->pegawai->nama_lengkap ?? $cuti->pegawai->nama }}</td>
            <td style="width: 18%;">NIP</td>
            <td style="width: 32%;">{{ $cuti->pegawai->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>{{ $cuti->pegawai->jabatan->nama_jabatan ?? '-' }}</td>
            <td>Masa Kerja Golongan</td>
            <td>{{ $cuti->pegawai->mkg_tahun ?? 0 }} Thn {{ $cuti->pegawai->mkg_bulan ?? 0 }} Bln</td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>{{ $cuti->pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}</td>
            <td>Golongan / Pangkat</td>
            <td>{{ $cuti->pegawai->golongan->nama_golongan ?? '-' }} ({{ $cuti->pegawai->golongan->nama_pangkat ?? '-' }})</td>
        </tr>
    </table>

    {{-- II. JENIS CUTI YANG DIAMBIL --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="4">II. JENIS CUTI YANG DIAMBIL</td>
        </tr>
        <tr>
            <td style="width: 40%;">1. Cuti Tahunan</td>
            <td style="width: 10%;" class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Tahunan' ? '✓' : '' }}</td>
            <td style="width: 40%;">2. Cuti Besar</td>
            <td style="width: 10%;" class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Besar' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>3. Cuti Sakit</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Sakit' ? '✓' : '' }}</td>
            <td>4. Cuti Melahirkan</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Melahirkan' ? '✓' : '' }}</td>
        </tr>
        <tr>
            <td>5. Cuti Karena Alasan Penting</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti Alasan Penting' ? '✓' : '' }}</td>
            <td>6. Cuti di Luar Tanggungan Negara</td>
            <td class="text-center checkbox-mark">{{ $cuti->jenis_cuti === 'Cuti di Luar Tanggungan Negara' ? '✓' : '' }}</td>
        </tr>
    </table>

    {{-- III. ALASAN CUTI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td>III. ALASAN CUTI</td>
        </tr>
        <tr>
            <td style="min-height: 28px;">{{ $cuti->alasan }}</td>
        </tr>
    </table>

    {{-- IV. LAMANYA CUTI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="6">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td style="width: 15%;">Selama</td>
            <td style="width: 20%;" class="text-bold">{{ $cuti->jumlah_hari }} Hari Kerja</td>
            <td style="width: 15%;">Mulai Tanggal</td>
            <td style="width: 20%;">{{ $cuti->tanggal_mulai ? $cuti->tanggal_mulai->translatedFormat('d/m/Y') : '-' }}</td>
            <td style="width: 12%;">s.d.</td>
            <td style="width: 18%;">{{ $cuti->tanggal_selesai ? $cuti->tanggal_selesai->translatedFormat('d/m/Y') : '-' }}</td>
        </tr>
    </table>

    {{-- V. CATATAN CUTI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="5">V. CATATAN CUTI TAHUNAN</td>
        </tr>
        <tr class="text-center" style="font-weight: bold; background-color: #fafafa;">
            <td>Tahun</td>
            <td>Sisa Kuota</td>
            <td>Keterangan</td>
            <td colspan="2">Alamat Selama Menjalankan Cuti</td>
        </tr>
        <tr>
            <td class="text-center">{{ now()->year }}</td>
            <td class="text-center text-bold">{{ $cuti->pegawai->sisa_cuti_tahunan ?? 12 }} Hari</td>
            <td>Hak Cuti Tahun Berjalan</td>
            <td colspan="2" rowspan="2">
                {{ $cuti->alamat_selama_cuti ?: '-' }}<br>
                <small>No. Telp/WA: {{ $cuti->nomor_telepon ?: '-' }}</small>
            </td>
        </tr>
        <tr>
            <td class="text-center">{{ now()->year - 1 }}</td>
            <td class="text-center">-</td>
            <td>Tahun N-1</td>
        </tr>
    </table>

    {{-- VI. PERTIMBANGAN ATASAN LANGSUNG & PEJABAT YANG BERWENANG --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td style="width: 50%;">VI. PERTIMBANGAN ATASAN LANGSUNG</td>
            <td style="width: 50%;">VII. KEPUTUSAN PEJABAT YANG BERWENANG</td>
        </tr>
        <tr>
            <td>
                <div>Status: <strong>{{ $cuti->status }}</strong></div>
                <div style="margin-top: 2px; font-size: 8.5pt;">Catatan: {{ $cuti->catatan_pimpinan ?: '-' }}</div>
                <br><br>
                <div class="text-center">
                    <strong><u>( KTU / Wakil Dekan II )</u></strong><br>
                    NIP. .....................................................
                </div>
            </td>
            <td>
                <div>Status: <strong>{{ $cuti->status }}</strong></div>
                <div style="margin-top: 2px; font-size: 8.5pt;">No. SK/Izin: {{ $cuti->nomor_surat ?: '-' }}</div>
                <br><br>
                <div class="text-center">
                    <strong><u>Prof. Dr. Dosen Dekan, M.Kep</u></strong><br>
                    NIP. .....................................................
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
