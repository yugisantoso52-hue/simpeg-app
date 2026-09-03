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
        if (!Schema::hasTable('arsip_dokumen')) {
            Schema::create('arsip_dokumen', function (Blueprint $table) {
                $table->id();
                $table->char('sync_uuid', 36)->unique();
                $table->char('pegawai_sync_uuid', 36)->index();
                $table->unsignedBigInteger('pegawai_id')->index();
                $table->string('jenis_dokumen', 50);
                $table->string('kategori', 50);
                $table->string('documentable_type', 100)->nullable();
                $table->unsignedBigInteger('documentable_id')->nullable()->index();
                $table->char('documentable_sync_uuid', 36)->nullable()->index();
                $table->string('nama_file_sistem', 255);
                $table->string('nama_file_asli', 255);
                $table->string('mime_type', 100);
                $table->unsignedBigInteger('ukuran_file');
                $table->char('checksum', 64)->index();
                $table->string('storage_driver', 30)->default('gdrive');
                $table->string('google_drive_file_id', 100)->nullable()->index();
                $table->string('google_drive_folder_id', 100)->nullable()->index();
                $table->string('status_sync', 20)->default('PENDING')->index();
                $table->text('sync_error')->nullable();
                $table->date('tanggal_dokumen')->nullable();
                $table->string('nomor_dokumen', 100)->nullable()->index();
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('created_by')->nullable()->index();
                $table->char('created_by_sync_uuid', 36)->nullable()->index();
                $table->boolean('is_active')->default(true);
                $table->softDeletes();
                $table->timestamps();

                // Composite indexes for performance optimization
                $table->index(['pegawai_id', 'jenis_dokumen']);
                $table->index(['documentable_type', 'documentable_id']);
                $table->index(['status_sync', 'created_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_dokumen');
    }
};
