<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Riwayat Hidup Usulan Satyalancana Karya Satya - {{ $pegawai->nama_lengkap ?? $pegawai->nama }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 12mm 15mm 12mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            line-height: 1.25;
            color: #1e293b;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .kop-text-1 {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #334155;
        }
        .kop-text-2 {
            font-size: 11pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }
        .kop-text-3 {
            font-size: 10pt;
            font-weight: 700;
            text-transform: uppercase;
            color: #047857;
        }
        .kop-text-sub {
            font-size: 7.5pt;
            color: #64748b;
        }
        .title-box {
            text-align: center;
            margin: 10px 0 12px 0;
        }
        .title-box h1 {
            font-size: 11pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
        }
        .title-box p {
            font-size: 8pt;
            color: #475569;
            margin: 2px 0 0 0;
        }
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            background-color: #f1f5f9;
            padding: 3px 6px;
            border-left: 3px solid #0f172a;
            margin: 8px 0 4px 0;
            text-transform: uppercase;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 8pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #cbd5e1;
            padding: 3px 5px;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f8fafc;
            font-weight: bold;
            text-align: center;
            color: #1e293b;
        }
        .identitas-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 6px;
        }
        .identitas-table td {
            padding: 1.5px 3px;
            vertical-align: top;
        }
        .ttd-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .ttd-table td {
            vertical-align: top;
            font-size: 8pt;
        }
        .qr-box {
            text-align: center;
            padding: 4px;
        }
        .qr-img {
            width: 65px;
            height: 65px;
        }
        .badge-kategori {
            display: inline-block;
            background-color: #fef3c7;
            color: #92400e;
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #fde68a;
        }
    </style>
