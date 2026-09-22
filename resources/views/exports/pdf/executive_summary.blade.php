<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Eksekutif Kepegawaian SIKAP FKP UNRI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header h3 { margin: 0; font-size: 13px; text-transform: uppercase; }
        .header h2 { margin: 2px 0; font-size: 15px; color: #007a3d; text-transform: uppercase; }
        .header p { margin: 0; font-size: 9px; color: #64748b; }
        .title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 14px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10px;
        }
        th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: left;
        }
        .kpi-box {
            display: inline-block;
            width: 23%;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 8px;
            margin-right: 1.5%;
            margin-bottom: 12px;
            vertical-align: top;
            box-sizing: border-box;
        }
        .kpi-title { font-size: 8px; font-weight: bold; color: #64748b; text-transform: uppercase; }
        .kpi-value { font-size: 16px; font-weight: bold; color: #1e293b; margin: 4px 0; }
        .kpi-desc { font-size: 8px; color: #3b82f6; }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 12px 0 6px 0;
            background: #e2e8f0;
            padding: 4px 6px;
            border-left: 3px solid #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>Kementerian Pendidikan Tinggi, Sains, dan Teknologi - Universitas Riau</h3>
        <h2>Fakultas Keperawatan</h2>
        <p>Sistem Informasi Kepegawaian (SIKAP) • Laman: keperawatan.unri.ac.id • Email: keperawatan@unri.ac.id</p>
    </div>

    <div class="title">
        Laporan Eksekutif Kinerja & Profil SDM<br>
        <span style="font-size: 10px; font-weight: normal; color: #475569;">Periode: {{ $namaBulan[$month] }} {{ $year }}</span>
    </div>

    <!-- 1. Ringkasan KPI -->
    <div style="margin-bottom: 6px;">
        <div class="kpi-box">
            <div class="kpi-title">Total SDM Aktif</div>
            <div class="kpi-value">{{ $kpis['total_aktif'] }}</div>
            <div class="kpi-desc">{{ $kpis['total_dosen'] }} Dosen • {{ $kpis['total_tendik'] }} Tendik</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-title">Verifikasi Logbook</div>
            <div class="kpi-value">{{ $kpis['logbook_rate'] }}%</div>
            <div class="kpi-desc">{{ $kpis['logbook_approved'] }} Selesai • {{ $kpis['logbook_pending'] }} Menunggu</div>
        </div>
        <div class="kpi-box">
            <div class="kpi-title">Kepatuhan Presensi</div>
            <div class="kpi-value">{{ $kpis['presensi_rate'] }}%</div>
            <div class="kpi-desc">{{ $kpis['presensi_tepat'] }} Tepat • {{ $kpis['presensi_telat'] }} Telat</div>
        </div>
        <div class="kpi-box" style="margin-right: 0;">
            <div class="kpi-title">Radar Pensiun (5 Th)</div>
            <div class="kpi-value">{{ $kpis['pensiun_5_tahun'] }} Org</div>
            <div class="kpi-desc">Formasi Pengganti Diperlukan</div>
        </div>
    </div>

    <!-- 2. Beban Kerja Logbook per Unit -->
    <div class="section-title">1. Beban Kerja Jam Logbook per Unit Kerja / Program Studi</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Unit Kerja / Bagian</th>
                <th style="text-align: right;">Total Jam Efektif</th>
                <th style="text-align: center;">Disetujui</th>
                <th style="text-align: center;">Menunggu Verifikasi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logbookWorkload['labels'] as $idx => $unitName)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $unitName }}</td>
                    <td style="text-align: right; font-weight: bold;">{{ $logbookWorkload['hours'][$idx] ?? 0 }} Jam</td>
                    <td style="text-align: center; color: #16a34a;">{{ $logbookWorkload['approved'][$idx] ?? 0 }}</td>
                    <td style="text-align: center; color: #d97706;">{{ $logbookWorkload['pending'][$idx] ?? 0 }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- 3. Radar Pensiun ASN -->
    <div class="section-title">2. Proyeksi Batas Usia Pensiun ASN (Radar 1-2 Tahun ke Depan)</div>
    @php
        $pensiunNear = $retirementRadar['details']['tahun_ini']->merge($retirementRadar['details']['tahun_1']);
    @endphp
    @if($pensiunNear->isEmpty())
        <p style="font-size: 9px; color: #64748b; font-style: italic;">Tidak ada pegawai yang memasuki masa pensiun dalam 1-2 tahun ke depan.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nama & NIP</th>
                    <th>Unit Kerja</th>
                    <th>Jabatan</th>
                    <th style="text-align: center;">BUP</th>
                    <th style="text-align: center;">TMT Pensiun</th>
                    <th style="text-align: center;">Sisa Waktu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pensiunNear as $p)
                    <tr>
                        <td><strong>{{ $p->nama }}</strong><br><span style="color:#64748b; font-size: 8px;">{{ $p->nip }}</span></td>
                        <td>{{ $p->unit }}</td>
                        <td>{{ $p->jabatan }}</td>
                        <td style="text-align: center;">{{ $p->bup }} Thn</td>
                        <td style="text-align: center;">{{ $p->tgl_pensiun }}</td>
                        <td style="text-align: center; font-weight: bold; color: #dc2626;">{{ $p->sisa_bulan }} Bulan</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <!-- 4. Profil Kualifikasi Dosen -->
    <div class="section-title">3. Profil Jabatan Akademik & Pendidikan Dosen</div>
    <table style="width: 50%; float: left; margin-right: 2%;">
        <thead>
            <tr>
                <th>Jabatan Akademik</th>
                <th style="text-align: center;">Jumlah Dosen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staffComposition['jafung'] as $jName => $jCount)
                <tr>
                    <td>{{ $jName }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $jCount }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 48%; float: left;">
        <thead>
            <tr>
                <th>Pendidikan Terakhir</th>
                <th style="text-align: center;">Jumlah Dosen</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staffComposition['pendidikan'] as $eName => $eCount)
                <tr>
                    <td>{{ $eName }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $eCount }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="clear: both;"></div>

    <div style="margin-top: 30px; text-align: right; font-size: 10px;">
        Pekanbaru, {{ date('d') }} {{ $namaBulan[(int)date('m')] }} {{ date('Y') }}<br>
        <strong>Pimpinan Fakultas Keperawatan Universitas Riau</strong>
        <br><br><br><br>
        ( ____________________________________ )
    </div>
</body>
</html>
