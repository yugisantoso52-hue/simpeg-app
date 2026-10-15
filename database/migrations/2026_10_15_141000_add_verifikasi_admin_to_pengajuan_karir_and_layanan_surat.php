<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengajuan_karir') && !Schema::hasColumn('pengajuan_karir', 'verifikasi_admin_at')) {
            Schema::table('pengajuan_karir', function (Blueprint $table) {
                $table->timestamp('verifikasi_admin_at')->nullable()->after('catatan_verifikator');
                $table->foreignId('verifikasi_admin_by')->nullable()->constrained('users')->nullOnDelete()->after('verifikasi_admin_at');
            });
        }

        if (Schema::hasTable('layanan_surat') && !Schema::hasColumn('layanan_surat', 'verifikasi_admin_at')) {
            Schema::table('layanan_surat', function (Blueprint $table) {
                $table->timestamp('verifikasi_admin_at')->nullable()->after('catatan');
                $table->foreignId('verifikasi_admin_by')->nullable()->constrained('users')->nullOnDelete()->after('verifikasi_admin_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pengajuan_karir') && Schema::hasColumn('pengajuan_karir', 'verifikasi_admin_at')) {
            Schema::table('pengajuan_karir', function (Blueprint $table) {
                $table->dropForeign(['verifikasi_admin_by']);
                $table->dropColumn(['verifikasi_admin_at', 'verifikasi_admin_by']);
            });
        }

        if (Schema::hasTable('layanan_surat') && Schema::hasColumn('layanan_surat', 'verifikasi_admin_at')) {
            Schema::table('layanan_surat', function (Blueprint $table) {
                $table->dropForeign(['verifikasi_admin_by']);
                $table->dropColumn(['verifikasi_admin_at', 'verifikasi_admin_by']);
            });
        }
    }
};
