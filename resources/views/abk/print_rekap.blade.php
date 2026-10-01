<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Kebutuhan Pegawai (ABK) - FKP UNRI</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10pt;
            line-height: 1.3;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 4px 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 8.5pt;
        }
        .title-doc {
            text-align: center;
            font-weight: bold;
            font-size: 12pt;
            text-transform: uppercase;
            margin-bottom: 12px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #000;
            padding: 5px 6px;
            vertical-align: middle;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
            font-size: 9pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
        }
        .signature-table td {
            border: none;
            padding: 4px;
            width: 50%;
            text-align: center;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 landscape;
                margin: 15mm 10mm 15mm 10mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: #fff; font-weight: bold; border: none; border-radius: 4px; cursor: pointer;">
            🖨️ Cetak Rekapitulasi (PDF / Cetak Landscape A4)
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">
            Tutup
        </button>
    </div>

    {{-- KOP RESMI --}}
    <div class="header">
        <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
        <h2>UNIVERSITAS RIAU — FAKULTAS KEPERAWATAN</h2>
        <p>Kampus Bina Widya Km. 12,5 Simpang Baru, Pekanbaru 28293 | Laman: https://fkp.unri.ac.id</p>
    </div>

    <div class="title-doc">
        REKAPITULASI ANALISIS BEBAN KERJA & FORMASI KEBUTUHAN PEGAWAI<br>
        <span style="font-size: 9pt; font-weight: normal; text-decoration: none;">
            Acuan: PermenPAN-RB No. 1 Tahun 2020 & Peraturan BKN No. 19 Tahun 2011 (Standar WKE: 1.250 Jam / 75.000 Menit)
            @if($selectedUnit)
                <br><strong>Unit Kerja: {{ $selectedUnit->nama_unit }}</strong>
            @endif
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 25%;">Nama Jabatan</th>
                <th style="width: 20%;">Unit Kerja</th>
                <th style="width: 6%;">Kelas (Grade)</th>
                <th style="width: 6%;">Jumlah Tugas</th>
                <th style="width: 10%;">Total Beban (Menit)</th>
                <th style="width: 7%;">Kebutuhan (ABK)</th>
                <th style="width: 7%;">Formasi</th>
                <th style="width: 7%;">Bezetting (Riil)</th>
                <th style="width: 8%;">Selisih (+/-)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totBeban = 0;
                $totKebutuhan = 0;
                $totBezetting = 0;
            @endphp
            @forelse($anjabs as $idx => $item)
                @php
                    $totBeban += $item->total_waktu_beban_menit;
                    $totKebutuhan += $item->formasi_pembulatan;
                    $totBezetting += $item->bezetting;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->jabatan->nama_jabatan ?? '-' }}</strong></td>
                    <td>{{ $item->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}</td>
                    <td style="text-align: center;">{{ $item->kelas_jabatan ?? $item->jabatan->kelas_jabatan ?? '-' }}</td>
                    <td style="text-align: center;">{{ $item->uraianTugas->count() }}</td>
                    <td style="text-align: right;">{{ number_format($item->total_waktu_beban_menit) }}</td>
                    <td style="text-align: center;">{{ $item->kebutuhan_pegawai }}</td>
                    <td style="text-align: center; font-weight: bold; background: #e0f2fe;">{{ $item->formasi_pembulatan }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $item->bezetting }}</td>
                    <td style="text-align: center; font-weight: bold; color: {{ $item->selisih_formasi < 0 ? '#b91c1c' : ($item->selisih_formasi > 0 ? '#b45309' : '#047857') }};">
                        {{ $item->selisih_formasi > 0 ? '+' : '' }}{{ $item->selisih_formasi }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center; font-style: italic;">Belum ada data analisis beban kerja.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f2f2f2;">
                <td colspan="5" style="text-align: right;">TOTAL KESELURUHAN:</td>
                <td style="text-align: right;">{{ number_format($totBeban) }}</td>
                <td></td>
                <td style="text-align: center; font-size: 11pt; background: #bae6fd;">{{ $totKebutuhan }}</td>
                <td style="text-align: center; font-size: 11pt;">{{ $totBezetting }}</td>
                <td style="text-align: center; font-size: 11pt;">{{ ($totBezetting - $totKebutuhan) > 0 ? '+' : '' }}{{ $totBezetting - $totKebutuhan }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                Mengetahui / Mengesahkan:<br>
                <strong>Dekan Fakultas Keperawatan UNRI,</strong>
                <br><br><br><br>
                <u>Prof. Dr. Ir. H. Pimpinan, M.Kes</u><br>
                NIP. 196801011993031001
            </td>
            <td>
                Pekanbaru, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Pengelola Kepegawaian & Tim Anjab,<br>
                <strong>Ka Pokja Keuangan dan Kepegawaian,</strong>
                <br><br><br><br>
                <u>Pengelola Kepegawaian FKP</u><br>
                NIP. 198205122008121002
            </td>
        </tr>
    </table>

</body>
</html>
