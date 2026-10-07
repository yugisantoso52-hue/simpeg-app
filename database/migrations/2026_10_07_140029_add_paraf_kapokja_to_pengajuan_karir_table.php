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
            $table->timestamp('paraf_kapokja_at')->nullable()->after('catatan_verifikator');
            $table->foreignId('paraf_kapokja_by')->nullable()->constrained('pegawai')->nullOnDelete()->after('paraf_kapokja_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_karir', function (Blueprint $table) {
            $table->dropForeign(['paraf_kapokja_by']);
            $table->dropColumn(['paraf_kapokja_at', 'paraf_kapokja_by']);
        });
    }
};
