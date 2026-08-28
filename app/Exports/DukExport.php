<?php

namespace App\Exports;

use App\Models\Pegawai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DukExport implements FromCollection, WithMapping, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $dosenList;
    protected $tendikList;
    protected $phlList;
    protected $headerRows = [];

    public function __construct(?string $search = null)
    {
        $dosenQuery = Pegawai::dosen()->with(['golongan', 'unitKerja', 'jabatan']);
        $tendikQuery = Pegawai::tendik()->with(['golongan', 'unitKerja', 'jabatan']);
        $phlQuery = Pegawai::phl()->with(['golongan', 'unitKerja', 'jabatan']);

        if ($search) {
            $filterSearch = function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nip', 'like', "%{$search}%")
                       ->orWhere('nidn_nuptk', 'like', "%{$search}%");
                });
            };
            $dosenQuery->where($filterSearch);
            $tendikQuery->where($filterSearch);
            $phlQuery->where($filterSearch);
        }

        $sortDuk = function($collection) {
            return $collection->sort(function($a, $b) {
                $golA = $a->golongan->urutan ?? $a->golongan_id ?? 0;
                $golB = $b->golongan->urutan ?? $b->golongan_id ?? 0;
                if ($golA !== $golB) return $golB <=> $golA;

                $tmtPangkatA = $a->tmt_pangkat_terakhir ? $a->tmt_pangkat_terakhir->timestamp : 0;
                $tmtPangkatB = $b->tmt_pangkat_terakhir ? $b->tmt_pangkat_terakhir->timestamp : 0;
                if ($tmtPangkatA !== $tmtPangkatB) return $tmtPangkatA <=> $tmtPangkatB;

                $tglLahirA = $a->tanggal_lahir ? $a->tanggal_lahir->timestamp : 0;
                $tglLahirB = $b->tanggal_lahir ? $b->tanggal_lahir->timestamp : 0;
                return $tglLahirA <=> $tglLahirB;
            })->values();
        };

        $this->dosenList = $sortDuk($dosenQuery->get());
        $this->tendikList = $sortDuk($tendikQuery->get());
        $this->phlList = $sortDuk($phlQuery->get());
    }

    public function collection()
    {
        $rows = collect();
        $currentRow = 2; // Data starts at row 2 (after heading)

        // ==========================================
        // 1. BAGIAN I: DOSEN / TENAGA PENDIDIK
        // ==========================================
        $this->headerRows[] = $currentRow;
        $rows->push(['I. DAFTAR URUT KEPANGKATAN DOSEN / TENAGA PENDIDIK (' . count($this->dosenList) . ' ORANG)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $currentRow++;

        $no = 1;
        foreach ($this->dosenList as $row) {
            $rows->push($this->formatRow($no++, $row, 'Dosen'));
            $currentRow++;
        }

        // Pemisah
        $rows->push(array_fill(0, 17, ''));
        $currentRow++;

        // ==========================================
        // 2. BAGIAN II: TENAGA KEPENDIDIKAN (TENDIK)
        // ==========================================
        $this->headerRows[] = $currentRow;
        $rows->push(['II. DAFTAR URUT KEPANGKATAN TENAGA KEPENDIDIKAN / TENDIK (' . count($this->tendikList) . ' ORANG)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $currentRow++;

        $no = 1;
        foreach ($this->tendikList as $row) {
            $rows->push($this->formatRow($no++, $row, 'Tendik'));
            $currentRow++;
        }

        // Pemisah
        $rows->push(array_fill(0, 17, ''));
        $currentRow++;

        // ==========================================
        // 3. BAGIAN III: PEGAWAI HARIAN LEPAS (PHL)
        // ==========================================
        $this->headerRows[] = $currentRow;
        $rows->push(['III. DAFTAR URUT PEGAWAI HARIAN LEPAS (PHL) & TENAGA KONTRAK (' . count($this->phlList) . ' ORANG)', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $currentRow++;

        $no = 1;
        foreach ($this->phlList as $row) {
            $rows->push($this->formatRow($no++, $row, 'PHL'));
            $currentRow++;
        }

        // Pemisah
        $rows->push(array_fill(0, 17, ''));
        $currentRow++;

        // ==========================================
        // 4. REKAPITULASI TOTAL
        // ==========================================
        $this->headerRows[] = $currentRow;
        $rows->push(['REKAPITULASI TOTAL DUK', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['1. Dosen (Tenaga Pendidik)', count($this->dosenList) . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['2. Tendik (Tenaga Kependidikan)', count($this->tendikList) . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['3. PHL & Tenaga Kontrak', count($this->phlList) . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);
        $rows->push(['TOTAL KESELURUHAN PEGAWAI', (count($this->dosenList) + count($this->tendikList) + count($this->phlList)) . ' Orang', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '']);

        return $rows;
    }

    private function formatRow(int $no, Pegawai $row, string $kategori): array
    {
        $golongan   = $row->golongan->nama_golongan ?? '-';
        $pangkat    = $row->golongan->nama_pangkat ?? '';
        $golPangkat = $golongan . ($pangkat ? ' - ' . $pangkat : '');

        $tglMasuk           = $row->tanggal_masuk ? $row->tanggal_masuk->format('d/m/Y') : '-';
        $tmtSkPertama       = $row->tmt_sk_pertama ? $row->tmt_sk_pertama->format('d/m/Y') : '-';
        $tmtPangkatTerakhir = $row->tmt_pangkat_terakhir ? $row->tmt_pangkat_terakhir->format('d/m/Y') : '-';
        $tmtKgbTerakhir     = $row->tmt_kgb_terakhir ? $row->tmt_kgb_terakhir->format('d/m/Y') : '-';

        $tmtPangkatKedepan  = $row->kp_berikutnya_kalkulasi ? $row->kp_berikutnya_kalkulasi->format('d/m/Y') : '-';
        $tmtKgbKedepan      = $row->kgb_berikutnya_kalkulasi ? $row->kgb_berikutnya_kalkulasi->format('d/m/Y') : '-';

        return [
            'no'                   => $no,
            'nama'                 => $row->nama_lengkap ?? $row->nama,
            'nip'                  => "'" . $row->nip . ($row->nidn_nuptk ? ' (NIDN: ' . $row->nidn_nuptk . ')' : ''),
            'gol_pangkat'          => $golPangkat,
            'jabatan'              => $row->jabatan->nama_jabatan ?? '-',
            'unit_kerja'           => $row->unitKerja->nama_unit ?? '-',
            'pendidikan'           => $row->pendidikan_tampil,
            'kategori'             => $kategori,
            'tgl_masuk'            => $tglMasuk,
            'tmt_sk_pertama'       => $tmtSkPertama,
            'tmt_pangkat_terakhir' => $tmtPangkatTerakhir,
            'tmt_pangkat_kedepan'  => $tmtPangkatKedepan,
            'tmt_kgb_terakhir'     => $tmtKgbTerakhir,
            'tmt_kgb_kedepan'      => $tmtKgbKedepan,
            'masa_kerja'           => $row->masa_kerja_formatted,
            'satyalancana'         => $row->satyalancana_tampil,
            'status'               => $row->status_pegawai ?? 'Aktif',
        ];
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA PEGAWAI',
            'NIP / NIDN',
            'GOLONGAN / PANGKAT',
            'JABATAN',
            'UNIT KERJA',
            'PENDIDIKAN TERAKHIR',
            'KATEGORI PEGAWAI',
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
        $styles = [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']]],
        ];

        foreach ($this->headerRows as $rowIndex) {
            $styles[$rowIndex] = [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']],
            ];
        }

        return $styles;
    }
}