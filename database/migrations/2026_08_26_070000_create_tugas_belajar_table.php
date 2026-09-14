<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('tugas_belajar')) {
            Schema::create('tugas_belajar', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->string('jenis_pengembangan', 40)->default('Tugas Belajar'); // Tugas Belajar, Izin Belajar
                $table->string('jenjang_studi', 30); // S2, S3, Spesialis, Subspesialis, Post Doctoral
                $table->string('program_studi', 150);
                $table->string('perguruan_tinggi', 150);
                $table->string('negara', 100)->default('Indonesia');
                $table->string('sumber_pembiayaan', 100); // Beasiswa BPI / Kemendikbud, Beasiswa LPDP, Beasiswa Pemda, Mandiri / Swadana, Lainnya
                $table->string('nama_sponsor', 150)->nullable();
                $table->string('nomor_sk', 100);
                $table->date('tanggal_sk')->nullable();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->integer('semester_berjalan')->default(1);
                $table->string('status_studi', 40)->default('Sedang Studi'); // Sedang Studi, Perpanjangan, Lulus, Dibatalkan / DO
                $table->string('file_sk')->nullable();
                $table->string('file_laporan_progress')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['pegawai_id', 'status_studi']);
                $table->index('tanggal_selesai');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tugas_belajar');
    }
};
