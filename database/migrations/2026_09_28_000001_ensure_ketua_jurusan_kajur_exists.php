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
        $exists = DB::table('jabatan')->where('nama_jabatan', 'Ketua Jurusan (Kajur)')->exists();
        if (!$exists) {
            DB::table('jabatan')->insert([
                'kode_jabatan' => 'JB-KAJUR',
                'nama_jabatan' => 'Ketua Jurusan (Kajur)',
                'keterangan'   => 'Ketua Jurusan Fakultas Keperawatan UNRI',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('jabatan')->where('nama_jabatan', 'Ketua Jurusan (Kajur)')->delete();
    }
};
