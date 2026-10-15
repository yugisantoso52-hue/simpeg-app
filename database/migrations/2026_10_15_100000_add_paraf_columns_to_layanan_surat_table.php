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
        Schema::table('layanan_surat', function (Blueprint $table) {
            $table->timestamp('paraf_kabag_at')->nullable()->after('qr_token');
            $table->foreignId('paraf_kabag_by')->nullable()->constrained('pegawai')->nullOnDelete()->after('paraf_kabag_at');
            $table->timestamp('paraf_kapokja_at')->nullable()->after('paraf_kabag_by');
            $table->foreignId('paraf_kapokja_by')->nullable()->constrained('pegawai')->nullOnDelete()->after('paraf_kapokja_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layanan_surat', function (Blueprint $table) {
            $table->dropForeign(['paraf_kabag_by']);
            $table->dropForeign(['paraf_kapokja_by']);
            $table->dropColumn([
                'paraf_kabag_at',
                'paraf_kabag_by',
                'paraf_kapokja_at',
                'paraf_kapokja_by',
            ]);
        });
    }
};
