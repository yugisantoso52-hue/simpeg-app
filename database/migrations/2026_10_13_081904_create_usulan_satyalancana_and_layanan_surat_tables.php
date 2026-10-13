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
        // 1. Tabel Usulan Satyalancana Karya Satya
        if (!Schema::hasTable('usulan_satyalancana')) {
            Schema::create('usulan_satyalancana', function (Blueprint $table) {
                $table->id();
                $table->uuid('sync_uuid')->nullable()->unique();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->string('kategori', 50); // 10 Tahun, 20 Tahun, 30 Tahun
                $table->integer('tahun_usulan')->default(date('Y'));
                $table->string('nomor_surat_usulan', 100)->nullable();
                $table->string('status', 30)->default('diajukan'); // draft, diajukan, diverifikasi, disetujui, ditolak, selesai
                $table->text('catatan')->nullable();
                $table->string('file_drh')->nullable();
                $table->string('file_sk_cpns')->nullable();
                $table->string('file_sk_pangkat_terakhir')->nullable();
                $table->string('file_sk_jabatan_terakhir')->nullable();
                $table->string('file_skp_2_tahun')->nullable();
                $table->string('file_surat_pernyataan_tidak_hukdis')->nullable();
                $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('diverifikasi_at')->nullable();
                $table->timestamps();

                $table->index(['pegawai_id', 'status', 'tahun_usulan']);
            });
        }

        // 2. Tabel Layanan Surat Keterangan Pegawai Mandiri
        if (!Schema::hasTable('layanan_surat')) {
            Schema::create('layanan_surat', function (Blueprint $table) {
                $table->id();
                $table->uuid('sync_uuid')->nullable()->unique();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->string('jenis_surat', 100)->default('Surat Keterangan Aktif Bekerja');
                $table->string('nomor_surat', 100)->unique();
                $table->text('keperluan')->nullable(); // Misal: Persyaratan Pengajuan KPR Bank, Pembuatan Visa, Beasiswa, dsb.
                $table->date('tanggal_surat');
                $table->string('status', 30)->default('selesai'); // diajukan, disetujui, selesai, ditolak
                $table->string('penandatangan_nama', 150)->nullable();
                $table->string('penandatangan_nip', 50)->nullable();
                $table->string('penandatangan_jabatan', 150)->nullable();
                $table->string('qr_token', 100)->nullable()->unique();
                $table->text('catatan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['pegawai_id', 'jenis_surat', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan_surat');
        Schema::dropIfExists('usulan_satyalancana');
    }
};
