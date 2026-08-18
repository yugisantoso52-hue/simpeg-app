<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix riwayat_jabatan: tmt_jabatan was NOT NULL but validation allows nullable
        Schema::table('riwayat_jabatan', function (Blueprint $table) {
            $table->date('tmt_jabatan')->nullable()->change();
        });

        // Fix riwayat_diklat: penyelenggara, tanggal_mulai, tanggal_selesai were NOT NULL
        Schema::table('riwayat_diklat', function (Blueprint $table) {
            $table->string('penyelenggara')->nullable()->change();
            $table->date('tanggal_mulai')->nullable()->change();
            $table->date('tanggal_selesai')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_jabatan', function (Blueprint $table) {
            $table->date('tmt_jabatan')->nullable(false)->change();
        });

        Schema::table('riwayat_diklat', function (Blueprint $table) {
            $table->string('penyelenggara')->nullable(false)->change();
            $table->date('tanggal_mulai')->nullable(false)->change();
            $table->date('tanggal_selesai')->nullable(false)->change();
        });
    }
};
