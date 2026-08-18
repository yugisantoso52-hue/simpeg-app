<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pendidikan', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnDelete();

            $table->string('jenjang');
            $table->string('institusi');
            $table->string('fakultas')->nullable();
            $table->string('jurusan')->nullable();
            $table->year('tahun_lulus')->nullable(); // FIX: Dibuat nullable agar tidak crash jika kosong
            $table->string('ijazah')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikan');
    }
};