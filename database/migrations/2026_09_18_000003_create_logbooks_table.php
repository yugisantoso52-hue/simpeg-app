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
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->uuid('sync_uuid')->nullable()->index();
            $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal')->index();
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->integer('durasi_menit')->default(0);
            $table->string('kategori_kegiatan', 100);
            $table->string('aktivitas', 255);
            $table->text('deskripsi_kegiatan');
            $table->string('output_kegiatan', 255)->nullable();
            $table->integer('jumlah_output')->default(1);
            $table->string('satuan_output', 50)->default('Kegiatan');
            $table->string('file_lampiran')->nullable();
            $table->enum('status', ['draft', 'diajukan', 'disetujui', 'perlu_revisi', 'ditolak'])->default('draft')->index();
            $table->text('catatan_atasan')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();

            $table->index(['pegawai_id', 'tanggal']);
            $table->index(['pegawai_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};