</head>
<body>

    <!-- KOP RESMI SURAT -->
    <table class="header-table">
        <tr>
            <td style="width: 15%; text-align: center;">
                <img src="{{ public_path('logo-unri.png') }}" style="height: 55px;" onerror="this.style.display='none'">
            </td>
            <td style="width: 85%; text-align: center;">
                <div class="kop-text-1">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div class="kop-text-2">UNIVERSITAS RIAU</div>
                <div class="kop-text-3">FAKULTAS KEPERAWATAN</div>
                <div class="kop-text-sub">Kampus Bina Widya Km. 12,5 Simpang Baru, Pekanbaru 28293 | Laman: fkep.unri.ac.id</div>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN -->
    <div class="title-box">
        <h1>DAFTAR RIWAYAT HIDUP USULAN TANDA KEHORMATAN</h1>
        <p>SATYALANCANA KARYA SATYA <span class="badge-kategori">{{ strtoupper($kategori) }}</span></p>
    </div>

    <!-- I. IDENTITAS PEGAWAI -->
    <div class="section-title">I. KETERANGAN PERORANGAN</div>
    <table class="identitas-table">
        <tr>
            <td style="width: 4%;">1.</td>
            <td style="width: 28%;">Nama Lengkap</td>
            <td style="width: 2%;">:</td>
            <td style="width: 66%; font-weight: bold;">{{ $pegawai->nama_lengkap ?? $pegawai->nama }}</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>NIP / NIDN</td>
            <td>:</td>
            <td>{{ $pegawai->nip }} {{ $pegawai->nidn_nuptk ? ' / ' . $pegawai->nidn_nuptk : '' }}</td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Tempat, Tanggal Lahir</td>
            <td>:</td>
            <td>{{ $pegawai->tempat_lahir ?? '-' }}, {{ $pegawai->tanggal_lahir ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->locale('id')->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Pangkat / Golongan Ruang / TMT</td>
            <td>:</td>
            <td>
                {{ $pegawai->golongan->nama_golongan ?? '-' }} ({{ $pegawai->golongan->kode_golongan ?? '-' }}) 
                TMT: {{ $pegawai->tmt_pangkat_terakhir ? \Carbon\Carbon::parse($pegawai->tmt_pangkat_terakhir)->locale('id')->translatedFormat('d F Y') : '-' }}
            </td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Jabatan Terakhir</td>
            <td>:</td>
            <td>{{ $pegawai->jabatan->nama_jabatan ?? '-' }}</td>
        </tr>
        <tr>
            <td>6.</td>
            <td>Unit Kerja / Fakultas</td>
            <td>:</td>
            <td>{{ $pegawai->unitKerja->nama_unit ?? 'Fakultas Keperawatan' }} - Universitas Riau</td>
        </tr>
        <tr>
            <td>7.</td>
            <td>TMT CPNS / Pengangkatan Pertama</td>
            <td>:</td>
            <td>
                {{ $pegawai->tmt_sk_pertama ? \Carbon\Carbon::parse($pegawai->tmt_sk_pertama)->locale('id')->translatedFormat('d F Y') : '-' }}
                (No. SK: {{ $pegawai->nomor_sk_pertama ?? '-' }})
            </td>
        </tr>
        <tr>
            <td>8.</td>
            <td>Masa Kerja Keseluruhan</td>
            <td>:</td>
            <td>
                @php
                    $tglMasuk = $pegawai->tmt_sk_pertama ?? $pegawai->tanggal_masuk;
                    $diffY = $tglMasuk ? \Carbon\Carbon::parse($tglMasuk)->diffInYears(now()) : 0;
                    $diffM = $tglMasuk ? (\Carbon\Carbon::parse($tglMasuk)->diffInMonths(now()) % 12) : 0;
                @endphp
                <strong>{{ $diffY }} Tahun {{ $diffM }} Bulan</strong>
            </td>
        </tr>
    </table>

    <!-- II. RIWAYAT KEPANGKATAN -->
    <div class="section-title">II. RIWAYAT KEPANGKATAN / GOLONGAN RUANG</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Pangkat / Golongan</th>
                <th style="width: 15%;">TMT</th>
                <th style="width: 35%;">Nomor & Tanggal SK</th>
                <th style="width: 20%;">Pejabat Penetap</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatPangkat as $rp)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>{{ $rp->golongan->nama_golongan ?? '-' }} ({{ $rp->golongan->kode_golongan ?? '-' }})</td>
                    <td style="text-align: center;">{{ $rp->tmt_pangkat ? \Carbon\Carbon::parse($rp->tmt_pangkat)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $rp->nomor_sk ?? '-' }} {{ $rp->tanggal_sk ? 'tgl ' . \Carbon\Carbon::parse($rp->tanggal_sk)->format('d-m-Y') : '' }}</td>
                    <td>{{ $rp->pejabat_penetap ?? 'Rektor / Menteri' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">Belum ada catatan riwayat pangkat tambahan tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- III. RIWAYAT JABATAN -->
    <div class="section-title">III. RIWAYAT JABATAN TERAKHIR</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Nama Jabatan</th>
                <th style="width: 15%;">TMT Jabatan</th>
                <th style="width: 40%;">Unit Kerja / Instansi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatJabatan as $rj)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>{{ $rj->jabatan->nama_jabatan ?? $rj->nama_jabatan ?? '-' }}</td>
                    <td style="text-align: center;">{{ $rj->tmt_jabatan ? \Carbon\Carbon::parse($rj->tmt_jabatan)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $rj->unitKerja->nama_unit ?? 'Fakultas Keperawatan UNRI' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b;">Menjabat sebagai {{ $pegawai->jabatan->nama_jabatan ?? '-' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- IV. RIWAYAT PENDIDIKAN FORMAL -->
    <div class="section-title">IV. RIWAYAT PENDIDIKAN FORMAL</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Jenjang</th>
                <th style="width: 40%;">Nama Lembaga / Universitas</th>
                <th style="width: 25%;">Jurusan / Program Studi</th>
                <th style="width: 15%;">Tahun Lulus</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatPendidikan as $pend)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td style="text-align: center; font-weight: bold;">{{ $pend->jenjang ?? '-' }}</td>
                    <td>{{ $pend->institusi ?? '-' }}</td>
                    <td>{{ $pend->jurusan ?? '-' }}</td>
                    <td style="text-align: center;">{{ $pend->tahun_lulus ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #64748b;">Pendidikan Terakhir: {{ $pegawai->pendidikan_terakhir ?? '-' }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- V. TANDA JASA / PENGHARGAAN SEBELUMNYA -->
    <div class="section-title">V. RIWAYAT TANDA JASA / PENGHARGAAN YANG PERNAH DITERIMA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 45%;">Nama Tanda Jasa / Penghargaan</th>
                <th style="width: 25%;">Instansi Pemberi</th>
                <th style="width: 25%;">Tahun Perolehan / No. SK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatPenghargaan as $peng)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>{{ $peng->nama_penghargaan ?? '-' }}</td>
                    <td>{{ $peng->instansi_pemberi ?? 'Presiden RI' }}</td>
                    <td>{{ $peng->tanggal_terima ? \Carbon\Carbon::parse($peng->tanggal_terima)->format('Y') : '-' }} {{ $peng->nomor_sk ? '(' . $peng->nomor_sk . ')' : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b;">
                        {{ $pegawai->satyalancana_terakhir ? 'Satyalancana Sebelumnya: ' . $pegawai->satyalancana_terakhir : 'Belum pernah menerima Satyalancana Karya Satya sebelumnya.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- VI. PERNYATAAN & PENGESAHAN DEKAN -->
    <div style="margin-top: 10px; font-size: 8pt; text-align: justify; line-height: 1.3;">
        Demikian Daftar Riwayat Hidup ini dibuat dengan sesungguhnya dan sebenar-benarnya untuk memenuhi kelengkapan berkas usulan penganugerahan Tanda Kehormatan <strong>Satyalancana Karya Satya {{ $kategori }}</strong>. Apabila dikemudian hari terdapat keterangan yang tidak benar, saya bersedia menerima segala konsekuensi hukum.
    </div>

    <table class="ttd-table">
        <tr>
            <td style="width: 35%; text-align: center;">
                <div class="qr-box">
                    <img src="{{ $qrCodeDataUri }}" class="qr-img" alt="QR Verifikasi">
                    <div style="font-size: 6.5pt; color: #64748b; margin-top: 2px;">
                        Pindai untuk validasi dokumen<br>
                        <strong>SIKAP FKP UNRI</strong>
                    </div>
                </div>
            </td>
            <td style="width: 25%;"></td>
            <td style="width: 40%; text-align: center;">
                Pekanbaru, {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}<br>
                <strong>Dekan Fakultas Keperawatan,</strong><br><br><br><br>
                <strong><u>{{ $pejabatDekan->nama_lengkap ?? 'Prof. Dr. Wan Nishfa Dewi, S.Kp., M.Ng.' }}</u></strong><br>
                NIP. {{ $pejabatDekan->nip ?? '197505232001122001' }}
            </td>
        </tr>
    </table>

</body>
</html>
