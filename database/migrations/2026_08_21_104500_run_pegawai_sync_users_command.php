<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan perintah sinkronisasi user dan pembersihan NIP secara otomatis saat migrasi dideploy
        Artisan::call('pegawai:sync-users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
