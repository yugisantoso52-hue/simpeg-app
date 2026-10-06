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
        Schema::create('pengajuan_karir', function (Blueprint $table) {
            $table->id();
            $table->uuid('sync_uuid')->nullable()->unique();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->enum('jenis_pengajuan', ['KGB', 'KP']);
            
            // Kolom Khusus Kenaikan Pangkat
            $table->string('periode_kp', 30)->nullable(); // Februari, April, Juni, Agustus, Oktober, Desember
            $table->integer('tahun_periode')->nullable();
            $table->foreignId('golongan_lama_id')->nullable()->constrained('golongan')->nullOnDelete();
            $table->foreignId('golongan_tujuan_id')->nullable()->constrained('golongan')->nullOnDelete();
            
            // Kolom Khusus KGB
            $table->decimal('gaji_pokok_lama', 15, 2)->nullable();
            $table->decimal('gaji_pokok_baru', 15, 2)->nullable();
            $table->date('tmt_lama')->nullable();
            $table->date('tmt_baru')->nullable();
            $table->integer('mkg_tahun')->default(0);
            $table->integer('mkg_bulan')->default(0);
            
            // Berkas Kelengkapan (File Upload)
            $table->string('file_sk_terakhir')->nullable(); // SK Pangkat atau SK KGB Terakhir
            $table->string('file_skp_1')->nullable();       // SKP Tahun N-1
            $table->string('file_skp_2')->nullable();       // SKP Tahun N-2
            $table->string('file_karpeg')->nullable();
            $table->string('file_pak')->nullable();          // Khusus Dosen / PLP
            $table->string('file_pendukung')->nullable();    // Berkas tambahan
            
            // Status & Approval Workflow
            // draft, diajukan, diverifikasi_kabag, diverifikasi_wd2, disetujui_dekan, ditolak, selesai
            $table->string('status', 30)->default('diajukan');
            $table->text('catatan_pegawai')->nullable();
            $table->text('catatan_verifikator')->nullable();
            
            // Tracking Paraf & Penandatanganan
            $table->timestamp('paraf_kabag_at')->nullable();
            $table->foreignId('paraf_kabag_by')->nullable()->constrained('pegawai')->nullOnDelete();
            
            $table->timestamp('paraf_wd2_at')->nullable();
            $table->foreignId('paraf_wd2_by')->nullable()->constrained('pegawai')->nullOnDelete();
            
            $table->timestamp('ttd_dekan_at')->nullable();
            $table->foreignId('ttd_dekan_by')->nullable()->constrained('pegawai')->nullOnDelete();
            
            $table->timestamps();
            
            // Indexing
            $table->index(['pegawai_id', 'jenis_pengajuan', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_karir');
    }
};
