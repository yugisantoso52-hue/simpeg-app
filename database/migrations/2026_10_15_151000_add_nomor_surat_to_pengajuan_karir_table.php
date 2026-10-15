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
        Schema::table('pengajuan_karir', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_karir', 'nomor_surat')) {
                $table->string('nomor_surat', 150)->nullable()->after('jenis_pengajuan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_karir', function (Blueprint $table) {
            if (Schema::hasColumn('pengajuan_karir', 'nomor_surat')) {
                $table->dropColumn('nomor_surat');
            }
        });
    }
};
