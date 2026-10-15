<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('layanan_surat', function (Blueprint $table) {
            // Drop unique index if exists
            try {
                $table->dropUnique('layanan_surat_qr_token_unique');
            } catch (\Throwable $e) {
                // Ignore if doesn't exist
            }
        });

        // Use raw SQL to reliably alter column to TEXT
        DB::statement('ALTER TABLE `layanan_surat` MODIFY `qr_token` TEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `layanan_surat` MODIFY `qr_token` VARCHAR(100) NULL');
        Schema::table('layanan_surat', function (Blueprint $table) {
            $table->unique('qr_token');
        });
    }
};
