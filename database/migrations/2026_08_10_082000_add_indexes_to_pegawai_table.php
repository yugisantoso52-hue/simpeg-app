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
            $table->index('status_pegawai');
            $table->index('jenis_pegawai');
            $table->index('status_asn');
            $table->index('tmt_pangkat_terakhir');
            $table->index('kgb_berikutnya');
            $table->index('kp_berikutnya');
            $table->index('satyalancana_berikutnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropIndex(['status_pegawai']);
            $table->dropIndex(['jenis_pegawai']);
            $table->dropIndex(['status_asn']);
            $table->dropIndex(['tmt_pangkat_terakhir']);
            $table->dropIndex(['kgb_berikutnya']);
            $table->dropIndex(['kp_berikutnya']);
            $table->dropIndex(['satyalancana_berikutnya']);
        });
    }
};
