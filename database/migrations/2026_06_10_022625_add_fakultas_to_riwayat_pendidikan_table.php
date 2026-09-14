<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hanya tambahkan kolom jika 'fakultas' BELUM ADA di tabel riwayat_pendidikan
        if (!Schema::hasColumn('riwayat_pendidikan', 'fakultas')) {
            Schema::table('riwayat_pendidikan', function (Blueprint $table) {
                $table->string('fakultas')
                      ->nullable()
                      ->after('institusi');
            });
        }
    }

    public function down(): void
    {
        // Hanya hapus kolom jika 'fakultas' MEMANG ADA di tabel riwayat_pendidikan
        if (Schema::hasColumn('riwayat_pendidikan', 'fakultas')) {
            Schema::table('riwayat_pendidikan', function (Blueprint $table) {
                $table->dropColumn('fakultas');
            });
        }
    }
};