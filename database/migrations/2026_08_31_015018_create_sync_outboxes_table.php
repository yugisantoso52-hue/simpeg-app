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
        Schema::create('sync_outboxes', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 100);
            $table->unsignedBigInteger('record_id');
            $table->string('sync_uuid', 36);
            $table->string('action', 20); // INSERT, UPDATE, DELETE
            $table->json('payload')->nullable();
            $table->string('status', 30)->default('PENDING'); // PENDING, PROCESSING, FAILED
            $table->text('error_message')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamps();

            // Indeks untuk efisiensi kueri antrean sinkronisasi
            $table->index(['status', 'created_at']);
            $table->index(['table_name', 'record_id']);
            $table->index('sync_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sync_outboxes');
    }
};
