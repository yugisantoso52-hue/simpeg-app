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
        if (!Schema::hasTable('sync_document_outboxes')) {
            Schema::create('sync_document_outboxes', function (Blueprint $table) {
                $table->id();
                $table->string('idempotency_key', 255)->unique();
                $table->char('arsip_dokumen_uuid', 36)->index();
                $table->char('pegawai_sync_uuid', 36)->index();
                $table->string('local_file_path', 500);
                $table->unsignedBigInteger('file_size');
                $table->char('checksum', 64)->index();
                $table->string('status', 20)->default('PENDING')->index();
                $table->unsignedInteger('attempts')->default(0);
                $table->timestamp('locked_at')->nullable();
                $table->timestamp('retry_at')->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->text('last_error')->nullable();
                $table->timestamps();

                // Composite index supporting queue processing and retries
                $table->index(['status', 'retry_at', 'locked_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_document_outboxes');
    }
};
