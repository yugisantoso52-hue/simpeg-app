<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('jenis_jabatan')) {
            Schema::create('jenis_jabatan', function (Blueprint $table) {
                $table->id();
                $table->string('nama_jenis_jabatan', 100)->unique();
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }

        // Data awal Jenis Jabatan Keperawatan
        $initialData = [
            [
                'nama_jenis_jabatan' => 'MEDIKAL BEDAH',
                'keterangan'         => 'Departemen Keperawatan Medikal Bedah (KMB)',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama_jenis_jabatan' => 'GAWAT DARURAT',
                'keterangan'         => 'Departemen Keperawatan Gawat Darurat & Kritis (KGD)',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama_jenis_jabatan' => 'MATERNITAS',
                'keterangan'         => 'Departemen Keperawatan Maternitas',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama_jenis_jabatan' => 'ANAK',
                'keterangan'         => 'Departemen Keperawatan Anak',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama_jenis_jabatan' => 'KELUARGA KOMUNITAS',
                'keterangan'         => 'Departemen Keperawatan Keluarga, Komunitas, dan Gerontik',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama_jenis_jabatan' => 'GERONTIK',
                'keterangan'         => 'Departemen Keperawatan Gerontik (Lansia)',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'nama_jenis_jabatan' => 'JIWA',
                'keterangan'         => 'Departemen Keperawatan Jiwa',
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ];

        foreach ($initialData as $item) {
            $exists = DB::table('jenis_jabatan')
                ->where('nama_jenis_jabatan', $item['nama_jenis_jabatan'])
                ->exists();

            if (!$exists) {
                DB::table('jenis_jabatan')->insert($item);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jenis_jabatan');
    }
};
