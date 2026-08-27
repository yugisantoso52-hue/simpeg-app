<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_pangkat')) {
            Schema::create('riwayat_pangkat', function (Blueprint $table) {
                $table->id();

                $table->foreignId('pegawai_id')
                    ->constrained('pegawai')
                    ->cascadeOnDelete();

                $table->foreignId('golongan_id')
                    ->constrained('golongan')
                    ->cascadeOnDelete();

                $table->date('tmt')->nullable();
                $table->string('nomor_sk')->nullable();
                $table->string('file_sk')->nullable(); // Arsip Dokumen SK
                $table->text('keterangan')->nullable();

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pangkat');
    }
};