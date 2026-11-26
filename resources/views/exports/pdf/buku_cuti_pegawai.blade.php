<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku Kendali Cuti Pegawai - {{ $pegawai->nama }}</title>
    <style>
        @page {
            margin: 10mm 12mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.25;
        }
        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }
        .header h3 {
            margin: 0;
            font-size: 11pt;
            text-transform: uppercase;
        }
        .header h4 {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 8px;
        }
        table.border-table th, table.border-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.border-table th {
            background-color: #f0f0f0;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        <h3>KARTU KENDALI CUTI PEGAWAI (BUKU CUTI MANDIRI)</h3>
        <h4>FAKULTAS KEPERAWATAN UNIVERSITAS RIAU — TAHUN ANGGARAN {{ $tahun }}</h4>
    </div>

    {{-- DATA PEGAWAI --}}
    <table style="border: none; margin-bottom: 10px;">
        <tr>
            <td style="width: 18%; border: none;">Nama Pegawai</td>
            <td style="width: 32%; border: none; font-weight: bold;">: {{ $pegawai->nama_lengkap ?? $pegawai->nama }}</td>
            <td style="width: 18%; border: none;">NIP</td>
            <td style="width: 32%; border: none;">: {{ $pegawai->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td style="border: none;">Jabatan</td>
            <td style="border: none;">: {{ $pegawai->jabatan?->nama_jabatan ?? '-' }}</td>
            <td style="border: none;">Unit Kerja</td>
            <td style="border: none;">: {{ $pegawai->unitKerja?->nama_unit ?? 'Fakultas Keperawatan' }}</td>
        </tr>
    </table>

    {{-- REKAP SALDO KUOTA CUTI TAHUNAN (N, N-1, N-2) --}}
    <div style="font-weight: bold; font-size: 9.5pt; margin-top: 6px; margin-bottom: 2px;">
        I. REKAPITULASI KUOTA CUTI TAHUNAN (PERATURAN BKN NOMOR 7 TAHUN 2022)
    </div>
    <table class="border-table">
        <thead>
            <tr>
                <th style="width: 25%;">DIMENSI WAKTU</th>
                <th style="width: 25%;">TAHUN ANGGARAN</th>
                <th style="width: 25%;">SISA KUOTA</th>
                <th style="width: 25%;">KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tahun N-2 (2 Tahun Lalu)</td>
                <td class="text-center">{{ $catatanCuti['N-2']['label_tahun'] }}</td>
                <td class="text-center text-bold">{{ $catatanCuti['N-2']['sisa_formatted'] }}</td>
                <td>{{ $catatanCuti['N-2']['keterangan'] }}</td>
            </tr>
            <tr>
                <td>Tahun N-1 (1 Tahun Lalu)</td>
                <td class="text-center">{{ $catatanCuti['N-1']['label_tahun'] }}</td>
                <td class="text-center text-bold">{{ $catatanCuti['N-1']['sisa_formatted'] }}</td>
                <td>{{ $catatanCuti['N-1']['keterangan'] }}</td>
            </tr>
            <tr>
                <td>Tahun N (Tahun Berjalan)</td>
                <td class="text-center">{{ $catatanCuti['N']['label_tahun'] }}</td>
                <td class="text-center text-bold">{{ $catatanCuti['N']['sisa_formatted'] }}</td>
                <td>{{ $catatanCuti['N']['keterangan'] }}</td>
            </tr>
        </tbody>
    </table>

    {{-- RIWAYAT TRANSAKSI CUTI TAHUN BERJALAN --}}
    <div style="font-weight: bold; font-size: 9.5pt; margin-top: 10px; margin-bottom: 2px;">
        II. RIWAYAT TRANSAKSI CUTI TAHUN {{ $tahun }}
    </div>
    <table class="border-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 22%;">JENIS CUTI</th>
                <th style="width: 20%;">NO. SURAT IZIN</th>
                <th style="width: 23%;">TANGGAL PELAKSANAAN</th>
                <th style="width: 12%;">DURASI</th>
                <th style="width: 18%;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayatCuti as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row->jenis_cuti }}</td>
                    <td class="text-center">{{ $row->nomor_surat ?: '-' }}</td>
                    <td class="text-center">
                        {{ $row->tanggal_mulai ? $row->tanggal_mulai->format('d/m/Y') : '-' }} s.d.
                        {{ $row->tanggal_selesai ? $row->tanggal_selesai->format('d/m/Y') : '-' }}
                    </td>
                    <td class="text-center text-bold">{{ $row->jumlah_hari }} Hari</td>
                    <td class="text-center">{{ $row->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="font-style: italic; color: #666; padding: 10px;">
                        Belum ada catatan pengambilan cuti pada tahun {{ $tahun }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table style="border: none; margin-top: 30px;">
        <tr style="border: none;">
            <td style="border: none; width: 60%;"></td>
            <td style="border: none; width: 40%; text-align: center;">
                Pekanbaru, {{ now()->translatedFormat('d F Y') }}<br>
                Admin Screening Kepegawaian,<br><br><br><br>
                <strong>Dolli Vita Zenitha Harning Arivina, SE</strong><br>
                NIP. 197605212014092001
            </td>
        </tr>
    </table>

</body>
</html>
