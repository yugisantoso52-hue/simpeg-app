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
            $table->string('file_sk_pangkat_terakhir')->nullable()->after('file_sk_terakhir');
            $table->string('file_sk_kgb_terakhir')->nullable()->after('file_sk_pangkat_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_karir', function (Blueprint $table) {
            $table->dropColumn(['file_sk_pangkat_terakhir', 'file_sk_kgb_terakhir']);
        });
    }
};
