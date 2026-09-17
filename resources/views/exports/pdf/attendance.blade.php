<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Presensi Pegawai</title>
    <style>
        @page {
            margin: 10mm 8mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8px;
            color: #1e293b;
            line-height: 1.3;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .header h3 {
            margin: 0;
            font-size: 9px;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 2px 0;
            font-size: 11px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .header h1 {
            margin: 2px 0 0;
            font-size: 13px;
            font-weight: 900;
            color: #007a3d;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0 0;
            font-size: 8px;
            color: #64748b;
        }
        .title-block {
            text-align: center;
            margin-bottom: 12px;
        }
        .title-block h4 {
            margin: 0;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #1e3a8a;
        }
        .title-block .subtitle {
            font-size: 9px;
            color: #475569;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
            vertical-align: middle;
        }
        th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            font-size: 7.5px;
            text-align: center;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: monospace; }
        
        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-wfo { background-color: #dbeafe; color: #1e40af; }
        .badge-wfh { background-color: #e0e7ff; color: #3730a3; }
        .badge-on-time { background-color: #dcfce7; color: #15803d; }
        .badge-late { background-color: #fef3c7; color: #b45309; }

        .summary-box {
            margin-bottom: 10px;
            padding: 6px 10px;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 8px;
        }

        .signature-table {
            width: 100%;
            margin-top: 20px;
            border: none;
        }
        .signature-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
    </style>
</head>
<body>

    <!-- KOP INSTANSI RESMI UNRI -->
    <div class="header">
        <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
        <h2>UNIVERSITAS RIAU — FAKULTAS KEPERAWATAN</h2>
        <h1>SISTEM INFORMASI KEPEGAWAIAN (SIKAP)</h1>
        <p>Kampus Bina Widya Gedung Health Studies Complex Km.12,5 Simpang Baru 28293 | Email: keperawatan@unri.ac.id</p>
    </div>

    <!-- JUDUL LAPORAN -->
    <div class="title-block">
        <h4>REKAPITULASI PRESENSI KEHADIRAN PEGAWAI</h4>
        <div class="subtitle">
            Periode: <strong>{{ $periodText }}</strong> | Dicetak pada: {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y, H:i') }} WIB
        </div>
    </div>

    <!-- RINGKASAN DATA -->
    <div class="summary-box">
        <strong>Ringkasan:</strong> Total Log Presensi: <strong>{{ count($attendances) }}</strong> Catatan |
        Presensi WFO: <strong>{{ $attendances->where('attendance_type', 'wfo')->count() }}</strong> |
        Presensi WFH: <strong>{{ $attendances->where('attendance_type', 'wfh')->count() }}</strong> |
        Tepat Waktu: <strong>{{ $attendances->where('status', 'present')->count() }}</strong> |
        Terlambat: <strong>{{ $attendances->where('status', 'late')->count() }}</strong>
    </div>

    <!-- TABEL DATA PRESENSI -->
    <table>
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="17%">NAMA PEGAWAI / NIP</th>
                <th width="15%">JABATAN & UNIT KERJA</th>
                <th width="8%">TANGGAL</th>
                <th width="6%">TIPE</th>
                <th width="8%">JAM MASUK</th>
                <th width="8%">JAM PULANG</th>
                <th width="13%">TOTAL JAM KERJA</th>
                <th width="7%">JARAK</th>
                <th width="15%">STATUS KEHADIRAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $item)
                <tr>
                    <td class="text-center font-bold">{{ $index + 1 }}</td>
                    <td>
                        <div class="font-bold">{{ $item->user->name ?? '-' }}</div>
                        <div class="font-mono text-slate-500" style="font-size: 7px;">
                            {{ $item->user->pegawai?->nip ?? $item->user->email }}
                        </div>
                    </td>
                    <td>
                        <div>{{ $item->user->pegawai?->jabatan?->nama_jabatan ?? '-' }}</div>
                        <div style="font-size: 7px; color: #64748b;">{{ $item->user->pegawai?->unitKerja?->nama_unit ?? '-' }}</div>
                    </td>
                    <td class="text-center font-mono">
                        {{ $item->attendance_date ? $item->attendance_date->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $item->attendance_type === 'wfh' ? 'badge-wfh' : 'badge-wfo' }}">
                            {{ strtoupper($item->attendance_type) }}
                        </span>
                    </td>
                    <td class="text-center font-mono font-bold">
                        {{ $item->check_in_time ? $item->check_in_time->timezone('Asia/Jakarta')->format('H:i:s') : '-' }}
                    </td>
                    <td class="text-center font-mono font-bold">
                        {{ $item->check_out_time ? $item->check_out_time->timezone('Asia/Jakarta')->format('H:i:s') : '-' }}
                    </td>
                    <td class="text-center font-mono font-bold" style="color: #0f172a;">
                        {{ $item->work_duration }}
                    </td>
                    <td class="text-center font-mono">
                        {{ number_format($item->check_in_distance_meters, 1) }} m
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $item->status === 'late' ? 'badge-late' : 'badge-on-time' }}">
                            {{ $item->status_badge['label'] ?? ucfirst($item->status) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada catatan presensi pada periode filter ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- LEMBAR PENGESAHAN / TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td width="60%"></td>
            <td width="40%" class="text-center">
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
