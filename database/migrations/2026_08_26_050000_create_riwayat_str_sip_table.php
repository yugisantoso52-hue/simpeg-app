<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_str_sip')) {
            Schema::create('riwayat_str_sip', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->string('jenis_dokumen', 30); // STR, SIP, SIKP
                $table->string('nomor_registrasi', 100);
                $table->string('nama_dokumen', 150)->nullable();
                $table->string('instansi_penerbit', 150)->nullable();
                $table->date('tanggal_terbit');
                $table->date('tanggal_berakhir')->nullable();
                $table->boolean('is_seumur_hidup')->default(false);
                $table->string('status', 40)->default('Aktif'); // Aktif, Kedaluwarsa, Dalam Proses Perpanjangan
                $table->string('file_dokumen')->nullable();
                $table->text('keterangan')->nullable();
                $table->timestamps();

                $table->index(['pegawai_id', 'status']);
                $table->index('tanggal_berakhir');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_str_sip');
    }
};
