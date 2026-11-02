<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Daftar Riwayat Hidup (Format Resmi BKN) - {{ $pegawai->nama_lengkap ?? $pegawai->nama }}</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 10mm 15mm 10mm 15mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 9pt;
            line-height: 1.25;
            color: #000;
        }

        /* KOP SURAT RESMI DINAS UNRI */
        .kop-surat-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #000000;
            padding-bottom: 4px;
            margin-bottom: 12px;
        }
        .kop-surat-table td {
            vertical-align: middle;
            padding: 0;
            border: none;
        }
        .kop-logo-cell {
            width: 24mm;
            text-align: left;
            vertical-align: middle;
        }
        .kop-logo-cell img {
            width: 22mm;
            height: 22mm;
            display: block;
        }
        .kop-text-cell {
            text-align: center;
            vertical-align: middle;
            padding-left: 4px;
            padding-right: 24mm;
        }
        .kop-text-cell .kop-kemdikti {
            margin: 0;
            font-size: 11pt;
            font-weight: normal;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.2px;
        }
        .kop-text-cell .kop-ptn {
            margin: 1.5px 0 0 0;
            font-size: 11.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.3px;
        }
        .kop-text-cell .kop-fakultas {
            margin: 1.5px 0 0 0;
            font-size: 11.5pt;
            font-weight: bold;
            font-family: 'Times New Roman', Times, serif;
            text-transform: uppercase;
            line-height: 1.15;
            letter-spacing: 0.3px;
        }
        .kop-text-cell .kop-alamat {
            margin: 2px 0 0 0;
            font-size: 8pt;
            font-family: 'Times New Roman', Times, serif;
            line-height: 1.2;
        }

        /* HEADER JUDUL */
        .title-box {
            text-align: center;
            margin-bottom: 12px;
        }
        .title-box h1 {
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin: 0;
            padding: 0;
            letter-spacing: 0.5px;
        }
        .title-box p {
            font-size: 8pt;
            font-style: italic;
            margin: 2px 0 0 0;
        }

        /* FOTO 3X4 */
        .foto-container {
            width: 75px;
            height: 100px;
            border: 1px solid #000;
            padding: 1px;
            text-align: center;
            vertical-align: middle;
            background: #fff;
        }
        .foto-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        /* SECTION HEADER BKN */
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            margin: 10px 0 4px 0;
            text-transform: uppercase;
        }

        /* TABEL FORMAT BKN */
        table.bkn-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-bottom: 8px;
        }
        table.bkn-table th, table.bkn-table td {
            border: 1px solid #000;
            padding: 3px 5px;
            vertical-align: middle;
        }
        table.bkn-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* PERNYATAAN & TTD */
        .declaration-text {
            font-size: 8.5pt;
            line-height: 1.35;
            margin-top: 10px;
            margin-bottom: 12px;
            text-align: justify;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
            font-size: 8.5pt;
        }
        .ttd-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }
    </style>
