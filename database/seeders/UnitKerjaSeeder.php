<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UnitKerja;

class UnitKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'kode_unit' => 'UK001',
                'nama_unit' => 'Akademik dan Kemahasiswaan',
            ],
            [
                'kode_unit' => 'UK002',
                'nama_unit' => 'Keuangan dan Kepegawaian',
            ],
            [
                'kode_unit' => 'UK003',
                'nama_unit' => 'Umum dan Sarana Akademik',
            ],
            [
                'kode_unit' => 'UK004',
                'nama_unit' => 'Lain-lainnya',
            ],
        ];

        foreach ($data as $item) {
            UnitKerja::updateOrCreate(
                [
                    'kode_unit' => $item['kode_unit'],
                ],
                [
                    'nama_unit' => $item['nama_unit'],
                ]
            );
        }
    }
}