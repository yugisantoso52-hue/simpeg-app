<?php

namespace App\Exports;

use App\Models\Pegawai;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DukExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $no = 1;
    protected $pegawais;

    public function __construct()
    {
        // Pengurutan Bertingkat Resmi BKN: Jenis Pegawai -> Golongan (Desc) -> TMT Pangkat (Asc) -> Tanggal Lahir (Asc)
        $this->pegawais = Pegawai::with(['golongan', 'unitKerja', 'jabatan'])
            ->get()
            ->sort(function($a, $b) {
                // 1. Prioritas Jenis Pegawai (PNS -> PPPK -> Honorer)
                $mapJenis = ['PNS' => 1, 'PPPK' => 2, 'HONORER' => 3];
                $jenisA = $mapJenis[strtoupper($a->jenis_pegawai ?? '')] ?? 4;
                $jenisB = $mapJenis[strtoupper($b->jenis_pegawai ?? '')] ?? 4;
                if ($jenisA !== $jenisB) return $jenisA <=> $jenisB;

                // 2. Tingkat Golongan/Pangkat (Tertinggi ke Terendah)
                $golA = $a->golongan->urutan ?? $a->golongan_id ?? 0;
                $golB = $b->golongan->urutan ?? $b->golongan_id ?? 0;
                if ($golA !== $golB) return $golB <=> $golA;

                // 3. TMT Pangkat Terakhir (Yang lebih lama menjabat naik ke atas)
                $tmtPangkatA = $a->tmt_pangkat_terakhir ? $a->tmt_pangkat_terakhir->timestamp : 0;
                $tmtPangkatB = $b->tmt_pangkat_terakhir ? $b->tmt_pangkat_terakhir->timestamp : 0;
                if ($tmtPangkatA !== $tmtPangkatB) return $tmtPangkatA <=> $tmtPangkatB;

                // 4. Usia / Tanggal Lahir (Yang lebih tua di atas)
                $tglLahirA = $a->tanggal_lahir ? $a->tanggal_lahir->timestamp : 0;
                $tglLahirB = $b->tanggal_lahir ? $b->tanggal_lahir->timestamp : 0;
                return $tglLahirA <=> $tglLahirB;
            });
    }

    public function collection()
    {
        $rows = collect();

        // 1. Data Utama DUK
        foreach ($this->pegawais as $row) {
            $golongan   = $row->golongan->nama_golongan ?? '-';
            $pangkat    = $row->golongan->nama_pangkat ?? '';
            $golPangkat = $golongan . ($pangkat ? ' - ' . $pangkat : '');

            $tglMasuk           = $row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-';
            $tmtSkPertama       = $row->tmt_sk_pertama ? $row->tmt_sk_pertama->format('d/m/Y') : '-';
            $tmtPangkatTerakhir = $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-';
            $tmtKgbTerakhir     = $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-';

            $tmtPangkatKedepan  = $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-';
            $tmtKgbKedepan      = $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-';

            $rows->push([
                'no'                   => $this->no++,
                'nama'                 => $row->nama_lengkap ?? $row->nama,
                'nip'                  => "'" . $row->nip,
                'gol_pangkat'          => $golPangkat,
                'jabatan'              => $row->jabatan->nama_jabatan ?? '-',
                'unit_kerja'           => $row->unitKerja->nama_unit ?? '-',
                'pendidikan'           => $row->pendidikan_tampil,
                'jenis_pegawai'        => $row->jenis_pegawai ?? '-',
                'tgl_masuk'            => $tglMasuk,
                'tmt_sk_pertama'       => $tmtSkPertama,
                'tmt_pangkat_terakhir' => $tmtPangkatTerakhir,
                'tmt_pangkat_kedepan'  => $tmtPangkatKedepan,
                'tmt_kgb_terakhir'     => $tmtKgbTerakhir,
                'tmt_kgb_kedepan'      => $tmtKgbKedepan,
                'masa_kerja'           => $row->masa_kerja_formatted,
                'satyalancana'         => $row->satyalancana_tampil,
                'status'               => $row->status_pegawai ?? 'Aktif',
            ]);
        }

        // Baris Pemisah
        $rows->push(array_fill(0, 17, ''));

        // 2. Rekapitulasi
        $totalPns     = $this->pegawais->where('jenis_pegawai', 'PNS')->count();
        $totalPppk    = $this->pegawais->where('jenis_pegawai', 'PPPK')->count();
        $totalHonorer = $this->pegawais->where('jenis_pegawai', 'Honorer')->count();
        $totalSemua   = $this->pegawais->count();

        $rows->push(['', 'REKAPITULASI PEGAWAI', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['', '1. PNS', $totalPns . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['', '2. PPPK', $totalPppk . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['', '3. Honorer', $totalHonorer . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['', 'TOTAL PEGAWAI', $totalSemua . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA PEGAWAI',
            'NIP',
            'GOLONGAN / PANGKAT',
            'JABATAN',
            'UNIT KERJA',
            'PENDIDIKAN TERAKHIR',
            'JENIS PEGAWAI',
            'TANGGAL MASUK',
            'TMT SK PERTAMA',
            'TMT PANGKAT TERAKHIR',
            'TMT PANGKAT KEDEPAN',
            'TMT KGB TERAKHIR',
            'TMT KGB KEDEPAN',
            'MASA KERJA',
            'SATYALANCANA',
            'STATUS PEGAWAI'
        ];
    }

    public function map($row): array
    {
        return array_values($row);
    }

    public function styles(Worksheet $sheet)
    {
        $totalRows = $this->pegawais->count() + 1;

        return [
            1 => ['font' => ['bold' => true]],
            ($totalRows + 2) => ['font' => ['bold' => true]],
            ($totalRows + 6) => ['font' => ['bold' => true]],
        ];
    }
}