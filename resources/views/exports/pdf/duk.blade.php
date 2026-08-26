<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Urut Kepangkatan (DUK)</title>
    <style>
        @page {
            margin: 10mm 8mm;
        }
        body { font-family: sans-serif; font-size: 8px; color: #333; }
        .header { text-align: center; margin-bottom: 15px; text-transform: uppercase; }
        .header h2 { margin: 0; font-size: 13px; }
        .header h3 { margin: 3px 0 0; font-size: 10px; font-weight: normal; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #444; padding: 4px 3px; vertical-align: middle; word-wrap: break-word; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; font-size: 7.5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .rekap-container { margin-top: 15px; width: 30%; }
        .rekap-title { font-weight: bold; margin-bottom: 4px; font-size: 9px; }
    </style>
</head>
<body>

    <div class="header">
        <h2>DAFTAR URUT KEPANGKATAN (DUK) PEGAWAI</h2>
        <h3>SIMPEG ENTERPRISE</h3>
    </div>

    @php
        // Pengurutan Bertingkat Sesuai Standar BKN: Jenis Pegawai -> Golongan -> TMT Pangkat -> Tgl Lahir
        $sortedPegawais = $pegawais->sort(function($a, $b) {
            $mapJenis = ['PNS' => 1, 'PPPK' => 2, 'DOSEN' => 3, 'PHL' => 4, 'HONORER' => 4];
            $jenisA = $mapJenis[strtoupper($a->jenis_pegawai ?? '')] ?? 5;
            $jenisB = $mapJenis[strtoupper($b->jenis_pegawai ?? '')] ?? 5;
            if ($jenisA !== $jenisB) return $jenisA <=> $jenisB;

            $golA = $a->golongan->urutan ?? $a->golongan_id ?? 0;
            $golB = $b->golongan->urutan ?? $b->golongan_id ?? 0;
            if ($golA !== $golB) return $golB <=> $golA;

            $tmtPangkatA = $a->tmt_pangkat_terakhir ? $a->tmt_pangkat_terakhir->timestamp : 0;
            $tmtPangkatB = $b->tmt_pangkat_terakhir ? $b->tmt_pangkat_terakhir->timestamp : 0;
            if ($tmtPangkatA !== $tmtPangkatB) return $tmtPangkatA <=> $tmtPangkatB;

            $tglLahirA = $a->tanggal_lahir ? $a->tanggal_lahir->timestamp : 0;
            $tglLahirB = $b->tanggal_lahir ? $b->tanggal_lahir->timestamp : 0;
            return $tglLahirA <=> $tglLahirB;
        });

        // Hitung Rekapitulasi
        $totalPns     = $pegawais->where('jenis_pegawai', 'PNS')->count();
        $totalPppk    = $pegawais->where('jenis_pegawai', 'PPPK')->count();
        $totalDosen   = $pegawais->where('jenis_pegawai', 'Dosen')->count();
        $totalPhl     = $pegawais->whereIn('jenis_pegawai', ['PHL', 'Honorer'])->count();
        $totalLainnya = $pegawais->whereNotIn('jenis_pegawai', ['PNS', 'PPPK', 'Dosen', 'PHL', 'Honorer'])->count();
    @endphp

    <table>
        <thead>
            <tr>
                <th width="2%">NO</th>
                <th width="12%">NAMA / NIP</th>
                <th width="8%">GOL / PANGKAT</th>
                <th width="10%">JABATAN</th>
                <th width="9%">UNIT KERJA</th>
                <th width="7%">PENDIDIKAN</th>
                <th width="6%">TGL MASUK</th>
                <th width="6%">TMT SK 1</th>
                <th width="10%">TMT PANGKAT<br>(Lama / Depan)</th>
                <th width="10%">TMT KGB<br>(Lama / Depan)</th>
                <th width="7%">MASA KERJA</th>
                <th width="7%">SATYALANCANA</th>
                <th width="6%">STATUS</th>
            </tr>
        </thead>
        <tbody>
    @forelse($sortedPegawais as $row)
        @php
            $tglMasuk = $row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-';
            $tmtSk1   = $row->tmt_sk_pertama ? $row->tmt_sk_pertama->format('d/m/Y') : '-';
            
            $tmtPangkatLama  = $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-';
            $tmtPangkatDepan = $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-';
            
            $tmtKgbLama  = $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-';
            $tmtKgbDepan = $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-';
        @endphp
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>
                <strong>{{ $row->nama_lengkap ?? $row->nama }}</strong><br>
                <span style="color: #555;">NIP. {{ $row->nip ?? '-' }}</span>
            </td>
            <td class="text-center">
                {{ $row->golongan->nama_golongan ?? '-' }}<br>
                <small>{{ $row->golongan->nama_pangkat ?? '' }}</small>
            </td>
            <td>{{ $row->jabatan->nama_jabatan ?? '-' }}</td>
            <td>{{ $row->unitKerja->nama_unit ?? '-' }}</td>
            <td class="text-center">{{ $row->pendidikan_tampil }}</td>
            <td class="text-center">{{ $tglMasuk }}</td>
            
            {{-- TMT SK 1 + Penanda File --}}
            <td class="text-center">
                {{ $tmtSk1 }}
                @if(!empty($row->file_sk_pertama))
                    <br><small style="color: green;">(Ada SK)</small>
                @endif
            </td>
            
            {{-- TMT PANGKAT + Penanda File --}}
            <td class="text-center">
                <span style="color: #444;">Terakhir: {{ $tmtPangkatLama }}</span><br>
                <strong>Kedepan: {{ $tmtPangkatDepan }}</strong>
                @if(!empty($row->file_sk_pangkat_terakhir))
                    <br><small style="color: green;">(Ada SK)</small>
                @endif
            </td>
            
            {{-- TMT KGB + Penanda File --}}
            <td class="text-center">
                <span style="color: #444;">Terakhir: {{ $tmtKgbLama }}</span><br>
                <strong>Kedepan: {{ $tmtKgbDepan }}</strong>
                @if(!empty($row->file_sk_kgb_terakhir))
                    <br><small style="color: green;">(Ada SK)</small>
                @endif
            </td>
            
            <td class="text-center">{{ $row->masa_kerja_formatted }}</td>
            <td class="text-center">{{ $row->satyalancana_tampil }}</td>
            <td class="text-center">{{ $row->status_pegawai ?? 'Aktif' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="13" class="text-center">Data pegawai belum tersedia.</td>
        </tr>
    @endforelse
</tbody>
    </table>

    {{-- TABEL REKAPITULASI JUMLAH PEGAWAI --}}
    <div class="rekap-container">
        <div class="rekap-title">REKAPITULASI PEGAWAI:</div>
        <table>
            <thead>
                <tr>
                    <th>JENIS PEGAWAI</th>
                    <th width="35%">JUMLAH</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>PNS</td>
                    <td class="text-center">{{ $totalPns }} Orang</td>
                </tr>
                <tr>
                    <td>PPPK</td>
                    <td class="text-center">{{ $totalPppk }} Orang</td>
                </tr>
                <tr>
                    <td>Dosen</td>
                    <td class="text-center">{{ $totalDosen }} Orang</td>
                </tr>
                <tr>
                    <td>PHL</td>
                    <td class="text-center">{{ $totalPhl }} Orang</td>
                </tr>
                @if($totalLainnya > 0)
                <tr>
                    <td>Lainnya</td>
                    <td class="text-center">{{ $totalLainnya }} Orang</td>
                </tr>
                @endif
                <tr style="background-color: #f9f9f9; font-weight: bold;">
                    <td>TOTAL PEGAWAI</td>
                    <td class="text-center">{{ $pegawais->count() }} Orang</td>
                </tr>
            </tbody>
        </table>
    </div>

</body>
</html>