<?php

namespace App\Exports;

use App\Repositories\Contracts\DashboardRepositoryInterface;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReminderExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected string $type;

    public function __construct(string $type = 'all')
    {
        $this->type = $type;
    }

    public function collection()
    {
        $repository = app(DashboardRepositoryInterface::class);
        $reminders = $repository->getReminder();

        $data = collect();
        $no = 1;

        $categories = [
            'kgb'          => 'Kenaikan Gaji Berkala (KGB)',
            'kp'           => 'Kenaikan Pangkat (KP)',
            'pensiun'      => 'Batas Usia Pensiun (BUP)',
            'satyalancana' => 'Satyalancana Karya Satya',
        ];

        foreach ($categories as $catKey => $catLabel) {
            if ($this->type !== 'all' && $this->type !== $catKey) {
                continue;
            }

            if (isset($reminders[$catKey])) {
                foreach ($reminders[$catKey] as $item) {
                    $data->push([
                        'no'              => $no++,
                        'kategori'        => $catLabel,
                        'nip'             => "'" . ($item->nip ?? '-'),
                        'nama'            => $item->nama_lengkap ?? $item->nama,
                        'unit_kerja'      => $item->unitKerja->nama_unit ?? '-',
                        'jabatan'         => $item->jabatan->nama_jabatan ?? '-',
                        'golongan'        => $item->golongan->nama_golongan ?? '-',
                        'tanggal_jatuh'   => $item->tanggal_kegiatan ?? '-',
                        'status'          => $item->status_pegawai ?? 'Aktif',
                    ]);
                }
            }
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kategori Pengingat',
            'NIP',
            'Nama Pegawai',
            'Unit Kerja',
            'Jabatan',
            'Pangkat / Golongan',
            'Tanggal Jatuh Tempo',
            'Status Pegawai',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF007A3D'],
                ],
            ],
        ];
    }
}