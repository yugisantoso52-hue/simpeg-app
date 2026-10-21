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
        Schema::table('pengajuan_cuti', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_cuti', 'verifikasi_admin_at')) {
                $table->timestamp('verifikasi_admin_at')->nullable()->after('catatan_atasan_langsung');
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'verifikasi_admin_by')) {
                $table->foreignId('verifikasi_admin_by')->nullable()->after('verifikasi_admin_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'paraf_kapokja_at')) {
                $table->timestamp('paraf_kapokja_at')->nullable()->after('verifikasi_admin_by');
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'paraf_kapokja_by')) {
                $table->foreignId('paraf_kapokja_by')->nullable()->after('paraf_kapokja_at')->constrained('pegawai')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'paraf_kabag_at')) {
                $table->timestamp('paraf_kabag_at')->nullable()->after('paraf_kapokja_by');
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'paraf_kabag_by')) {
                $table->foreignId('paraf_kabag_by')->nullable()->after('paraf_kabag_at')->constrained('pegawai')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'catatan_verifikator')) {
                $table->text('catatan_verifikator')->nullable()->after('catatan_pimpinan');
            }
        });

        Schema::table('layanan_surat', function (Blueprint $table) {
            if (!Schema::hasColumn('layanan_surat', 'ttd_pimpinan_at')) {
                $table->timestamp('ttd_pimpinan_at')->nullable()->after('paraf_kabag_by');
            }
            if (!Schema::hasColumn('layanan_surat', 'ttd_pimpinan_by')) {
                $table->foreignId('ttd_pimpinan_by')->nullable()->after('ttd_pimpinan_at')->constrained('pegawai')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_cuti', function (Blueprint $table) {
            $table->dropForeign(['verifikasi_admin_by']);
            $table->dropForeign(['paraf_kapokja_by']);
            $table->dropForeign(['paraf_kabag_by']);
            $table->dropColumn([
                'verifikasi_admin_at',
                'verifikasi_admin_by',
                'paraf_kapokja_at',
                'paraf_kapokja_by',
                'paraf_kabag_at',
                'paraf_kabag_by',
                'catatan_verifikator',
            ]);
        });

        Schema::table('layanan_surat', function (Blueprint $table) {
            $table->dropForeign(['ttd_pimpinan_by']);
            $table->dropColumn(['ttd_pimpinan_at', 'ttd_pimpinan_by']);
        });
    }
};
