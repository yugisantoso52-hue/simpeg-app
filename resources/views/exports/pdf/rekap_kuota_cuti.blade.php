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
        <h3>REKAPITULASI REALISASI & KUOTA CUTI PEGAWAI</h3>
        <h4>FAKULTAS KEPERAWATAN UNIVERSITAS RIAU — TAHUN ANGGARAN {{ $tahun }}</h4>
        <div style="font-size: 8pt; color: #444; margin-top: 2px;">
            Dasar Hukum: Peraturan Badan Kepegawaian Negara Republik Indonesia Nomor 7 Tahun 2022
            @if($unitKerja) | Unit Kerja: {{ $unitKerja->nama_unit }} @endif
            @if($jenisPegawai) | Kategori: {{ $jenisPegawai }} @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">NO</th>
                <th rowspan="2" style="width: 17%;">NAMA PEGAWAI & NIP</th>
                <th rowspan="2" style="width: 16%;">JABATAN & UNIT KERJA</th>
                <th colspan="9" style="background-color: #e6edf5;">JENIS CUTI (PERATURAN BKN NO. 7 TAHUN 2022)</th>
                <th rowspan="2" style="width: 16%;">TGL PENGAJUAN</th>
            </tr>
            <tr>
                <th style="width: 5%;">{{ $tahun - 2 }}<br>(N-2)</th>
                <th style="width: 5%;">{{ $tahun - 1 }}<br>(N-1)</th>
                <th style="width: 5%;">{{ $tahun }}<br>(N)</th>
                <th style="width: 5%; background-color: #e0f2fe; font-weight: bold;">SISA</th>
                <th style="width: 5%;">BESAR</th>
                <th style="width: 5%;">SAKIT</th>
                <th style="width: 6%;">LAHIR</th>
                <th style="width: 4%;">CAP</th>
                <th style="width: 4%;">CLTN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawaiList as $p)
                @php
                    $rekap = $p->getCutiRekapBkn($tahun);
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $p->nama_lengkap ?? $p->nama }}</strong><br>
                        <span style="font-size: 7.5pt; color: #444;">NIP. {{ $p->nip ?? '-' }}</span>
                    </td>
                    <td style="font-size: 7.5pt;">
                        {{ $p->jabatan_tampil }}<br>
                        <span style="color: #555;">{{ $p->unit_kerja_tampil }}</span>
                    </td>
                    <td class="text-center">{{ $rekap['n2'] ?? '-' }}</td>
                    <td class="text-center">{{ $rekap['n1'] ?? '-' }}</td>
                    <td class="text-center font-bold">{{ $rekap['n_terpakai'] ?? '-' }}</td>
                    <td class="text-center font-bold" style="background-color: #f0f9ff;">{{ $rekap['sisa'] }}</td>
                    <td class="text-center">{{ $rekap['cuti_besar'] ?? '-' }}</td>
                    <td class="text-center">{{ $rekap['cuti_sakit'] ?? '-' }}</td>
                    <td class="text-center font-bold">{{ $rekap['cuti_melahirkan'] ? $rekap['cuti_melahirkan'] . ' Bln' : '-' }}</td>
                    <td class="text-center">{{ $rekap['cuti_cap'] ?? '-' }}</td>
                    <td class="text-center">{{ $rekap['cuti_cltn'] ?? '-' }}</td>
                    <td style="font-size: 7.5pt;">{{ $rekap['tgl_pengajuan'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center">Tidak ada data pegawai.</td>
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
