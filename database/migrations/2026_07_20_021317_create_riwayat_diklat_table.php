<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_diklat', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnDelete();

            $table->string('nama_diklat');
            $table->enum('jenis_diklat', [
                'Struktural', 'Fungsional', 'Teknis',
                'Workshop', 'Seminar', 'Bimtek', 'Lainnya'
            ])->default('Teknis');

            $table->string('penyelenggara');
            $table->string('tempat')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_jam')->nullable();

            $table->string('nomor_sertifikat')->nullable();
            $table->date('tanggal_sertifikat')->nullable();
            $table->string('file_sertifikat')->nullable(); // File Sertifikat Digital

            $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_diklat');
    }
};