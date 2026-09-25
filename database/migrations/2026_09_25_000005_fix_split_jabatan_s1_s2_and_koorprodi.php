<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pisahkan jabatan "Koordinator Prodi S1/S2 Keperawatan" menjadi dua jabatan terpisah:
     *   - "Koordinator Prodi S1 Keperawatan"
     *   - "Koordinator Prodi S2 Keperawatan"
     *
     * Pisahkan "Dosen S1/S2 Keperawatan" menjadi:
     *   - "Dosen S1 Keperawatan"
     *   - "Dosen S2 Keperawatan"
     */
    public function up(): void
    {
        // --- 1. Pisahkan Koordinator Prodi ---
        $koorprodiGabung = DB::table('jabatan')->where('nama_jabatan', 'Koordinator Prodi S1/S2 Keperawatan')->first();

        $koorprodiS1Id = DB::table('jabatan')->where('nama_jabatan', 'Koordinator Prodi S1 Keperawatan')->value('id');
        if (!$koorprodiS1Id) {
            $koorprodiS1Id = DB::table('jabatan')->insertGetId([
                'kode_jabatan' => 'JB-KPS1-' . strtoupper(substr(md5('Koordinator Prodi S1 Keperawatan'), 0, 4)),
                'nama_jabatan' => 'Koordinator Prodi S1 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi S1 Keperawatan (Koorprodi S1)',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $koorprodiS2Id = DB::table('jabatan')->where('nama_jabatan', 'Koordinator Prodi S2 Keperawatan')->value('id');
        if (!$koorprodiS2Id) {
            $koorprodiS2Id = DB::table('jabatan')->insertGetId([
                'kode_jabatan' => 'JB-KPS2-' . strtoupper(substr(md5('Koordinator Prodi S2 Keperawatan'), 0, 4)),
                'nama_jabatan' => 'Koordinator Prodi S2 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi S2 (Magister) Keperawatan (Koorprodi S2)',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // Migrasi pegawai yang menggunakan jabatan gabungan → default ke S1 (admin bisa sesuaikan kemudian)
        if ($koorprodiGabung) {
            DB::table('pegawai')
                ->where('jabatan_id', $koorprodiGabung->id)
                ->update(['jabatan_id' => $koorprodiS1Id]);

            DB::table('riwayat_jabatan')
                ->where('jabatan_id', $koorprodiGabung->id)
                ->update(['jabatan_id' => $koorprodiS1Id]);

            // Hapus jabatan gabungan yang sudah tidak dipakai
            DB::table('jabatan')->where('id', $koorprodiGabung->id)->delete();
        }

        // --- 2. Pisahkan Dosen S1/S2 ---
        $dosenGabung = DB::table('jabatan')->where('nama_jabatan', 'Dosen S1/S2 Keperawatan')->first();

        $dosenS1Id = DB::table('jabatan')->where('nama_jabatan', 'Dosen S1 Keperawatan')->value('id');
        if (!$dosenS1Id) {
            $dosenS1Id = DB::table('jabatan')->insertGetId([
                'kode_jabatan' => 'JB-DS1-' . strtoupper(substr(md5('Dosen S1 Keperawatan'), 0, 5)),
                'nama_jabatan' => 'Dosen S1 Keperawatan',
                'keterangan'   => 'Dosen Fungsional Program Studi S1 Keperawatan',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $dosenS2Id = DB::table('jabatan')->where('nama_jabatan', 'Dosen S2 Keperawatan')->value('id');
        if (!$dosenS2Id) {
            $dosenS2Id = DB::table('jabatan')->insertGetId([
                'kode_jabatan' => 'JB-DS2-' . strtoupper(substr(md5('Dosen S2 Keperawatan'), 0, 5)),
                'nama_jabatan' => 'Dosen S2 Keperawatan',
                'keterangan'   => 'Dosen Fungsional Program Studi S2 (Magister) Keperawatan',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        if ($dosenGabung) {
            DB::table('pegawai')
                ->where('jabatan_id', $dosenGabung->id)
                ->update(['jabatan_id' => $dosenS1Id]);

            DB::table('riwayat_jabatan')
                ->where('jabatan_id', $dosenGabung->id)
                ->update(['jabatan_id' => $dosenS1Id]);

            DB::table('jabatan')->where('id', $dosenGabung->id)->delete();
        }

        // --- 3. Pastikan Jabatan Fungsional Dosen Profesi Ners ada ---
        $dosenNers = DB::table('jabatan')->where('nama_jabatan', 'Dosen Profesi Ners')->first();
        if (!$dosenNers) {
            DB::table('jabatan')->insert([
                'kode_jabatan' => 'JB-DN-' . strtoupper(substr(md5('Dosen Profesi Ners'), 0, 5)),
                'nama_jabatan' => 'Dosen Profesi Ners',
                'keterangan'   => 'Dosen Fungsional Program Studi Profesi Ners',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        // --- 4. Pastikan Jabatan Ketua Jurusan (Kajur) ada dengan nama lengkap ---
        $kajurPreklinik = DB::table('jabatan')->where('nama_jabatan', 'Ketua Jurusan Preklinik Keperawatan')->first();
        if (!$kajurPreklinik) {
            DB::table('jabatan')->insert([
                'kode_jabatan' => 'JB-KJPK-' . strtoupper(substr(md5('Ketua Jurusan Preklinik Keperawatan'), 0, 3)),
                'nama_jabatan' => 'Ketua Jurusan Preklinik Keperawatan',
                'keterangan'   => 'Ketua Jurusan Preklinik Keperawatan (Mengawasi Prodi S1 & S2)',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }

        $kajurKlinik = DB::table('jabatan')->where('nama_jabatan', 'Ketua Jurusan Klinik dan Komunitas')->first();
        if (!$kajurKlinik) {
            DB::table('jabatan')->insert([
                'kode_jabatan' => 'JB-KJKK-' . strtoupper(substr(md5('Ketua Jurusan Klinik dan Komunitas'), 0, 3)),
                'nama_jabatan' => 'Ketua Jurusan Klinik dan Komunitas',
                'keterangan'   => 'Ketua Jurusan Klinik dan Komunitas (Mengawasi Prodi Ners)',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    /**
     * Reverse: kembalikan jabatan gabungan (best effort, tidak mengembalikan relasi)
     */
    public function down(): void
    {
        // Re-insert jabatan gabungan jika belum ada
        if (!DB::table('jabatan')->where('nama_jabatan', 'Koordinator Prodi S1/S2 Keperawatan')->exists()) {
            DB::table('jabatan')->insert([
                'kode_jabatan' => 'JB-KPGAB',
                'nama_jabatan' => 'Koordinator Prodi S1/S2 Keperawatan',
                'keterangan'   => 'Koordinator Program Studi S1 atau S2 Keperawatan (gabungan rollback)',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
};
