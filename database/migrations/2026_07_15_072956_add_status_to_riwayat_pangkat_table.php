<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('riwayat_pangkat', function (Blueprint $table) {
            if (!Schema::hasColumn('riwayat_pangkat', 'status')) {
                $table->enum('status', ['aktif', 'nonaktif'])
                    ->default('aktif')
                    ->after('keterangan');
            }

            if (!Schema::hasColumn('riwayat_pangkat', 'tanggal_berakhir')) {
                $table->date('tanggal_berakhir')
                    ->nullable()
                    ->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('riwayat_pangkat', function (Blueprint $table) {
            $table->dropColumn(['status', 'tanggal_berakhir']);
        });
    }
};