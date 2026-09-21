<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Logbook Kinerja - {{ $pegawai->nama }} ({{ $namaBulan }})</title>
    <style>
        @page {
            margin: 12mm 15mm 12mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.3;
        }
        /* Styling Kop Surat Resmi Sesuai Permendikti Saintek No. 42 Tahun 2025 */
        .header-kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000;
            padding-bottom: 4px;
            margin-bottom: 12px;
        }
        .header-kop-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }
        .kop-logo-cell {
            width: 80px;
            text-align: left;
        }
        .kop-logo-cell img {
            width: 70px;
            height: 70px;
            display: block;
        }
        .kop-text-cell {
            text-align: center;
            padding-right: 20px;
        }
        .kop-kemdikti {
            margin: 0;
            font-size: 11pt;
            font-weight: normal;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .kop-ptn {
            margin: 1px 0;
            font-size: 10.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .kop-fakultas {
            margin: 1px 0;
            font-size: 10.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
        }
        .kop-alamat {
            margin: 2px 0 0 0;
            font-size: 7.5pt;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.2;
        }

        .report-title {
            text-align: center;
            font-weight: bold;
            font-size: 11.5pt;
            margin: 8px 0 4px 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            text-align: center;
            font-size: 9.5pt;
            margin-bottom: 14px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-table td {
            padding: 2px 4px;
            font-size: 9pt;
            vertical-align: top;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }

        .summary-box {
            border: 1px solid #000;
            padding: 6px 10px;
            margin-bottom: 16px;
            background-color: #fafafa;
            font-size: 8.5pt;
        }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .sign-table td {
            border: none;
            vertical-align: top;
            font-size: 9pt;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT FKP UNRI SESUAI PERMENDIKTI 42/2025 --}}
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
            <td class="kop-logo-cell">
                @if($foundLogo)
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($foundLogo)) }}" alt="Logo UNRI">
                @else
                    <div style="font-size: 8pt; color: #666; text-align: center;">[LOGO UNRI]</div>
                @endif
            </td>
            <td class="kop-text-cell">
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

    {{-- JUDUL LAPORAN --}}
    <div class="report-title">LAPORAN AKTIVITAS KINERJA HARIAN PEGAWAI (E-LOGBOOK)</div>
    <div class="report-subtitle">Periode: <strong>{{ $namaBulan }}</strong></div>

    {{-- IDENTITAS PEGAWAI --}}
    <table class="info-table">
        <tr>
            <td style="width: 18%;"><strong>Nama Pegawai</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 40%;">{{ $pegawai->nama }}</td>
            <td style="width: 18%;"><strong>Unit Kerja</strong></td>
            <td style="width: 2%;">:</td>
            <td style="width: 20%;">{{ $pegawai->unitKerja?->nama_unit ?? 'FKP UNRI' }}</td>
        </tr>
        <tr>
            <td><strong>NIP</strong></td>
            <td>:</td>
            <td>{{ $pegawai->nip ?? '-' }}</td>
            <td><strong>Jabatan</strong></td>
            <td>:</td>
            <td>{{ $pegawai->jabatan?->nama_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Pangkat / Golongan</strong></td>
            <td>:</td>
            <td>{{ $pegawai->golongan?->nama_golongan ?? '-' }} ({{ $pegawai->golongan?->ruang ?? '-' }})</td>
            <td><strong>Kategori Pegawai</strong></td>
            <td>:</td>
            <td>{{ $pegawai->jenis_pegawai ?? '-' }}</td>
        </tr>
    </table>

    {{-- RINGKASAN CAPAIAN BULAN INI --}}
    <div class="summary-box">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 25%;"><strong>Total Aktivitas</strong>: {{ $statistics['total_aktivitas'] }} Kegiatan</td>
                <td style="width: 25%;"><strong>Total Jam Kerja</strong>: {{ $statistics['total_jam'] }} Jam ({{ $statistics['total_menit'] }} Menit)</td>
                <td style="width: 25%;"><strong>Total Output</strong>: {{ $statistics['total_output'] }} Berkas/Item</td>
                <td style="width: 25%;"><strong>Status Disetujui</strong>: {{ $statistics['disetujui'] }} / {{ $statistics['total_aktivitas'] }}</td>
            </tr>
        </table>
    </div>

    {{-- TABEL AKTIVITAS --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 13%;">Tanggal</th>
                <th style="width: 13%;">Waktu</th>
                <th style="width: 17%;">Kategori</th>
                <th style="width: 32%;">Uraian Pekerjaan / Kegiatan</th>
                <th style="width: 12%;">Output</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logbooks as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('D/MM/Y') }}<br>
                        <span style="font-size: 7.5pt; color: #555;">{{ \Carbon\Carbon::parse($item->tanggal)->locale('id')->isoFormat('dddd') }}</span>
                    </td>
                    <td class="text-center">
                        {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}<br>
                        <span style="font-size: 7.5pt; color: #333;">({{ $item->durasi_menit }} Menit)</span>
                    </td>
                    <td>{{ $item->kategori_kegiatan }}</td>
                    <td>
                        <strong>{{ $item->aktivitas }}</strong><br>
                        <span style="font-size: 8pt; color: #333;">{{ $item->deskripsi_kegiatan }}</span>
                    </td>
                    <td>
                        {{ $item->jumlah_output }} {{ $item->satuan_output }}
                        @if($item->output_kegiatan)
                            <br><span style="font-size: 7.5pt; color: #555;">({{ $item->output_kegiatan }})</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 7.5pt;">
                        @if($item->status === 'disetujui')
                            <strong>Disetujui</strong>
                        @elseif($item->status === 'diajukan')
                            Diajukan
                        @else
                            {{ ucfirst($item->status) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #777;">
                        Tidak ada catatan aktivitas logbook pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN PENGESAHAN --}}
    <table class="sign-table">
        <tr>
            <td style="width: 50%; text-align: center;">
                Mengetahui,<br>
                Atasan Langsung / Pimpinan<br><br><br><br><br>
                ....................................................<br>
                NIP. ...............................................
            </td>
            <td style="width: 50%; text-align: center;">
                Pekanbaru, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                Pegawai yang Bersangkutan<br><br><br><br><br>
                {{ $pegawai->nama }}<br>
                NIP. {{ $pegawai->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
