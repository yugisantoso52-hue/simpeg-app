<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambah kolom nilai ke tabel riwayat_skp — SIMPEG Fak. Keperawatan UNRI
     * -------------------------------------------------------------------------
     * nilai_skp      : Nilai SKP (0–100)
     * nilai_perilaku : Nilai Perilaku Kerja (0–100)
     * nilai_akhir    : Nilai Akhir Kinerja
     */
    public function up(): void
    {
        Schema::table('riwayat_skp', function (Blueprint $table) {

            if (!Schema::hasColumn('riwayat_skp', 'nilai_skp')) {
                $table->decimal('nilai_skp', 5, 2)
                      ->nullable()
                      ->after('predikat_kinerja')
                      ->comment('Nilai SKP (0-100)');
            }

            if (!Schema::hasColumn('riwayat_skp', 'nilai_perilaku')) {
                $table->decimal('nilai_perilaku', 5, 2)
                      ->nullable()
                      ->after('nilai_skp')
                      ->comment('Nilai Perilaku Kerja (0-100)');
            }

            if (!Schema::hasColumn('riwayat_skp', 'nilai_akhir')) {
                $table->decimal('nilai_akhir', 5, 2)
                      ->nullable()
                      ->after('nilai_perilaku')
                      ->comment('Nilai Akhir Kinerja');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_skp', function (Blueprint $table) {
            $columns = ['nilai_skp', 'nilai_perilaku', 'nilai_akhir'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('riwayat_skp', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
