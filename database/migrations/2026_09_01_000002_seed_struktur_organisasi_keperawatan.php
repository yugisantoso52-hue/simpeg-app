<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Data Unit Kerja / Program Studi berdasarkan Struktur Organisasi
        $unitKerjas = [
            [
                'nama_unit'  => 'Dekanat / Pimpinan Fakultas',
                'keterangan' => 'Unsur Pimpinan Fakultas Keperawatan UNRI',
            ],
            [
                'nama_unit'  => 'Senat Fakultas',
                'keterangan' => 'Badan Normatif dan Perwakilan Fakultas',
            ],
            [
                'nama_unit'  => 'Jurusan Preklinik Keperawatan',
                'keterangan' => 'Jurusan Preklinik Keperawatan Fakultas Keperawatan',
            ],
            [
                'nama_unit'  => 'Program Studi S1 Keperawatan',
                'keterangan' => 'Program Studi Sarjana Keperawatan (S1)',
            ],
            [
                'nama_unit'  => 'Program Studi S2 Keperawatan',
                'keterangan' => 'Program Studi Magister Keperawatan (S2)',
            ],
            [
                'nama_unit'  => 'Jurusan Klinik dan Komunitas',
                'keterangan' => 'Jurusan Klinik dan Komunitas Fakultas Keperawatan',
            ],
            [
                'nama_unit'  => 'Program Studi Profesi Ners',
                'keterangan' => 'Program Studi Pendidikan Profesi Ners',
            ],
            [
                'nama_unit'  => 'Laboratorium Keperawatan',
                'keterangan' => 'Unit Pelaksana Teknis Laboratorium Keperawatan',
            ],
            [
                'nama_unit'  => 'Bagian Umum',
                'keterangan' => 'Bagian Tata Usaha & Administrasi Umum Fakultas',
            ],
        ];

        foreach ($unitKerjas as $uk) {
            $exists = DB::table('unit_kerja')->where('nama_unit', $uk['nama_unit'])->exists();
            if (!$exists) {
                DB::table('unit_kerja')->insert([
                    'kode_unit'  => 'UK-' . strtoupper(substr(md5($uk['nama_unit']), 0, 6)),
                    'nama_unit'  => $uk['nama_unit'],
                    'keterangan' => $uk['keterangan'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Data Jabatan Struktural & Akademik berdasarkan Struktur Organisasi
        $jabatans = [
            // Pimpinan Fakultas (Dekanat)
            [
                'nama_jabatan' => 'Dekan',
                'keterangan'   => 'Pimpinan Tertinggi Fakultas Keperawatan',
            ],
            [
                'nama_jabatan' => 'Wakil Dekan Bidang Akademik',
                'keterangan'   => 'Wakil Dekan I Bidang Akademik',
            ],
            [
                'nama_jabatan' => 'Wakil Dekan Keuangan dan Umum',
                'keterangan'   => 'Wakil Dekan II Bidang Keuangan dan Umum',
            ],
            [
                'nama_jabatan' => 'Wakil Dekan Kemahasiswaan, Alumni dan Kerjasama',
                'keterangan'   => 'Wakil Dekan III Bidang Kemahasiswaan, Alumni dan Kerjasama',
            ],

            // Senat Fakultas
            [
                'nama_jabatan' => 'Ketua Senat Fakultas',
                'keterangan'   => 'Ketua Senat Fakultas Keperawatan',
            ],
            [
                'nama_jabatan' => 'Sekretaris Senat Fakultas',
                'keterangan'   => 'Sekretaris Senat Fakultas Keperawatan',
            ],

            // Jurusan Preklinik Keperawatan
            [
                'nama_jabatan' => 'Ketua Jurusan Preklinik Keperawatan',
                'keterangan'   => 'Ketua Jurusan Preklinik Keperawatan',
            ],
            [
                'nama_jabatan' => 'Sekretaris Jurusan Preklinik Keperawatan',
                'keterangan'   => 'Sekretaris Jurusan Preklinik Keperawatan',
            ],
            [
                'nama_jabatan' => 'Koordinator Program Studi S1 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi Sarjana (S1) Keperawatan',
            ],
            [
                'nama_jabatan' => 'Koordinator Program Studi S2 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi Magister (S2) Keperawatan',
            ],

            // Jurusan Klinik dan Komunitas
            [
                'nama_jabatan' => 'Ketua Jurusan Klinik dan Komunitas',
                'keterangan'   => 'Ketua Jurusan Klinik dan Komunitas',
            ],
            [
                'nama_jabatan' => 'Sekretaris Jurusan Klinik dan Komunitas',
                'keterangan'   => 'Sekretaris Jurusan Klinik dan Komunitas',
            ],
            [
                'nama_jabatan' => 'Koordinator Program Studi Profesi Ners',
                'keterangan'   => 'Koordinator Program Studi Pendidikan Profesi Ners',
            ],

            // Kelompok Jabatan Fungsional Dosen (KJFD)
            [
                'nama_jabatan' => 'Ketua Kelompok Jabatan Fungsional Dosen (KJFD)',
                'keterangan'   => 'Ketua Kelompok Jabatan Fungsional Dosen',
            ],
            [
                'nama_jabatan' => 'Dosen',
                'keterangan'   => 'Tenaga Pendidik / Fungsional Dosen',
            ],

            // Laboratorium
            [
                'nama_jabatan' => 'Kepala Laboratorium',
                'keterangan'   => 'Kepala Unit Pelaksana Laboratorium Keperawatan',
            ],
            [
                'nama_jabatan' => 'Kepala Ruang Laboratorium Keperawatan',
                'keterangan'   => 'Penanggung Jawab Ruang Laboratorium Keperawatan',
            ],
        ];

        foreach ($jabatans as $jb) {
            $exists = DB::table('jabatan')->where('nama_jabatan', $jb['nama_jabatan'])->exists();
            if (!$exists) {
                DB::table('jabatan')->insert([
                    'kode_jabatan' => 'JB-' . strtoupper(substr(md5($jb['nama_jabatan']), 0, 6)),
                    'nama_jabatan' => $jb['nama_jabatan'],
                    'keterangan'   => $jb['keterangan'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }

        // 3. Tambahan Jenis Jabatan / KJFD sesuai bagan struktur
        $jenisJabatans = [
            [
                'nama_jenis_jabatan' => 'KJFD KLINIK',
                'keterangan'         => 'Kelompok Jabatan Fungsional Dosen Klinik (Jurusan Klinik & Komunitas)',
            ],
            [
                'nama_jenis_jabatan' => 'KJFD KOMUNITAS',
                'keterangan'         => 'Kelompok Jabatan Fungsional Dosen Komunitas (Jurusan Klinik & Komunitas)',
            ],
            [
                'nama_jenis_jabatan' => 'LAB - BIOMEDIK',
                'keterangan'         => 'Ruang Keperawatan Laboratorium Biomedik',
            ],
            [
                'nama_jenis_jabatan' => 'LAB - TUMBUH KEMBANG ANAK',
                'keterangan'         => 'Ruang Keperawatan Laboratorium Tumbuh Kembang Anak',
            ],
        ];

        foreach ($jenisJabatans as $jj) {
            $exists = DB::table('jenis_jabatan')->where('nama_jenis_jabatan', $jj['nama_jenis_jabatan'])->exists();
            if (!$exists) {
                DB::table('jenis_jabatan')->insert([
                    'nama_jenis_jabatan' => $jj['nama_jenis_jabatan'],
                    'keterangan'         => $jj['keterangan'],
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for seed data
    }
};
