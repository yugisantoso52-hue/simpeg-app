<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // 1. Pastikan Unit Kerja "Fakultas Keperawatan" ada
            $unitFakultas = DB::table('unit_kerja')->where('nama_unit', 'Fakultas Keperawatan')->first();
            $unitFakultasId = $unitFakultas ? $unitFakultas->id : DB::table('unit_kerja')->insertGetId([
                'kode_unit'  => 'FKP-01',
                'nama_unit'  => 'Fakultas Keperawatan',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Pastikan Jabatan "Dosen" ada
            $jabatanDosen = DB::table('jabatan')->where('nama_jabatan', 'Dosen')->first();
            $jabatanDosenId = $jabatanDosen ? $jabatanDosen->id : DB::table('jabatan')->insertGetId([
                'kode_jabatan'  => 'JB-DOSEN',
                'nama_jabatan'  => 'Dosen',
                'keterangan'    => 'Tenaga Pendidik / Dosen Fakultas Keperawatan',
                'unit_kerja_id' => $unitFakultasId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // 3. Update seluruh Pegawai berkategori Dosen yang unit_kerja_id masih null
            DB::table('pegawai')
                ->where(function ($q) {
                    $q->where('jenis_pegawai', 'Dosen')
                      ->orWhereNotNull('nidn_nuptk');
                })
                ->whereNull('unit_kerja_id')
                ->update(['unit_kerja_id' => $unitFakultasId]);

            // 4. Update seluruh Pegawai berkategori Dosen yang jabatan_id masih null
            DB::table('pegawai')
                ->where(function ($q) {
                    $q->where('jenis_pegawai', 'Dosen')
                      ->orWhereNotNull('nidn_nuptk');
                })
                ->whereNull('jabatan_id')
                ->update(['jabatan_id' => $jabatanDosenId]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
