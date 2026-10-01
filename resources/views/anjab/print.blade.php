<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Anjab - {{ $anjab->jabatan->nama_jabatan ?? 'Jabatan' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 4px 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 0;
            font-size: 9pt;
        }
        .title-doc {
            text-align: center;
            font-weight: bold;
            font-size: 13pt;
            text-transform: uppercase;
            margin-bottom: 15px;
            text-decoration: underline;
        }
        table.content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.content-table th, table.content-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        table.content-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .section-title {
            font-weight: bold;
            background-color: #e6e6e6;
        }
        .signature-table {
            width: 100%;
            margin-top: 30px;
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
                size: A4;
                margin: 20mm 15mm 20mm 15mm;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0284c7; color: #fff; font-weight: bold; border: none; border-radius: 4px; cursor: pointer;">
            🖨️ Cetak Formulir (PDF / Kertas A4)
        </button>
        <button onclick="window.close()" style="padding: 8px 16px; background: #64748b; color: #fff; font-weight: bold; border: none; border-radius: 4px; cursor: pointer; margin-left: 8px;">
            Tutup
        </button>
    </div>

    {{-- KOP SURAT RESMI KEMENDIKTISAINTEK / UNRI --}}
    <div class="header">
        <h3>KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</h3>
        <h2>UNIVERSITAS RIAU — FAKULTAS KEPERAWATAN</h2>
        <p>Kampus Bina Widya Km. 12,5 Simpang Baru, Pekanbaru 28293 | Laman: https://fkp.unri.ac.id</p>
    </div>

    <div class="title-doc">
        INFORMASI JABATAN<br>
        <span style="font-size: 10pt; font-weight: normal; text-decoration: none;">(Berdasarkan PermenPAN-RB No. 1 Tahun 2020 & Peraturan BKN No. 12 Tahun 2011)</span>
    </div>

    <table class="content-table">
        <tr>
            <td style="width: 25%; font-weight: bold;">1. Nama Jabatan</td>
            <td style="width: 75%;">{{ $anjab->jabatan->nama_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">2. Kode Jabatan</td>
            <td>{{ $anjab->kode_anjab ?? $anjab->jabatan->kode_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">3. Unit Kerja</td>
            <td>
                Fakultas Keperawatan Universitas Riau<br>
                <strong>Unit Pengelola:</strong> {{ $anjab->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">4. Ikhtisar Jabatan</td>
            <td>{{ $anjab->ikhtisar_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">5. Kualifikasi Jabatan</td>
            <td>
                <strong>a. Pendidikan Formal:</strong> {{ $anjab->kualifikasi_pendidikan ?? '-' }}<br>
                <strong>b. Diklat / Pelatihan:</strong> {{ $anjab->kualifikasi_pelatihan ?? '-' }}<br>
                <strong>c. Pengalaman Kerja:</strong> {{ $anjab->kualifikasi_pengalaman ?? '-' }}
            </td>
        </tr>
    </table>

    {{-- Tabel Tugas Pokok & Beban Kerja --}}
    <p style="font-weight: bold; margin-bottom: 4px;">6. Tugas Pokok & Analisis Beban Kerja (WKE Standar: 1.250 Jam / 75.000 Menit per Tahun):</p>
    <table class="content-table" style="font-size: 9.5pt;">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Uraian Tugas Pokok</th>
                <th style="width: 15%;">Satuan Hasil</th>
                <th style="width: 12%;">Waktu (Menit)</th>
                <th style="width: 10%;">Volume 1 Thn</th>
                <th style="width: 18%;">Waktu Beban (Menit)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($anjab->uraianTugas as $idx => $t)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $t->uraian_tugas }}</td>
                    <td style="text-align: center;">{{ $t->satuan_hasil }}</td>
                    <td style="text-align: center;">{{ $t->norma_waktu_menit }}</td>
                    <td style="text-align: center;">{{ $t->volume_1_tahun }}</td>
                    <td style="text-align: right;">{{ number_format($t->waktu_beban_menit) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; font-style: italic;">Belum ada butir tugas beban kerja.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f2f2f2;">
                <td colspan="5" style="text-align: right;">TOTAL BEBAN KERJA TAHUNAN (JKE):</td>
                <td style="text-align: right;">{{ number_format($anjab->total_waktu_beban_menit) }} Menit ({{ $anjab->total_jam_beban }} Jam)</td>
            </tr>
            <tr style="font-weight: bold; background: #e6e6e6;">
                <td colspan="5" style="text-align: right;">KEBUTUHAN PEGAWAI (ABK = JKE / 75.000 MENIT):</td>
                <td style="text-align: right;">{{ $anjab->kebutuhan_pegawai }} ≈ {{ $anjab->formasi_pembulatan }} Orang</td>
            </tr>
        </tfoot>
    </table>

    {{-- Rincian Instrumen Lainnya --}}
    <table class="content-table">
        <tr>
            <td style="width: 25%; font-weight: bold;">7. Bahan Kerja</td>
            <td style="width: 75%; white-space: pre-line;">{{ $anjab->bahan_kerja ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">8. Perangkat Kerja</td>
            <td style="white-space: pre-line;">{{ $anjab->perangkat_kerja ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">9. Tanggung Jawab</td>
            <td style="white-space: pre-line;">{{ $anjab->tanggung_jawab ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">10. Wewenang</td>
            <td style="white-space: pre-line;">{{ $anjab->wewenang ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">11. Korelasi Jabatan</td>
            <td style="white-space: pre-line;">{{ $anjab->korelasi_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">12. Kondisi Lingkungan Kerja</td>
            <td style="white-space: pre-line;">{{ $anjab->kondisi_lingkungan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">13. Resiko Bahaya</td>
            <td style="white-space: pre-line;">{{ $anjab->resiko_bahaya ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">14. Syarat Jabatan</td>
            <td>
                <strong>a. Keterampilan Kerja:</strong> {{ $anjab->syarat_keterampilan ?? '-' }}<br>
                <strong>b. Bakat Kerja:</strong> {{ $anjab->syarat_bakat ?? '-' }}<br>
                <strong>c. Temperamen Kerja:</strong> {{ $anjab->syarat_temperamen ?? '-' }}<br>
                <strong>d. Minat Kerja:</strong> {{ $anjab->syarat_minat ?? '-' }}<br>
                <strong>e. Upaya Fisik:</strong> {{ $anjab->syarat_upaya_fisik ?? '-' }}<br>
                <strong>f. Kondisi Fisik:</strong> {{ $anjab->kondisi_fisik ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">15. Prestasi Kerja yang Diharapkan</td>
            <td>{{ $anjab->prestasi_diharapkan ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">16. Kelas Jabatan</td>
            <td><strong>Grade {{ $anjab->kelas_jabatan ?? $anjab->jabatan->kelas_jabatan ?? '-' }}</strong></td>
        </tr>
    </table>

    {{-- Kolom Tanda Tangan --}}
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui / Mengesahkan:<br>
                <strong>Dekan Fakultas Keperawatan,</strong>
                <br><br><br><br>
                <u>Prof. Dr. Ir. H. Pimpinan, M.Kes</u><br>
                NIP. 196801011993031001
            </td>
            <td>
                Pekanbaru, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Pejabat / Tim Penyusun Anjab,<br>
                <strong>Ka Pokja Keu & Kepegawaian,</strong>
                <br><br><br><br>
                <u>Pengelola Kepegawaian FKP</u><br>
                NIP. 198205122008121002
            </td>
        </tr>
    </table>

</body>
</html>
