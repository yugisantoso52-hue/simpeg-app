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
        Schema::table('riwayat_jabatan', function (Blueprint $table) {
            $table->string('eselon', 50)->nullable()->after('unit_kerja_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayat_jabatan', function (Blueprint $table) {
            $table->dropColumn('eselon');
        });
    }
};
