<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah jabatan fungsional Dosen ke tabel master jabatan.
     * Menggunakan insertOrIgnore agar aman dijalankan berulang kali
     * (idempotent — tidak akan duplikat jika sudah ada).
     */
    public function up(): void
    {
        $jabatanDosen = [
            [
                'kode_jabatan' => 'DOC-AA',
                'nama_jabatan' => 'Asisten Ahli',
                'keterangan'   => 'Jabatan Fungsional Dosen — Asisten Ahli',
            ],
            [
                'kode_jabatan' => 'DOC-LK',
                'nama_jabatan' => 'Lektor',
                'keterangan'   => 'Jabatan Fungsional Dosen — Lektor',
            ],
            [
                'kode_jabatan' => 'DOC-LKP',
                'nama_jabatan' => 'Lektor Kepala',
                'keterangan'   => 'Jabatan Fungsional Dosen — Lektor Kepala',
            ],
            [
                'kode_jabatan' => 'DOC-GB',
                'nama_jabatan' => 'Guru Besar',
                'keterangan'   => 'Jabatan Fungsional Dosen — Guru Besar / Profesor',
            ],
        ];

        foreach ($jabatanDosen as $jabatan) {
            // Hanya insert jika nama_jabatan belum ada (cegah duplikat)
            $exists = DB::table('jabatan')
                ->where('nama_jabatan', $jabatan['nama_jabatan'])
                ->exists();

            if (!$exists) {
                DB::table('jabatan')->insert(array_merge($jabatan, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations — hapus jabatan fungsional dosen yang ditambahkan.
     */
    public function down(): void
    {
        $kodeDosen = ['DOC-AA', 'DOC-LK', 'DOC-LKP', 'DOC-GB'];
        DB::table('jabatan')->whereIn('kode_jabatan', $kodeDosen)->delete();
    }
};
