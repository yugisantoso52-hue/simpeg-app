<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawai', 'file_karpeg')) {
                $table->string('file_karpeg')->nullable()->after('karpeg_karis_karsu')->comment('Arsip Dokumen Fotocopy KARPEG');
            }
            if (!Schema::hasColumn('pegawai', 'file_pak')) {
                $table->string('file_pak')->nullable()->after('angka_kredit')->comment('Arsip Dokumen Penetapan Angka Kredit (PAK)');
            }
            if (!Schema::hasColumn('pegawai', 'nomor_pak')) {
                $table->string('nomor_pak', 100)->nullable()->after('file_pak')->comment('Nomor SK Penetapan Angka Kredit');
            }
            if (!Schema::hasColumn('pegawai', 'tanggal_pak')) {
                $table->date('tanggal_pak')->nullable()->after('nomor_pak')->comment('Tanggal Penetapan Angka Kredit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $colsToDrop = [];
            foreach (['file_karpeg', 'file_pak', 'nomor_pak', 'tanggal_pak'] as $col) {
                if (Schema::hasColumn('pegawai', $col)) {
                    $colsToDrop[] = $col;
                }
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
