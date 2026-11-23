<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Kuota Cuti Pegawai Tahun {{ $tahun }}</title>
    <style>
        @page {
            margin: 10mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
        }
        .header h3 {
            margin: 0;
            font-size: 11pt;
            text-transform: uppercase;
        }
        .header h4 {
            margin: 2px 0;
            font-size: 9.5pt;
            font-weight: normal;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        th, td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }
        th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h3>REKAPITULASI KUOTA CUTI TAHUNAN PEGAWAI (N, N-1, N-2)</h3>
        <h4>FAKULTAS KEPERAWATAN UNIVERSITAS RIAU — TAHUN ANGGARAN {{ $tahun }}</h4>
        <div style="font-size: 8pt; color: #444; margin-top: 2px;">
            Dasar Hukum: Peraturan Badan Kepegawaian Negara Republik Indonesia Nomor 24 Tahun 2017
            @if($unitKerja) | Unit Kerja: {{ $unitKerja->nama_unit }} @endif
            @if($jenisPegawai) | Kategori: {{ $jenisPegawai }} @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">NO</th>
                <th style="width: 20%;">NAMA PEGAWAI</th>
                <th style="width: 15%;">NIP</th>
                <th style="width: 22%;">JABATAN & UNIT KERJA</th>
                <th style="width: 9%;">SISA {{ $tahun - 2 }}<br>(N-2)</th>
                <th style="width: 9%;">SISA {{ $tahun - 1 }}<br>(N-1)</th>
                <th style="width: 9%;">SISA {{ $tahun }}<br>(N)</th>
                <th style="width: 12%;">TOTAL SISA AKTIF</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawaiList as $p)
                @php
                    $catatan = $p->getCatatanCutiTahunan($tahun);
                    $sisaN   = $catatan['N']['sisa'] ?? 12;
                    $sisaN1  = $catatan['N-1']['sisa'];
                    $sisaN2  = $catatan['N-2']['sisa'];
                    $totalAktif = $sisaN + ($sisaN1 ?? 0) + ($sisaN2 ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $p->nama_lengkap ?? $p->nama }}</td>
                    <td class="text-center">{{ $p->nip ?? '-' }}</td>
                    <td>{{ $p->jabatan?->nama_jabatan ?? '-' }} - {{ $p->unitKerja?->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ $catatan['N-2']['sisa_formatted'] }}</td>
                    <td class="text-center">{{ $catatan['N-1']['sisa_formatted'] }}</td>
                    <td class="text-center font-bold">{{ $catatan['N']['sisa_formatted'] }}</td>
                    <td class="text-center font-bold">{{ $totalAktif }} Hari</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data pegawai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table style="border: none; margin-top: 20px;">
        <tr style="border: none;">
            <td style="border: none; width: 65%;"></td>
            <td style="border: none; width: 35%; text-align: center;">
                Pekanbaru, {{ now()->translatedFormat('d F Y') }}<br>
                Kepala Bagian Umum,<br><br><br><br>
                <strong>Bakhtiar, S.Sos., M.Si</strong><br>
                NIP. 197507122005011002
            </td>
        </tr>
    </table>

</body>
</html>
