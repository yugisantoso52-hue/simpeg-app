<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations for FKp UNRI Organizational Structure Updates (Including Pokja & Approval Hierarchy).
     */
    public function up(): void
    {
        // 1. Data Unit Kerja Fakultas Keperawatan UNRI
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
                'nama_unit'  => 'Satuan Penjamin Mutu (SPMF) & GPM',
                'keterangan' => 'Unsur Penjaminan Mutu Fakultas & Prodi (GPM S1, S2, Ners)',
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
                'keterangan' => 'Unit Pelaksana Teknis Laboratorium Keperawatan (9 Ruang Lab)',
            ],
            [
                'nama_unit'  => 'Unit-Unit Fungsional',
                'keterangan' => 'Unit Etik, CBT, NEDU, P2M, Kerjasama, Konseling, Humas, PPID',
            ],
            [
                'nama_unit'  => 'Bagian Umum',
                'keterangan' => 'Bagian Umum & Tata Usaha Fakultas Keperawatan',
            ],
            [
                'nama_unit'  => 'Pokja Akademik dan Kemahasiswaan',
                'keterangan' => 'Kelompok Kerja Bidang Akademik dan Kemahasiswaan',
            ],
            [
                'nama_unit'  => 'Pokja Keuangan dan Kepepegawaian',
                'keterangan' => 'Kelompok Kerja Keuangan dan Kepepegawaian',
            ],
            [
                'nama_unit'  => 'Pokja Umum Sarana Akademik',
                'keterangan' => 'Kelompok Kerja Umum Sarana Akademik',
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

        // 2. Data Jabatan Sesuai Matriks Revisi Fakultas Keperawatan UNRI
        $jabatans = [
            // Pimpinan Fakultas (Dekanat)
            [
                'nama_jabatan' => 'Dekan',
                'keterangan'   => 'Pimpinan Tertinggi Fakultas Keperawatan UNRI',
            ],
            [
                'nama_jabatan' => 'Wakil Dekan I (Bid. Akademik)',
                'keterangan'   => 'Wakil Dekan Bidang Akademik (WD I)',
            ],
            [
                'nama_jabatan' => 'Wakil Dekan II (Bid. Keuangan dan Umum)',
                'keterangan'   => 'Wakil Dekan Bidang Keuangan dan Umum (WD II)',
            ],
            [
                'nama_jabatan' => 'Wakil Dekan III (Bid. Kemahasiswaan, Alumni dan Kerjasama)',
                'keterangan'   => 'Wakil Dekan Bidang Kemahasiswaan, Alumni dan Kerjasama (WD III)',
            ],

            // Pimpinan Tata Usaha & Pokja
            [
                'nama_jabatan' => 'Kepala Bagian Umum',
                'keterangan'   => 'Kepala Bagian Umum / Kabag TU Fakultas Keperawatan',
            ],
            [
                'nama_jabatan' => 'Ka Pokja Akademik',
                'keterangan'   => 'Kepala Pokja Akademik dan Kemahasiswaan',
            ],
            [
                'nama_jabatan' => 'Ka Pokja Keu-Kepeg',
                'keterangan'   => 'Kepala Pokja Keuangan dan Kepepegawaian',
            ],
            [
                'nama_jabatan' => 'Ka Pokja Umum Sarana Akademik',
                'keterangan'   => 'Kepala Pokja Umum Sarana Akademik',
            ],

            // Pimpinan Jurusan & Koorprodi
            [
                'nama_jabatan' => 'Ketua Jurusan Preklinik Keperawatan',
                'keterangan'   => 'Ketua Jurusan Preklinik Keperawatan',
            ],
            [
                'nama_jabatan' => 'Ketua Jurusan Klinik dan Komunitas',
                'keterangan'   => 'Ketua Jurusan Klinik dan Komunitas',
            ],
            [
                'nama_jabatan' => 'Koordinator Prodi S1/S2 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi S1 atau S2 Keperawatan',
            ],
            [
                'nama_jabatan' => 'Koordinator Prodi Ners',
                'keterangan'   => 'Koordinator Program Studi Pendidikan Profesi Ners',
            ],

            // Kepala Unit & Laboratorium
            [
                'nama_jabatan' => 'Kepala UPT Laboratorium Keperawatan',
                'keterangan'   => 'Kepala Laboratorium Keperawatan Fakultas',
            ],
            [
                'nama_jabatan' => 'Kepala Unit Fungsional',
                'keterangan'   => 'Kepala Unit Etik / CBT / NEDU / P2M / Konseling / Humas / PPID',
            ],

            // Fungsional Pegawai & Staff
            [
                'nama_jabatan' => 'Dosen S1/S2 Keperawatan',
                'keterangan'   => 'Dosen Fungsional Prodi S1 / S2 Keperawatan',
            ],
            [
                'nama_jabatan' => 'Dosen Profesi Ners',
                'keterangan'   => 'Dosen Fungsional Prodi Profesi Ners',
            ],
            [
                'nama_jabatan' => 'PLP / Laboran Lab',
                'keterangan'   => 'Pranata Laboratorium Pendidikan / Laboran',
            ],
            [
                'nama_jabatan' => 'Staff Pokja Akademik dan Kemahasiswaan',
                'keterangan'   => 'Staf Pelaksana Pokja Akademik dan Kemahasiswaan',
            ],
            [
                'nama_jabatan' => 'Staff Pokja Keu & Kepeg',
                'keterangan'   => 'Staf Pelaksana Pokja Keuangan dan Kepepegawaian',
            ],
            [
                'nama_jabatan' => 'Staff Pokja Umum Sarana Akademik',
                'keterangan'   => 'Staf Pelaksana Pokja Umum Sarana Akademik',
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
