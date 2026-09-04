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
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('user_name')->nullable();
                $table->string('user_email')->nullable();
                $table->string('role_name')->nullable();
                $table->string('activity_type', 50)->index(); // LOGIN, LOGOUT, CREATE, UPDATE, DELETE, UPLOAD_FILE
                $table->string('subject_type', 100)->nullable()->index(); // Pegawai, PengajuanCuti, etc.
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->text('description');
                $table->json('changes')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['created_at', 'activity_type']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
