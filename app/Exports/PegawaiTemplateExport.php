<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PegawaiTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'NIP',
            'NIK',
            'Nama Lengkap',
            'Gelar Depan',
            'Gelar Belakang',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)',
            'Agama',
            'Pendidikan',
            'Jenis Pegawai (PNS/PPPK/Honorer)',
            'Status ASN (ASN/Non ASN)',
            'ID Unit Kerja',
            'ID Jabatan',
            'ID Golongan',
            'Tanggal Masuk (YYYY-MM-DD)',
            'TMT SK Pertama (YYYY-MM-DD)',
            'TMT Pangkat Terakhir (YYYY-MM-DD)',
            'TMT KGB Terakhir (YYYY-MM-DD)',
            'Status Pegawai (Aktif/Pensiun)'
        ];
    }

    public function array(): array
    {
        return [
            [
                '198501012010011001',
                '1471010101850001',
                'Dr. Budi Santoso',
                'Dr.',
                'M.Si.',
                'Pekanbaru',
                '1985-01-01',
                'L',
                'Islam',
                'S3',
                'PNS',
                'ASN',
                '1',
                '1',
                '1',
                '2010-01-01',
                '2010-01-01',
                '2022-04-01',
                '2024-04-01',
                'Aktif'
            ],
            [
                '199002022015022002',
                '1471020202900002',
                'Siti Aminah',
                '',
                'S.Kep., Ns.',
                'Kampar',
                '1990-02-02',
                'P',
                'Islam',
                'S1',
                'PPPK',
                'ASN',
                '1',
                '2',
                '2',
                '2015-02-01',
                '2015-02-01',
                '2023-10-01',
                '2023-10-01',
                'Aktif'
            ]
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1E3A8A']
                ]
            ],
        ];
    }
}