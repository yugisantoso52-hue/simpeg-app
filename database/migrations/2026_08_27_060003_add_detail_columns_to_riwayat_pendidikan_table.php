<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambah kolom detail ke tabel riwayat_pendidikan — SIMPEG Fak. Keperawatan UNRI
     * ---------------------------------------------------------------------------------
     * gelar       : Gelar akademik (S.Kep, M.Kep, dr., dll)
     * akreditasi  : Akreditasi program studi (A, B, C, Unggul, Baik Sekali, Baik)
     * tahun_masuk : Tahun masuk untuk hitung masa studi
     * ipk         : IPK skala 0.00 – 4.00
     */
    public function up(): void
    {
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {

            if (!Schema::hasColumn('riwayat_pendidikan', 'gelar')) {
                $table->string('gelar', 50)
                      ->nullable()
                      ->after('jurusan')
                      ->comment('Gelar akademik misal S.Kep, M.Kep, dr.');
            }

            if (!Schema::hasColumn('riwayat_pendidikan', 'akreditasi')) {
                $table->string('akreditasi', 20)
                      ->nullable()
                      ->after('gelar')
                      ->comment('A, B, C, Unggul, Baik Sekali, Baik');
            }

            if (!Schema::hasColumn('riwayat_pendidikan', 'tahun_masuk')) {
                $table->year('tahun_masuk')
                      ->nullable()
                      ->after('akreditasi')
                      ->comment('Tahun masuk untuk hitung masa studi');
            }

            if (!Schema::hasColumn('riwayat_pendidikan', 'ipk')) {
                $table->decimal('ipk', 3, 2)
                      ->nullable()
                      ->after('tahun_masuk')
                      ->comment('IPK skala 0.00 - 4.00');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pendidikan', function (Blueprint $table) {
            $columns = ['gelar', 'akreditasi', 'tahun_masuk', 'ipk'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('riwayat_pendidikan', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
