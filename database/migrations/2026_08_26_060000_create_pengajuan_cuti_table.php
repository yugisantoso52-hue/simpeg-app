<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengajuan_cuti')) {
            Schema::create('pengajuan_cuti', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->string('jenis_cuti', 50); // Cuti Tahunan, Cuti Sakit, Cuti Melahirkan, Cuti Alasan Penting, Cuti Besar, Cuti di Luar Tanggungan Negara
                $table->string('nomor_surat', 100)->nullable();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->integer('jumlah_hari');
                $table->text('alasan');
                $table->text('alamat_selama_cuti')->nullable();
                $table->string('nomor_telepon', 30)->nullable();
                $table->string('file_lampiran')->nullable(); // Surat dokter, surat keterangan, dsb.
                $table->string('status', 40)->default('Menunggu Persetujuan'); // Menunggu Persetujuan, Disetujui, Ditolak, Dibatalkan
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('approved_at')->nullable();
                $table->text('catatan_pimpinan')->nullable();
                $table->timestamps();

                $table->index(['pegawai_id', 'status']);
                $table->index(['tanggal_mulai', 'tanggal_selesai']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
    }
};
