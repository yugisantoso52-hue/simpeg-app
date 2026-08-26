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
            'KARPEG / KARIS / KARSU',
            'NIDN / NUPTK',
            'Nama Lengkap',
            'Gelar Depan',
            'Gelar Belakang',
            'Tempat Lahir',
            'Tanggal Lahir (YYYY-MM-DD)',
            'Jenis Kelamin (L/P)',
            'Agama',
            'Pendidikan Terakhir (SD/SMP/SMA/D1/D2/D3/D4/S1/S2/S3/Profesi)',
            'Jenis Pegawai (Dosen/PNS/PPPK/PHL)',
            'Status ASN (ASN/Non ASN)',
            'ID Unit Kerja',
            'ID Jabatan',
            'ID Golongan',
            'MKG Tahun',
            'MKG Bulan',
            'Tanggal Masuk (YYYY-MM-DD)',
            'TMT SK Pertama (YYYY-MM-DD)',
            'TMT Pangkat Terakhir (YYYY-MM-DD)',
            'TMT KGB Terakhir (YYYY-MM-DD)',
            'Status Pegawai (Aktif/Tugas Belajar/Non Aktif/Pensiun)',
        ];
    }

    public function array(): array
    {
        return [
            [
                '198501012010011001', // NIP
                'C.123456',           // KARPEG
                '0001234567',         // NIDN
                'Dr. Budi Santoso',   // Nama Lengkap
                'Dr.',                // Gelar Depan
                'M.Si.',              // Gelar Belakang
                'Pekanbaru',          // Tempat Lahir
                '1985-01-01',         // Tanggal Lahir
                'L',                  // Jenis Kelamin
                'Islam',              // Agama
                'S3',                 // Pendidikan Terakhir
                'Dosen',              // Jenis Pegawai
                'ASN',                // Status ASN
                '1',                  // ID Unit Kerja
                '1',                  // ID Jabatan
                '1',                  // ID Golongan
                '15',                 // MKG Tahun
                '6',                  // MKG Bulan
                '2010-01-01',         // Tanggal Masuk
                '2010-01-01',         // TMT SK Pertama
                '2022-04-01',         // TMT Pangkat Terakhir
                '2024-04-01',         // TMT KGB Terakhir
                'Aktif',              // Status Pegawai
            ],
            [
                '199002022015022002', // NIP
                'D.654321',           // KARPEG
                '',                   // NIDN (Tendik kosong)
                'Siti Aminah',        // Nama Lengkap
                '',                   // Gelar Depan
                'S.Kep., Ns.',        // Gelar Belakang
                'Kampar',             // Tempat Lahir
                '1990-02-02',         // Tanggal Lahir
                'P',                  // Jenis Kelamin
                'Islam',              // Agama
                'S1',                 // Pendidikan Terakhir
                'PPPK',               // Jenis Pegawai
                'ASN',                // Status ASN
                '1',                  // ID Unit Kerja
                '2',                  // ID Jabatan
                '2',                  // ID Golongan
                '10',                 // MKG Tahun
                '0',                  // MKG Bulan
                '2015-02-01',         // Tanggal Masuk
                '2015-02-01',         // TMT SK Pertama
                '2023-10-01',         // TMT Pangkat Terakhir
                '2023-10-01',         // TMT KGB Terakhir
                'Aktif',              // Status Pegawai
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