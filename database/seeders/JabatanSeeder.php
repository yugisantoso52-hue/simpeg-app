<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Jabatan;

class JabatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jabatans = [
            [
                'kode_jabatan' => 'JB001',
                'nama_jabatan' => 'Kepala Bagian Umum',
            ],
            [
                'kode_jabatan' => 'JB002',
                'nama_jabatan' => 'Ketua Pokja Keuangan dan Kepegawaian',
            ],
            [
                'kode_jabatan' => 'JB003',
                'nama_jabatan' => 'Ketua Pokja Umum dan Sarana Akademik',
            ],
            [
                'kode_jabatan' => 'JB004',
                'nama_jabatan' => 'Ketua Pokja Akademik dan Kemahasiswaan',
            ],
            [
                'kode_jabatan' => 'JB005',
                'nama_jabatan' => 'Pengolah Data dan Informasi',
            ],
            [
                'kode_jabatan' => 'JB006',
                'nama_jabatan' => 'Pustakawan Ahli Muda',
            ],
            [
                'kode_jabatan' => 'JB007',
                'nama_jabatan' => 'Pranata Laboratorium Pendidikan Ahli Muda',
            ],
            [
                'kode_jabatan' => 'JB008',
                'nama_jabatan' => 'Pengadministrasi Perkantoran',
            ],
            [
                'kode_jabatan' => 'JB009',
                'nama_jabatan' => 'Pranata Laboratorium Pendidikan Terampil',
            ],
            [
                'kode_jabatan' => 'JB010',
                'nama_jabatan' => 'Penata Layanan Operasional',
            ],
            [
                'kode_jabatan' => 'JB011',
                'nama_jabatan' => 'Pengelola Layanan Operasional',
            ],
            [
                'kode_jabatan' => 'JB012',
                'nama_jabatan' => 'Operator Layanan Operasional',
            ],
            [
                'kode_jabatan' => 'JB013',
                'nama_jabatan' => 'PHL',
            ],
            [
                'kode_jabatan' => 'JB014',
                'nama_jabatan' => 'Lain-lainnya',
            ],
        ];

        foreach ($jabatans as $jabatan) {
            Jabatan::updateOrCreate(
                ['kode_jabatan' => $jabatan['kode_jabatan']], // Kunci pengecekan keunikan
                ['nama_jabatan' => $jabatan['nama_jabatan']]   // Data yang disinkronkan
            );
        }
    }
}