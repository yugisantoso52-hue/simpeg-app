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
        Schema::create('employee_attendance_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('wfo_latitude', 10, 8)->nullable();
            $table->decimal('wfo_longitude', 11, 8)->nullable();
            $table->timestamp('wfo_locked_at')->nullable();
            $table->decimal('wfh_latitude', 10, 8)->nullable();
            $table->decimal('wfh_longitude', 11, 8)->nullable();
            $table->timestamp('wfh_locked_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendance_locations');
    }
};
