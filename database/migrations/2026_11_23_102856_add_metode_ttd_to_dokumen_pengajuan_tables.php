<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan opsi metode penandatanganan dokumen ('elektronik' vs 'manual')
     * pada seluruh tabel pengajuan kedinasan SIKAP FKP UNRI.
     */
    public function up(): void
    {
        $tables = [
            'pengajuan_cuti',
            'pengajuan_karir',
            'layanan_surat',
            'usulan_satyalancana',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'metode_ttd')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->string('metode_ttd', 20)
                        ->nullable()
                        ->default('elektronik')
                        ->after('status')
                        ->comment('Metode tanda tangan: elektronik (TTE QR) atau manual (tanda tangan basah)');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'pengajuan_cuti',
            'pengajuan_karir',
            'layanan_surat',
            'usulan_satyalancana',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'metode_ttd')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('metode_ttd');
                });
            }
        }
    }
};