</head>
<body>

    {{-- KOP RESMI UNRI --}}
    @php
        $candidatePaths = [
            public_path('images/logo-unri-bw.png'),
            public_path('images/logo-unri-bw.jpg'),
            public_path('logo-unri-bw.png'),
            public_path('images/logo-unri.png'),
            public_path('logo-unri.png'),
        ];
        $foundLogo = null;
        foreach ($candidatePaths as $p) {
            if (file_exists($p)) {
                $foundLogo = $p;
                break;
            }
        }

        // Resolusi Foto Pegawai
        $fotoBase64 = null;
        if (!empty($pegawai->foto)) {
            $cleanFoto = ltrim(str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $pegawai->foto), DIRECTORY_SEPARATOR);
            $candidateFotoPaths = [
                storage_path('app/public/' . $cleanFoto),
                storage_path('app/' . $cleanFoto),
                public_path('storage/' . $cleanFoto),
                public_path($cleanFoto),
            ];
            foreach ($candidateFotoPaths as $fp) {
                if (file_exists($fp) && is_file($fp)) {
                    $mime = mime_content_type($fp) ?: 'image/jpeg';
                    $fotoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fp));
                    break;
                }
            }
        }

        $namaLengkap = $pegawai->nama_lengkap ?? $pegawai->nama;
    @endphp

    <table class="kop-surat-table">
        <tr>
            <td class="kop-logo-cell">
                @if($foundLogo)
                    @php
                        $mimeType = str_ends_with(strtolower($foundLogo), '.jpg') || str_ends_with(strtolower($foundLogo), '.jpeg') ? 'image/jpeg' : 'image/png';
                    @endphp
                    <img src="data:{{ $mimeType }};base64,{{ base64_encode(file_get_contents($foundLogo)) }}" alt="Logo UNRI">
                @else
                    <div style="font-size: 8pt; color: #666;">[LOGO]</div>
                @endif
            </td>
            <td class="kop-text-cell">
                <div class="kop-kemdikti">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div class="kop-ptn">UNIVERSITAS RIAU</div>
                <div class="kop-fakultas">FAKULTAS KEPERAWATAN</div>
                <div class="kop-alamat">
                    Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru 28293<br>
                    Laman : www.keperawatan.unri.ac.id &nbsp;&nbsp;|&nbsp;&nbsp; Email : keperawatan@unri.co.id
                </div>
            </td>
        </tr>
    </table>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px;">
        <tr>
            <td style="width: 80px;"></td>
            <td class="title-box">
                <h1>DAFTAR RIWAYAT HIDUP</h1>
                <p>Format Baku Standar Badan Kepegawaian Negara (Kepka BKN No. 11/2002)</p>
            </td>
            <td style="width: 80px; text-align: right; vertical-align: top;">
                <div class="foto-container" style="display: inline-block;">
                    @if($fotoBase64)
                        <img src="{{ $fotoBase64 }}" alt="Pas Foto">
                    @else
                        <div style="font-size: 7pt; color: #555; padding-top: 38px;">Pas Foto<br>3 x 4 cm</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    {{-- I. KETERANGAN PERORANGAN --}}
    <div class="section-title">I. KETERANGAN PERORANGAN</div>
    <table class="bkn-table">
        <tr>
            <td style="width: 5%;" class="text-center font-bold">1</td>
            <td style="width: 32%;">Nama Lengkap</td>
            <td style="width: 63%;" class="font-bold">{{ $namaLengkap }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">2</td>
            <td>NIP (Nomor Induk Pegawai)</td>
            <td class="font-bold font-mono">{{ $pegawai->nip ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">3</td>
            <td>Pangkat dan Golongan Ruang</td>
            <td>{{ $pegawai->golongan?->nama_pangkat ?? '-' }} / {{ $pegawai->golongan?->nama_golongan ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">4</td>
            <td>Tempat Lahir / Tanggal Lahir</td>
            <td>
                {{ $pegawai->tempat_lahir ?? '-' }}, 
                {{ $pegawai->tanggal_lahir ? (is_string($pegawai->tanggal_lahir) ? \Carbon\Carbon::parse($pegawai->tanggal_lahir)->translatedFormat('d F Y') : $pegawai->tanggal_lahir->translatedFormat('d F Y')) : '-' }}
            </td>
        </tr>
        <tr>
            <td class="text-center font-bold">5</td>
            <td>Jenis Kelamin</td>
            <td>{{ $pegawai->jenis_kelamin == 'L' ? 'Laki-laki' : ($pegawai->jenis_kelamin == 'P' ? 'Perempuan' : ($pegawai->jenis_kelamin ?? '-')) }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">6</td>
            <td>Agama</td>
            <td>{{ $pegawai->agama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">7</td>
            <td>Status Perkawinan</td>
            <td>{{ $pegawai->status_pernikahan ?? $pegawai->status_kawin ?? 'Kawin' }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">8</td>
            <td>Jabatan Terakhir / Unit Kerja</td>
            <td>{{ $pegawai->jabatan?->nama_jabatan ?? '-' }} / {{ $pegawai->unitKerja?->nama_unit ?? 'Fakultas Keperawatan Universitas Riau' }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">9</td>
            <td>Alamat Rumah Lengkap</td>
            <td>{{ $pegawai->alamat ?? $pegawai->alamat_domisili ?? 'Pekanbaru, Riau' }}</td>
        </tr>
        <tr>
            <td class="text-center font-bold">10</td>
            <td>Nomor Telepon / HP / Email</td>
            <td>{{ $pegawai->no_hp ?? $pegawai->telepon ?? '-' }} / {{ $pegawai->email ?? '-' }}</td>
        </tr>
    </table>

    {{-- II. PENDIDIKAN --}}
    <div class="section-title">II. PENDIDIKAN</div>
    <div style="font-size: 8.5pt; font-weight: bold; margin-bottom: 2px;">1. Pendidikan di Dalam dan Luar Negeri</div>
    <table class="bkn-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 15%;">Tingkat</th>
                <th style="width: 25%;">Nama Lembaga / Sekolah</th>
                <th style="width: 20%;">Jurusan / Prodi</th>
                <th style="width: 15%;">Tahun Lulus</th>
                <th style="width: 20%;">Nomor Ijazah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatPendidikan ?? [] as $pend)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-center font-bold">{{ $pend->jenjang ?? $pend->tingkat ?? '-' }}</td>
                    <td>{{ $pend->nama_institusi ?? $pend->nama_sekolah ?? '-' }}</td>
                    <td>{{ $pend->jurusan ?? '-' }}</td>
                    <td class="text-center">{{ $pend->tahun_lulus ?? '-' }}</td>
                    <td class="font-mono text-center">{{ $pend->nomor_ijazah ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="font-style: italic; color: #666;">Data riwayat pendidikan belum tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 8.5pt; font-weight: bold; margin-bottom: 2px; margin-top: 6px;">2. Kursus / Pelatihan / Diklat Teknis & Fungsional</div>
    <table class="bkn-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nama Kursus / Pelatihan</th>
                <th style="width: 15%;">Tahun / Tgl</th>
                <th style="width: 12%;">Jam / JP</th>
                <th style="width: 33%;">Penyelenggara / Tempat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatDiklat ?? [] as $diklat)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $diklat->nama_diklat ?? $diklat->nama_kursus ?? '-' }}</td>
                    <td class="text-center">{{ $diklat->tahun ?? ($diklat->tanggal_mulai ? $diklat->tanggal_mulai->format('Y') : '-') }}</td>
                    <td class="text-center">{{ $diklat->jumlah_jam ?? '-' }} JP</td>
                    <td>{{ $diklat->penyelenggara ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="font-style: italic; color: #666;">Data riwayat diklat / pelatihan belum tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- III. RIWAYAT PEKERJAAN --}}
    <div class="section-title">III. RIWAYAT PEKERJAAN & KEPANGKATAN</div>
    <div style="font-size: 8.5pt; font-weight: bold; margin-bottom: 2px;">1. Riwayat Kepangkatan dan Golongan Ruang</div>
    <table class="bkn-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 25%;">Pangkat / Golongan</th>
                <th style="width: 15%;">Berlaku TMT</th>
                <th style="width: 25%;">Nomor SK</th>
                <th style="width: 15%;">Tanggal SK</th>
                <th style="width: 15%;">Pejabat Penetap</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatPangkat ?? [] as $pangkat)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $pangkat->golongan?->nama_pangkat ?? $pangkat->nama_pangkat ?? '-' }} ({{ $pangkat->golongan?->nama_golongan ?? $pangkat->nama_golongan ?? '-' }})</td>
                    <td class="text-center">{{ $pangkat->tmt ? (is_string($pangkat->tmt) ? \Carbon\Carbon::parse($pangkat->tmt)->format('d-m-Y') : $pangkat->tmt->format('d-m-Y')) : '-' }}</td>
                    <td class="font-mono text-center">{{ $pangkat->nomor_sk ?? '-' }}</td>
                    <td class="text-center">{{ $pangkat->tanggal_sk ? (is_string($pangkat->tanggal_sk) ? \Carbon\Carbon::parse($pangkat->tanggal_sk)->format('d-m-Y') : $pangkat->tanggal_sk->format('d-m-Y')) : '-' }}</td>
                    <td>{{ $pangkat->pejabat_penetap ?? 'Rektor Universitas Riau' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="font-style: italic; color: #666;">Data riwayat kepangkatan belum tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 8.5pt; font-weight: bold; margin-bottom: 2px; margin-top: 6px;">2. Riwayat Jabatan / Pekerjaan</div>
    <table class="bkn-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Jabatan / Tugas</th>
                <th style="width: 15%;">TMT Jabatan</th>
                <th style="width: 25%;">Unit Kerja / Instansi</th>
                <th style="width: 20%;">Nomor & Tgl SK</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai->riwayatJabatan ?? [] as $jab)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $jab->nama_jabatan ?? $jab->jabatan?->nama_jabatan ?? '-' }}</td>
                    <td class="text-center">{{ $jab->tmt_jabatan ? (is_string($jab->tmt_jabatan) ? \Carbon\Carbon::parse($jab->tmt_jabatan)->format('d-m-Y') : $jab->tmt_jabatan->format('d-m-Y')) : '-' }}</td>
                    <td>{{ $jab->unit_kerja ?? 'Fakultas Keperawatan UNRI' }}</td>
                    <td class="font-mono text-center">{{ $jab->nomor_sk ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="font-style: italic; color: #666;">Data riwayat jabatan belum tercatat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- IV. EVALUASI PRESTASI KINERJA (SKP) & TANDA JASA --}}
    <table style="width: 100%; border-collapse: collapse; margin-top: 4px;">
        <tr>
            <td style="width: 48%; vertical-align: top; padding-right: 2%;">
                <div class="section-title">IV. PENILAIAN PRESTASI KERJA (SKP)</div>
                <table class="bkn-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Tahun</th>
                            <th style="width: 40%;">Predikat Kinerja</th>
                            <th style="width: 40%;">Pejabat Penilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawai->riwayatSkp ?? [] as $skp)
                            <tr>
                                <td class="text-center font-bold">{{ $skp->tahun }}</td>
                                <td class="text-center">{{ $skp->predikat_kinerja ?? $skp->nilai_prestasi ?? 'Baik' }}</td>
                                <td>{{ $skp->pejabat_penilai_nama ?? 'Dekan / Atasan Langsung' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center" style="font-style: italic; color: #666;">Belum ada data SKP tahunan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td style="width: 48%; vertical-align: top; padding-left: 2%;">
                <div class="section-title">V. TANDA JASA / PENGHARGAAN</div>
                <table class="bkn-table">
                    <thead>
                        <tr>
                            <th style="width: 15%;">No</th>
                            <th style="width: 45%;">Nama Penghargaan</th>
                            <th style="width: 15%;">Tahun</th>
                            <th style="width: 25%;">Instansi Pemberi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pegawai->riwayatPenghargaan ?? [] as $award)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>{{ $award->nama_penghargaan ?? '-' }}</td>
                                <td class="text-center font-bold">{{ $award->tahun ?? '-' }}</td>
                                <td>{{ $award->instansi_pemberi ?? 'Presiden RI / Rektor' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center" style="font-style: italic; color: #666;">Belum ada riwayat tanda jasa / penghargaan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    {{-- PERNYATAAN BKN & LEGALITAS PENGESAHAN --}}
    <div class="declaration-text">
        Demikian Daftar Riwayat Hidup ini saya buat dengan sesungguhnya dan apabila di kemudian hari terdapat keterangan yang tidak benar, saya bersedia dituntut di muka pengadilan serta bersedia menerima segala tindakan yang diambil oleh Pemerintah sesuai ketentuan peraturan perundang-undangan yang berlaku.
    </div>

    <table class="ttd-table">
        <tr>
            <td style="width: 48%;">
                Mengetahui,<br>
                <strong>Dekan Fakultas Keperawatan</strong><br>
                Universitas Riau,<br><br><br><br>
                <strong><u>{{ $pejabatPimpinan?->nama_lengkap ?? 'Prof. Dr. Ir. H. Aras Mulyadi, M.Sc / Pimpinan Fakultas' }}</u></strong><br>
                NIP. {{ $pejabatPimpinan?->nip ?? '196208151988031002' }}
            </td>
            <td style="width: 15%; text-align: center; vertical-align: middle;">
                @if(!empty($qrCodeDataUri))
                    <img src="{{ $qrCodeDataUri }}" style="width: 65px; height: 65px; display: block; margin: 0 auto;" alt="QR Code Keabsahan"><br>
                    <span style="font-size: 6.5pt; font-family: monospace; color: #444;">{{ $verifyCode ?? 'KEABSAHAN-DIGITAL' }}</span>
                @endif
            </td>
            <td style="width: 37%; text-align: left; padding-left: 20px;">
                Pekanbaru, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Yang Membuat Pernyataan,<br><br><br><br><br>
                <strong><u>{{ $namaLengkap }}</u></strong><br>
                NIP. {{ $pegawai->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
