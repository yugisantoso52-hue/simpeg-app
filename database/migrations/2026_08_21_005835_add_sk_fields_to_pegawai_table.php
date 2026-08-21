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
            $table->string('nomor_sk_pertama', 100)->nullable()->after('file_sk_pertama');
            $table->date('tanggal_sk_pertama')->nullable()->after('nomor_sk_pertama');
            $table->string('nomor_sk_pangkat_terakhir', 100)->nullable()->after('file_sk_pangkat_terakhir');
            $table->date('tanggal_sk_pangkat_terakhir')->nullable()->after('nomor_sk_pangkat_terakhir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn([
                'nomor_sk_pertama',
                'tanggal_sk_pertama',
                'nomor_sk_pangkat_terakhir',
                'tanggal_sk_pangkat_terakhir'
            ]);
        });
    }
};
