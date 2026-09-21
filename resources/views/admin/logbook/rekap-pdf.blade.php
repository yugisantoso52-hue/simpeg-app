<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Logbook Kinerja Pegawai - {{ $unitKerja?->nama_unit ?? 'Semua Unit' }} ({{ $namaBulan }})</title>
    <style>
        @page {
            margin: 10mm 12mm 10mm 12mm;
            size: a4 landscape;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 8.5pt;
            color: #000;
            line-height: 1.25;
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
            font-size: 11pt;
            margin: 6px 0 2px 0;
            text-transform: uppercase;
        }
        .report-subtitle {
            text-align: center;
            font-size: 9pt;
            margin-bottom: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 8pt;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .sign-table td {
            border: none;
            vertical-align: top;
            font-size: 8.5pt;
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
    <div class="report-title">REKAPITULASI AKTIVITAS KINERJA HARIAN (LOGBOOK) PEGAWAI</div>
    <div class="report-subtitle">
        Unit Kerja: <strong>{{ $unitKerja?->nama_unit ?? 'Seluruh Unit Kerja / Fakultas' }}</strong> • Periode: <strong>{{ $namaBulan }}</strong>
    </div>

    {{-- TABEL DATA REKAP --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 16%;">Nama Pegawai & NIP</th>
                <th style="width: 12%;">Unit Kerja</th>
                <th style="width: 7%;">Tanggal</th>
                <th style="width: 8%;">Waktu & Durasi</th>
                <th style="width: 12%;">Kategori</th>
                <th style="width: 23%;">Aktivitas / Kegiatan</th>
                <th style="width: 10%;">Output</th>
                <th style="width: 9%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logbooks as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $item->pegawai?->nama ?? '-' }}</strong><br>
                        <span style="font-size: 7pt; color: #555;">NIP: {{ $item->pegawai?->nip ?? '-' }}</span>
                    </td>
                    <td>{{ $item->pegawai?->unitKerja?->nama_unit ?? '-' }}</td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') }}
                    </td>
                    <td class="text-center">
                        {{ substr($item->jam_mulai, 0, 5) }} - {{ substr($item->jam_selesai, 0, 5) }}<br>
                        <span style="font-size: 7pt; color: #333;">({{ $item->durasi_menit }} mnt)</span>
                    </td>
                    <td>{{ $item->kategori_kegiatan }}</td>
                    <td>
                        <strong>{{ $item->aktivitas }}</strong>
                        @if($item->deskripsi_kegiatan)
                            <br><span style="font-size: 7.5pt; color: #444;">{{ Str::limit($item->deskripsi_kegiatan, 100) }}</span>
                        @endif
                    </td>
                    <td>
                        {{ $item->jumlah_output }} {{ $item->satuan_output }}
                        @if($item->output_kegiatan)
                            <br><span style="font-size: 7pt; color: #555;">({{ Str::limit($item->output_kegiatan, 40) }})</span>
                        @endif
                    </td>
                    <td class="text-center" style="font-size: 7.5pt;">
                        @if($item->status === 'disetujui')
                            <strong>Disetujui</strong>
                        @elseif($item->status === 'diajukan')
                            Menunggu
                        @elseif($item->status === 'perlu_revisi')
                            Revisi
                        @else
                            {{ ucfirst($item->status) }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px; color: #777;">
                        Tidak ada data aktivitas logbook yang ditemukan pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN --}}
    <table class="sign-table">
        <tr>
            <td style="width: 70%;"></td>
            <td style="width: 30%; text-align: center;">
                Pekanbaru, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM Y') }}<br>
                Dekan / Wakil Dekan,<br><br><br><br><br>
                ....................................................<br>
                NIP. ...............................................
            </td>
        </tr>
    </table>

</body>
</html>
