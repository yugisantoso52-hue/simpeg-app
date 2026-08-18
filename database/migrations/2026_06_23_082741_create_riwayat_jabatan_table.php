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
        Schema::create('riwayat_jabatan', function (Blueprint $table) {
            $table->id();
            // Menghubungkan riwayat ke data induk pegawai
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
            
            // Menghubungkan ke tabel master jabatan & unit kerja
            $table->foreignId('jabatan_id')->constrained('jabatan')->onDelete('cascade');
            $table->foreignId('unit_kerja_id')->constrained('unit_kerja')->onDelete('cascade');
            
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->date('tmt_jabatan'); // Kolom TMT utama Anda
            $table->string('file_sk')->nullable(); // Ditambahkan agar bisa upload file SK
            $table->text('keterangan')->nullable(); // Ditambahkan untuk catatan tambahan
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_jabatan');
    }
};