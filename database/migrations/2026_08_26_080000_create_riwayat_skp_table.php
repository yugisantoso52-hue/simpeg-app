<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_skp')) {
            Schema::create('riwayat_skp', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->integer('tahun');
                $table->string('predikat_kinerja', 40)->nullable(); // Sangat Baik, Baik, Butuh Perbaikan, Kurang, Sangat Kurang
                $table->string('file_rencana_skp')->nullable();
                $table->string('file_evaluasi_skp')->nullable();
                $table->string('pejabat_penilai', 150)->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->unique(['pegawai_id', 'tahun']);
                $table->index('tahun');
                $table->index('predikat_kinerja');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_skp');
    }
};
