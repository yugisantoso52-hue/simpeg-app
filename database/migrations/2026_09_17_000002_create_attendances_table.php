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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('attendance_type', ['wfo', 'wfh'])->default('wfo');
            $table->date('attendance_date')->index();
            $table->timestamp('check_in_time');
            $table->decimal('check_in_latitude', 10, 8);
            $table->decimal('check_in_longitude', 11, 8);
            $table->float('check_in_distance_meters')->default(0);
            $table->string('check_in_photo_path');
            
            $table->timestamp('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->float('check_out_distance_meters')->nullable();
            $table->string('check_out_photo_path')->nullable();
            
            $table->enum('status', ['present', 'late', 'leave'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
