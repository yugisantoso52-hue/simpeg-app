<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Urut Kepangkatan (DUK) Pegawai</title>
    <style>
        @page {
            margin: 8mm 6mm;
        }
        body { font-family: sans-serif; font-size: 7.5px; color: #222; }
        .header { text-align: center; margin-bottom: 12px; }
        .header h2 { margin: 0; font-size: 12px; text-transform: uppercase; font-weight: bold; }
        .header h3 { margin: 2px 0 0; font-size: 9px; font-weight: normal; color: #444; }
        
        .section-header {
            margin-top: 14px;
            margin-bottom: 4px;
            padding: 4px 6px;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            border-left: 3px solid #1e3a8a;
            background-color: #f1f5f9;
        }
        
        table { width: 100%; border-collapse: collapse; margin-top: 2px; margin-bottom: 8px; }
        th, td { border: 1px solid #64748b; padding: 3px 2px; vertical-align: middle; word-wrap: break-word; }
        th { background-color: #e2e8f0; text-align: center; font-weight: bold; font-size: 7px; color: #1e293b; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        
        .rekap-container { margin-top: 12px; width: 35%; }
        .rekap-title { font-weight: bold; margin-bottom: 3px; font-size: 8px; text-transform: uppercase; }
        .page-break { page-break-before: always; }
    </style>
</head>
    <!-- KOP INSTANSI RESMI UNRI -->
    @php
        $candidatePaths = [
            public_path('images/logo-unri-bw.png'),
            public_path('images/logo-unri-bw.jpg'),
            public_path('logo-unri-bw.png'),
            public_path('images/logo-unri.png'),
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
    <table style="width: 100%; border-collapse: collapse; border-bottom: 2px solid #000; margin-bottom: 10px; padding-bottom: 3px;">
        <tr>
            <td style="width: 24mm; vertical-align: middle; border: none;">
                @if($foundLogo)
                    @php
                        $mimeType = str_ends_with(strtolower($foundLogo), '.jpg') || str_ends_with(strtolower($foundLogo), '.jpeg') ? 'image/jpeg' : 'image/png';
                    @endphp
                    <img src="data:{{ $mimeType }};base64,{{ base64_encode(file_get_contents($foundLogo)) }}" style="width: 22mm; height: 22mm; display: block;" alt="Logo UNRI">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-left: 5px; padding-right: 24mm; font-family: 'Times New Roman', Times, serif; border: none;">
                <div style="font-size: 12pt; text-transform: uppercase; line-height: 1.15;">KEMENTERIAN PENDIDIKAN TINGGI, SAINS, DAN TEKNOLOGI</div>
                <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; line-height: 1.15; margin-top: 1px;">UNIVERSITAS RIAU — FAKULTAS KEPERAWATAN</div>
                <div style="font-size: 8pt; line-height: 1.2; margin-top: 2px;">
                    Kampus Bina Widya Gedung Health Studies Complex KM. 12,5 Simpang Baru 28293 &nbsp;|&nbsp; Laman : www.keperawatan.unri.ac.id &nbsp;|&nbsp; Email : keperawatan@unri.co.id
                </div>
            </td>
        </tr>
    </table>

    <div class="header">
        <h2 style="font-size: 11px;">DAFTAR URUT KEPANGKATAN (DUK) PEGAWAI</h2>
        <h3 style="font-size: 8.5px;">FAKULTAS KEPERAWATAN UNIVERSITAS RIAU</h3>
    </div>

    {{-- ========================================================================= --}}
    {{-- I. DAFTAR URUT KEPANGKATAN DOSEN PNS                                      --}}
    {{-- ========================================================================= --}}
    <div class="section-header">
        I. DAFTAR URUT KEPANGKATAN (DUK) DOSEN PNS ({{ count($dosenPnsList) }} ORANG)
    </div>
    <table>
        <thead>
            <tr>
                <th width="2%">NO</th>
                <th width="14%">NAMA / NIP / NIDN</th>
                <th width="9%">GOL / PANGKAT</th>
                <th width="12%">JABATAN</th>
                <th width="10%">UNIT KERJA</th>
                <th width="7%">PENDIDIKAN</th>
                <th width="6%">TGL MASUK</th>
                <th width="10%">TMT PANGKAT<br>(Lama / Depan)</th>
                <th width="10%">TMT KGB<br>(Lama / Depan)</th>
                <th width="7%">MASA KERJA</th>
                <th width="7%">SATYALANCANA</th>
                <th width="6%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dosenPnsList as $row)
                @php
                    $tglMasuk = $row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-';
                    $tmtPangkatLama  = $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-';
                    $tmtPangkatDepan = $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-';
                    $tmtKgbLama      = $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-';
                    $tmtKgbDepan     = $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $row->nama_lengkap ?? $row->nama }}</strong><br>
                        NIP. {{ $row->nip ?? '-' }}
                        @if($row->nidn_nuptk)
                            <br><small style="color: #0369a1;">NIDN: {{ $row->nidn_nuptk }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ $row->golongan->nama_golongan ?? '-' }}</strong><br>
                        <small>{{ $row->golongan->nama_pangkat ?? '' }}</small>
                    </td>
                    <td>{{ $row->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ $row->pendidikan_tampil }}</td>
                    <td class="text-center">{{ $tglMasuk }}</td>
                    <td class="text-center">
                        {{ $tmtPangkatLama }}<br>
                        <strong>{{ $tmtPangkatDepan }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $tmtKgbLama }}<br>
                        <strong>{{ $tmtKgbDepan }}</strong>
                    </td>
                    <td class="text-center">{{ $row->masa_kerja_formatted }}</td>
                    <td class="text-center">{{ $row->satyalancana_tampil }}</td>
                    <td class="text-center">{{ $row->status_pegawai ?? 'Aktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data Dosen PNS.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ========================================================================= --}}
    {{-- II. DAFTAR URUT KEPANGKATAN DOSEN PPPK                                    --}}
    {{-- ========================================================================= --}}
    <div class="section-header" style="border-left-color: #0284c7;">
        II. DAFTAR URUT KEPANGKATAN (DUK) DOSEN PPPK ({{ count($dosenPppkList) }} ORANG)
    </div>
    <table>
        <thead>
            <tr>
                <th width="2%">NO</th>
                <th width="14%">NAMA / NIP / NIDN</th>
                <th width="9%">GOL / PANGKAT</th>
                <th width="12%">JABATAN</th>
                <th width="10%">UNIT KERJA</th>
                <th width="7%">PENDIDIKAN</th>
                <th width="6%">TGL MASUK</th>
                <th width="10%">TMT PANGKAT<br>(Lama / Depan)</th>
                <th width="10%">TMT KGB<br>(Lama / Depan)</th>
                <th width="7%">MASA KERJA</th>
                <th width="7%">SATYALANCANA</th>
                <th width="6%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dosenPppkList as $row)
                @php
                    $tglMasuk = $row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-';
                    $tmtPangkatLama  = $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-';
                    $tmtPangkatDepan = $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-';
                    $tmtKgbLama      = $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-';
                    $tmtKgbDepan     = $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $row->nama_lengkap ?? $row->nama }}</strong><br>
                        NIP. {{ $row->nip ?? '-' }}
                        @if($row->nidn_nuptk)
                            <br><small style="color: #0369a1;">NIDN: {{ $row->nidn_nuptk }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ $row->golongan->nama_golongan ?? '-' }}</strong><br>
                        <small>{{ $row->golongan->nama_pangkat ?? '' }}</small>
                    </td>
                    <td>{{ $row->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ $row->pendidikan_tampil }}</td>
                    <td class="text-center">{{ $tglMasuk }}</td>
                    <td class="text-center">
                        {{ $tmtPangkatLama }}<br>
                        <strong>{{ $tmtPangkatDepan }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $tmtKgbLama }}<br>
                        <strong>{{ $tmtKgbDepan }}</strong>
                    </td>
                    <td class="text-center">{{ $row->masa_kerja_formatted }}</td>
                    <td class="text-center">{{ $row->satyalancana_tampil }}</td>
                    <td class="text-center">{{ $row->status_pegawai ?? 'Aktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data Dosen PPPK.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ========================================================================= --}}
    {{-- III. DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PNS             --}}
    {{-- ========================================================================= --}}
    <div class="section-header" style="border-left-color: #059669;">
        III. DAFTAR URUT KEPANGKATAN (DUK) TENAGA KEPENDIDIKAN / TENDIK PNS ({{ count($tendikPnsList) }} ORANG)
    </div>
    <table>
        <thead>
            <tr>
                <th width="2%">NO</th>
                <th width="14%">NAMA / NIP</th>
                <th width="9%">GOL / PANGKAT</th>
                <th width="12%">JABATAN</th>
                <th width="10%">UNIT KERJA</th>
                <th width="7%">PENDIDIKAN</th>
                <th width="6%">TGL MASUK</th>
                <th width="10%">TMT PANGKAT<br>(Lama / Depan)</th>
                <th width="10%">TMT KGB<br>(Lama / Depan)</th>
                <th width="7%">MASA KERJA</th>
                <th width="7%">SATYALANCANA</th>
                <th width="6%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tendikPnsList as $row)
                @php
                    $tglMasuk = $row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-';
                    $tmtPangkatLama  = $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-';
                    $tmtPangkatDepan = $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-';
                    $tmtKgbLama      = $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-';
                    $tmtKgbDepan     = $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $row->nama_lengkap ?? $row->nama }}</strong><br>
                        NIP. {{ $row->nip ?? '-' }}
                    </td>
                    <td class="text-center">
                        <strong>{{ $row->golongan->nama_golongan ?? '-' }}</strong><br>
                        <small>{{ $row->golongan->nama_pangkat ?? '' }}</small>
                    </td>
                    <td>{{ $row->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ $row->pendidikan_tampil }}</td>
                    <td class="text-center">{{ $tglMasuk }}</td>
                    <td class="text-center">
                        {{ $tmtPangkatLama }}<br>
                        <strong>{{ $tmtPangkatDepan }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $tmtKgbLama }}<br>
                        <strong>{{ $tmtKgbDepan }}</strong>
                    </td>
                    <td class="text-center">{{ $row->masa_kerja_formatted }}</td>
                    <td class="text-center">{{ $row->satyalancana_tampil }}</td>
                    <td class="text-center">{{ $row->status_pegawai ?? 'Aktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data Tendik PNS.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ========================================================================= --}}
    {{-- IV. DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN (TENDIK) PPPK            --}}
    {{-- ========================================================================= --}}
    <div class="section-header" style="border-left-color: #0d9488;">
        IV. DAFTAR URUT KEPANGKATAN (DUK) TENAGA KEPENDIDIKAN / TENDIK PPPK ({{ count($tendikPppkList) }} ORANG)
    </div>
    <table>
        <thead>
            <tr>
                <th width="2%">NO</th>
                <th width="14%">NAMA / NIP</th>
                <th width="9%">GOL / PANGKAT</th>
                <th width="12%">JABATAN</th>
                <th width="10%">UNIT KERJA</th>
                <th width="7%">PENDIDIKAN</th>
                <th width="6%">TGL MASUK</th>
                <th width="10%">TMT PANGKAT<br>(Lama / Depan)</th>
                <th width="10%">TMT KGB<br>(Lama / Depan)</th>
                <th width="7%">MASA KERJA</th>
                <th width="7%">SATYALANCANA</th>
                <th width="6%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tendikPppkList as $row)
                @php
                    $tglMasuk = $row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-';
                    $tmtPangkatLama  = $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-';
                    $tmtPangkatDepan = $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-';
                    $tmtKgbLama      = $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-';
                    $tmtKgbDepan     = $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $row->nama_lengkap ?? $row->nama }}</strong><br>
                        NIP. {{ $row->nip ?? '-' }}
                    </td>
                    <td class="text-center">
                        <strong>{{ $row->golongan->nama_golongan ?? '-' }}</strong><br>
                        <small>{{ $row->golongan->nama_pangkat ?? '' }}</small>
                    </td>
                    <td>{{ $row->jabatan->nama_jabatan ?? '-' }}</td>
                    <td>{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ $row->pendidikan_tampil }}</td>
                    <td class="text-center">{{ $tglMasuk }}</td>
                    <td class="text-center">
                        {{ $tmtPangkatLama }}<br>
                        <strong>{{ $tmtPangkatDepan }}</strong>
                    </td>
                    <td class="text-center">
                        {{ $tmtKgbLama }}<br>
                        <strong>{{ $tmtKgbDepan }}</strong>
                    </td>
                    <td class="text-center">{{ $row->masa_kerja_formatted }}</td>
                    <td class="text-center">{{ $row->satyalancana_tampil }}</td>
                    <td class="text-center">{{ $row->status_pegawai ?? 'Aktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">Tidak ada data Tendik PPPK.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ========================================================================= --}}
    {{-- V. DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & KONTRAK                      --}}
    {{-- ========================================================================= --}}
    <div class="section-header" style="border-left-color: #d97706;">
        V. DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK ({{ count($phlList) }} ORANG)
    </div>
    <table>
        <thead>
            <tr>
                <th width="2%">NO</th>
                <th width="16%">NAMA / NIK / ID</th>
                <th width="14%">JENIS KONTRAK / JABATAN</th>
                <th width="14%">UNIT KERJA</th>
                <th width="8%">PENDIDIKAN</th>
                <th width="16%">PERIODE KONTRAK</th>
                <th width="10%">MASA KERJA</th>
                <th width="8%">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($phlList as $row)
                @php
                    $kontrakMulai   = $row->tanggal_kontrak_mulai ? $row->tanggal_kontrak_mulai->format('d/m/Y') : ($row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-');
                    $kontrakSelesai = $row->tanggal_kontrak_selesai ? $row->tanggal_kontrak_selesai->format('d/m/Y') : 'Aktif';
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $row->nama_lengkap ?? $row->nama }}</strong><br>
                        ID: {{ $row->nip ?? '-' }}
                    </td>
                    <td>
                        <strong>{{ $row->jabatan->nama_jabatan ?? 'Tenaga Penunjang / PHL' }}</strong><br>
                        <small style="color: #b45309;">{{ $row->jenis_kontrak ?? 'Kontrak Kerja' }}</small>
                    </td>
                    <td>{{ $row->unitKerja->nama_unit ?? '-' }}</td>
                    <td class="text-center">{{ $row->pendidikan_tampil }}</td>
                    <td class="text-center">{{ $kontrakMulai }} s/d {{ $kontrakSelesai }}</td>
                    <td class="text-center">{{ $row->masa_kerja_formatted }}</td>
                    <td class="text-center">{{ $row->status_pegawai ?? 'Aktif' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data PHL.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- REKAPITULASI TOTAL --}}
    <div class="rekap-container" style="width: 45%;">
        <div class="rekap-title">REKAPITULASI DUK PEGAWAI:</div>
        <table>
            <thead>
                <tr>
                    <th>KATEGORI PEGAWAI</th>
                    <th width="35%">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Dosen PNS (Tenaga Pendidik PNS)</td>
                    <td class="text-center font-bold">{{ count($dosenPnsList) }} Orang</td>
                </tr>
                <tr>
                    <td>Dosen PPPK (Tenaga Pendidik PPPK)</td>
                    <td class="text-center font-bold">{{ count($dosenPppkList) }} Orang</td>
                </tr>
                <tr>
                    <td>Tendik PNS (Tenaga Kependidikan PNS)</td>
                    <td class="text-center font-bold">{{ count($tendikPnsList) }} Orang</td>
                </tr>
                <tr>
                    <td>Tendik PPPK (Tenaga Kependidikan PPPK)</td>
                    <td class="text-center font-bold">{{ count($tendikPppkList) }} Orang</td>
                </tr>
                <tr>
                    <td>PHL & Tenaga Kontrak</td>
                    <td class="text-center font-bold">{{ count($phlList) }} Orang</td>
                </tr>
                <tr style="background-color: #f1f5f9; font-weight: bold;">
                    <td>TOTAL KESELURUHAN</td>
                    <td class="text-center">{{ count($dosenPnsList) + count($dosenPppkList) + count($tendikPnsList) + count($tendikPppkList) + count($phlList) }} Orang</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>