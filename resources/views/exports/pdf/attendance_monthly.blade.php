<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Matriks Presensi Bulanan Pegawai</title>
    <style>
        @page {
            margin: 8mm 6mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 7px;
            color: #1e293b;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .header h3 {
            margin: 0;
            font-size: 8px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 1px 0;
            font-size: 10px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header h1 {
            margin: 1px 0 0;
            font-size: 12px;
            font-weight: 900;
            color: #007a3d;
            text-transform: uppercase;
        }
        .header p {
            margin: 1px 0 0;
            font-size: 7.5px;
            color: #64748b;
        }
        .title-block {
            text-align: center;
            margin-bottom: 8px;
        }
        .title-block h4 {
            margin: 0;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e3a8a;
        }
        .title-block .subtitle {
            font-size: 8px;
            color: #475569;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 3px 2px;
            vertical-align: middle;
        }
        th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            font-size: 6.5px;
            text-align: center;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .day-header {
            font-size: 6px;
            width: 15px;
            text-align: center;
            padding: 2px 0;
        }
        .day-cell {
            text-align: center;
            font-size: 6.5px;
            font-weight: bold;
            padding: 2px 0;
        }
        .cell-present { color: #16a34a; }
        .cell-late { color: #d97706; background-color: #fef3c7; }
        .cell-absent { color: #dc2626; background-color: #fee2e2; }
        .cell-weekend { color: #94a3b8; background-color: #f1f5f9; }
        .cell-future { color: #cbd5e1; }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }

        .legend {
            margin-top: 6px;
            padding: 4px 6px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            font-size: 7px;
        }
        .legend span {
            margin-right: 12px;
            font-weight: 600;
        }

        .signature-table {
            width: 100%;
            margin-top: 15px;
            border: none;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
            font-size: 8px;
        }
    </style>
</head>
<body>

    <!-- KOP INSTANSI RESMI UNRI -->
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
    <table style="width: 100%; border-collapse: collapse; border-bottom: 2.5px solid #000; margin-bottom: 12px; padding-bottom: 4px;">
        <tr>
            <td style="width: 28mm; vertical-align: middle;">
                @if($foundLogo)
                    @php
                        $mimeType = str_ends_with(strtolower($foundLogo), '.jpg') || str_ends_with(strtolower($foundLogo), '.jpeg') ? 'image/jpeg' : 'image/png';
                    @endphp
                    <img src="data:{{ $mimeType }};base64,{{ base64_encode(file_get_contents($foundLogo)) }}" style="width: 25mm; height: 25mm; display: block;" alt="Logo UNRI">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-left: 5px; padding-right: 28mm; font-family: 'Times New Roman', Times, serif;">
                <div style="font-size: 13pt; text-transform: uppercase; line-height: 1.15;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; line-height: 1.15; margin-top: 1px;">UNIVERSITAS RIAU</div>
                <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase; line-height: 1.15; margin-top: 1px;">FAKULTAS KEPERAWATAN</div>
                <div style="font-size: 8.5pt; line-height: 1.25; margin-top: 3px;">
                    Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru 28293<br>
                    Laman : www.keperawatan.unri.ac.id &nbsp;&nbsp;|&nbsp;&nbsp; Email : keperawatan@unri.co.id
                </div>
            </td>
        </tr>
    </table>

    <!-- JUDUL LAPORAN -->
    <div class="title-block">
        <h4>REKAPITULASI MATRIKS PRESENSI BULANAN PEGAWAI</h4>
        <div class="subtitle">
            Periode: <strong>{{ $matrixData['month_name'] }}</strong> | Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    <!-- TABEL MATRIKS BULANAN -->
    <table>
        <thead>
            <tr>
                <th width="2%" rowspan="2">NO</th>
                <th width="15%" rowspan="2">NAMA PEGAWAI / NIP</th>
                <th width="11%" rowspan="2">JABATAN & UNIT</th>
                <th colspan="{{ $matrixData['days_in_month'] }}">TANGGAL ({{ $matrixData['month_name'] }})</th>
                <th width="4%" rowspan="2">HADIR</th>
                <th width="4%" rowspan="2">TELAT</th>
                <th width="4%" rowspan="2">PSW</th>
                <th width="8%" rowspan="2">JAM KERJA</th>
                <th width="7%" rowspan="2">SANKSI</th>
            </tr>
            <tr>
                @foreach($matrixData['days'] as $d => $dayInfo)
                    <th class="day-header" style="{{ $dayInfo['is_weekend'] ? 'background-color: #334155;' : '' }}">
                        {{ $d }}<br>
                        <span style="font-size: 5px; opacity: 0.85;">{{ $dayInfo['day_short'][0] }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($matrixData['rows'] as $index => $row)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $row['pegawai']->nama }}</div>
                        <div class="font-mono text-slate-500" style="font-size: 6px;">
                            {{ $row['pegawai']->nip ?? '-' }}
                        </div>
                    </td>
                    <td>
                        <div>{{ $row['pegawai']->jabatan?->nama_jabatan ?? '-' }}</div>
                        <div style="font-size: 6px; color: #64748b;">{{ $row['pegawai']->unitKerja?->nama_unit ?? '-' }}</div>
                    </td>

                    @foreach($matrixData['days'] as $d => $dayInfo)
                        @php
                            $dayRecord = $row['days'][$d] ?? null;
                            $cellClass = match($dayRecord['status'] ?? '') {
                                'present' => 'cell-present',
                                'late' => 'cell-late',
                                'absent' => 'cell-absent',
                                'weekend' => 'cell-weekend',
                                default => 'cell-future',
                            };
                        @endphp
                        <td class="day-cell {{ $cellClass }}">
                            {{ $dayRecord['badge'] ?? '·' }}
                        </td>
                    @endforeach

                    <td class="text-center font-bold font-mono">{{ $row['total_hadir'] }}</td>
                    <td class="text-center font-mono" style="{{ $row['total_late'] > 0 ? 'color: #b45309; font-weight: bold;' : '' }}">
                        {{ $row['total_late'] }}x
                        @if($row['total_late_minutes'] > 0)<br><span style="font-size: 5px;">({{ $row['total_late_minutes'] }}m)</span>@endif
                    </td>
                    <td class="text-center font-mono" style="{{ $row['total_early_count'] > 0 ? 'color: #ea580c; font-weight: bold;' : '' }}">
                        {{ $row['total_early_count'] }}x
                        @if($row['total_early_minutes'] > 0)<br><span style="font-size: 5px;">({{ $row['total_early_minutes'] }}m)</span>@endif
                    </td>
                    <td class="text-center font-mono" style="font-size: 5.5px; font-weight: bold;">{{ $row['total_duration'] }}</td>
                    <td class="text-center font-mono" style="font-size: 5.5px;">
                        @if($row['sanksi_hari'] > 0)
                            <strong style="color: #dc2626;">{{ $row['sanksi_hari'] }} Hari</strong><br><span style="font-size: 5px; color: #64748b;">(Sisa {{ $row['sisa_menit_sanksi'] }}m)</span>
                        @elseif($row['total_violation_minutes'] > 0)
                            <span style="color: #475569;">0 Hari</span><br><span style="font-size: 5px; color: #64748b;">({{ $row['formatted_violation_time'] }})</span>
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 8 + $matrixData['days_in_month'] }}" class="text-center" style="padding: 10px; color: #94a3b8;">
                        Tidak ada data pegawai aktif yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- KETERANGAN / LEGENDA & CATATAN ATURAN ASN UNRI -->
    <div class="legend">
        <div>
            <strong>Legenda:</strong>
            <span style="color: #16a34a;">[H] Hadir Tepat Waktu</span>
            <span style="color: #d97706;">[T] Terlambat</span>
            <span style="color: #dc2626;">[A] Tidak Hadir</span>
            <span style="color: #64748b;">[—] Akhir Pekan / Libur</span>
        </div>
        <div style="margin-top: 3px; font-size: 6px; color: #475569; border-top: 1px dashed #cbd5e1; padding-top: 2px;">
            <em>* Ketentuan Jam Kerja ASN Universitas Riau: Total 37,5 Jam/Minggu (7,5 Jam/Hari Efektif). Senin-Kamis (07.30-16.00 WIB, Istirahat 12.00-13.00 WIB); Jumat (07.30-16.30 WIB, Istirahat 11.45-13.15 WIB). Akumulasi keterlambatan & pulang sebelum waktu (PSW) mencapai 7,5 jam (450 menit) dalam sebulan setara sanksi 1 hari tidak masuk kerja.</em>
        </div>
    </div>

    <!-- LEMBAR PENGESAHAN / TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td width="65%"></td>
            <td width="35%" class="text-center">
                Pekanbaru, {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}<br>
                <strong>Dekan / Wakil Dekan Bidang Umum & Keuangan</strong><br>
                Fakultas Keperawatan Universitas Riau<br>
                <br><br><br><br>
                <strong><u>Dr. Wan Nishfa Dewi, M.Kep., Ns., Sp.Kep.An.</u></strong><br>
                NIP. 197412152002122001
            </td>
        </tr>
    </table>

</body>
</html>
