<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Golongan;

class GolonganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_golongan' => 'II/a',
                'nama_pangkat'  => 'Pengatur Muda',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'II/b',
                'nama_pangkat'  => 'Pengatur Muda Tingkat I',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'II/c',
                'nama_pangkat'  => 'Pengatur',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'II/d',
                'nama_pangkat'  => 'Pengatur Tingkat I',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'III/a',
                'nama_pangkat'  => 'Penata Muda',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'III/b',
                'nama_pangkat'  => 'Penata Muda Tingkat I',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'III/c',
                'nama_pangkat'  => 'Penata',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'III/d',
                'nama_pangkat'  => 'Penata Tingkat I',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'IV/a',
                'nama_pangkat'  => 'Pembina',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'IV/b',
                'nama_pangkat'  => 'Pembina Tingkat I',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'IV/c',
                'nama_pangkat'  => 'Pembina Utama Muda',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'IV/d',
                'nama_pangkat'  => 'Pembina Utama Madya',
                'keterangan'    => null,
            ],
            [
                'nama_golongan' => 'IV/e',
                'nama_pangkat'  => 'Pembina Utama',
                'keterangan'    => null,
            ],
        ];

        foreach ($data as $item) {
            Golongan::updateOrCreate(
                [
                    'nama_golongan' => $item['nama_golongan']
                ],
                [
                    'nama_pangkat' => $item['nama_pangkat'],
                    'keterangan'   => $item['keterangan']
                ]
            );
        }
    }
}