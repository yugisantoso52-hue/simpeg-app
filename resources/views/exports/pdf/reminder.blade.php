<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Pengingat Jatuh Tempo Kepegawaian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007a3d;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 15px;
            color: #007a3d;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 3px 0 0 0;
            font-size: 12px;
            font-weight: normal;
        }
        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #1e293b;
        }
        .cat-title {
            background-color: #e2e8f0;
            font-weight: bold;
            font-size: 11px;
            padding: 5px 8px;
            margin-top: 10px;
            border: 1px solid #cbd5e1;
        }
        .footer {
            margin-top: 30px;
            float: right;
            text-align: center;
            width: 200px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sistem Informasi Kepegawaian (SIKAP)</h2>
        <h3>Fakultas Keperawatan - Universitas Riau</h3>
    </div>

    <div class="title">
        Daftar Pengingat & Jatuh Tempo Transaksi Kepegawaian<br>
        <span style="font-size: 10px; font-weight: normal; text-transform: none;">Per Tanggal: {{ date('d F Y') }}</span>
    </div>

    @php
        $categories = [
            'kgb'          => ['label' => 'Kenaikan Gaji Berkala (KGB) - 3 Bulan ke Depan', 'data' => $reminder['kgb'] ?? []],
            'kp'           => ['label' => 'Kenaikan Pangkat (KP) - 3 Bulan ke Depan', 'data' => $reminder['kp'] ?? []],
            'pensiun'      => ['label' => 'Batas Usia Pensiun (BUP 58 Tahun) - 1 Tahun ke Depan', 'data' => $reminder['pensiun'] ?? []],
            'satyalancana' => ['label' => 'Satyalancana Karya Satya - 3 Bulan ke Depan', 'data' => $reminder['satyalancana'] ?? []],
        ];
    @endphp

    @foreach($categories as $key => $cat)
        @if($type === 'all' || $type === $key)
            <div class="cat-title">{{ $cat['label'] }} (Total: {{ count($cat['data']) }})</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%; text-align: center;">No</th>
                        <th style="width: 20%;">NIP</th>
                        <th style="width: 25%;">Nama Pegawai</th>
                        <th style="width: 20%;">Unit Kerja</th>
                        <th style="width: 15%;">Pangkat / Gol.</th>
                        <th style="width: 15%; text-align: center;">Jatuh Tempo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cat['data'] as $idx => $pegawai)
                        <tr>
                            <td style="text-align: center;">{{ $idx + 1 }}</td>
                            <td>{{ $pegawai->nip ?? '-' }}</td>
                            <td><strong>{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</strong></td>
                            <td>{{ $pegawai->unitKerja->nama_unit ?? '-' }}</td>
                            <td>{{ $pegawai->golongan->nama_golongan ?? '-' }}</td>
                            <td style="text-align: center; font-weight: bold; color: #007a3d;">{{ $pegawai->tanggal_kegiatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; font-style: italic; color: #64748b;">
                                Tidak ada data jatuh tempo untuk kategori ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @endif
    @endforeach

    <div class="footer">
        Pekanbaru, {{ date('d F Y') }}<br>
        Kasubbag Kepegawaian<br><br><br><br>
        <strong>NIP. ..................................</strong>
    </div>
</body>
</html>