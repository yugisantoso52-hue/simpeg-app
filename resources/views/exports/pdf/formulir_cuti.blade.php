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
        .header-kop-table {
            width: 100%;
            border-collapse: collapse;
            /* Garis tebal tunggal sesuai Permendikti 42/2025 */
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        .header-kop-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }
        .header-kop-logo {
            width: 76px;
            text-align: left;
        }
        .header-kop-logo img {
            width: 70px;
            height: 70px;
            display: block;
        }
        .header-kop-text {
            text-align: center;
            padding-right: 15px;
        }
        /* Baris 1: KEMENTERIAN - Times New Roman regular */
        .header-kop-text .kop-kemdikti {
            margin: 0;
            font-size: 11pt;
            font-weight: normal;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
        }
        /* Baris 2: NAMA PTN - Times New Roman bold */
        .header-kop-text .kop-ptn {
            margin: 1px 0;
            font-size: 10.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
        }
        /* Baris 3: NAMA FAKULTAS - Times New Roman bold */
        .header-kop-text .kop-fakultas {
            margin: 1px 0;
            font-size: 10.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
        }
        /* Baris 4: Alamat & Kontak */
        .header-kop-text .kop-alamat {
            margin: 2px 0 0 0;
            font-size: 7.5pt;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.2;
        }

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

    {{-- KOP SURAT BERLOGO HITAM PUTIH --}}
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

    <table class="header-kop-table">
        <tr>
            <td class="header-kop-logo">
                @if($foundLogo)
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($foundLogo)) }}" alt="Logo UNRI">
                @else
                    <div style="font-size: 8pt; color: #666; text-align: center;">[LOGO UNRI]</div>
                @endif
            </td>
            <td class="header-kop-text">
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
            <td>{{ $cuti->alasan ?: '-' }}</td>
        </tr>
    </table>

    {{-- IV. LAMANYA CUTI --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="6">IV. LAMANYA CUTI</td>
        </tr>
        <tr>
            <td style="width: 15%;">Selama</td>
            <td style="width: 25%;" class="text-bold">{{ $cuti->jumlah_hari }} Hari Kerja</td>
            <td style="width: 18%;">Mulai Tanggal</td>
            <td style="width: 18%;">{{ $cuti->tanggal_mulai ? $cuti->tanggal_mulai->format('d/m/Y') : '-' }}</td>
            <td style="width: 6%;" class="text-center">s.d.</td>
            <td style="width: 18%;">{{ $cuti->tanggal_selesai ? $cuti->tanggal_selesai->format('d/m/Y') : '-' }}</td>
        </tr>
    </table>

    {{-- V. CATATAN CUTI TAHUNAN & ALAMAT --}}
    <table class="bkn-table">
        <tr class="section-title">
            <td colspan="5">V. CATATAN CUTI TAHUNAN</td>
        </tr>
        <tr class="section-title" style="font-size: 8pt;">
            <td style="width: 12%; text-align: center;">Tahun</td>
            <td style="width: 15%; text-align: center;">Sisa Kuota</td>
            <td style="width: 28%;">Keterangan</td>
            <td colspan="2" style="width: 45%;">Alamat Selama Menjalankan Cuti</td>
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
                    <span style="font-weight: normal; text-decoration: none;">KTU / Wakil Dekan II</span><br>
                    NIP. .....................................................
                </div>
            </td>
            <td>
                <div>Status: <strong>{{ $cuti->status }}</strong></div>
                <div style="margin-top: 2px; font-size: 8.5pt;">No. SK/Izin: {{ $cuti->nomor_surat ?: '-' }}</div>
                <br><br>
                <div class="text-center">
                    <span style="font-weight: normal; text-decoration: none;">Prof. Dr. Dosen Dekan, M.Kep</span><br>
                    NIP. .....................................................
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
