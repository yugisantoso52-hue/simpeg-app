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
        Schema::table('attendances', function (Blueprint $table) {
            // Pilar 1: Validasi Lokasi (Anti-Fake GPS & Network)
            $table->string('ip_address', 45)->nullable()->after('notes');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->float('gps_accuracy')->nullable()->after('user_agent');
            $table->float('gps_altitude')->nullable()->after('gps_accuracy');
            $table->float('gps_speed')->nullable()->after('gps_altitude');
            $table->boolean('is_mock_location')->default(false)->after('gps_speed');

            // Pilar 2: Verifikasi Identitas (Liveness & Face Verification)
            $table->boolean('liveness_verified')->default(false)->after('is_mock_location');
            $table->string('liveness_challenge', 50)->nullable()->after('liveness_verified');
            $table->float('face_similarity_score')->nullable()->after('liveness_challenge');

            // Pilar 3: Proteksi Integritas Perangkat (Anti-Titip Akun)
            $table->string('device_fingerprint', 64)->nullable()->index()->after('face_similarity_score');
            $table->string('device_platform', 100)->nullable()->after('device_fingerprint');
            $table->boolean('is_suspicious')->default(false)->index()->after('device_platform');
            $table->text('suspicious_reason')->nullable()->after('is_suspicious');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'gps_accuracy',
                'gps_altitude',
                'gps_speed',
                'is_mock_location',
                'liveness_verified',
                'liveness_challenge',
                'face_similarity_score',
                'device_fingerprint',
                'device_platform',
                'is_suspicious',
                'suspicious_reason',
            ]);
        });
    }
};
