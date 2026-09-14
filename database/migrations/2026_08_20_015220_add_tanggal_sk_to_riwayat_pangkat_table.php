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
        Schema::table('riwayat_pangkat', function (Blueprint $table) {
            if (!Schema::hasColumn('riwayat_pangkat', 'tanggal_sk')) {
                $table->date('tanggal_sk')->nullable()->after('nomor_sk');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_pangkat', function (Blueprint $table) {
            if (Schema::hasColumn('riwayat_pangkat', 'tanggal_sk')) {
                $table->dropColumn('tanggal_sk');
            }
        });
    }
};
