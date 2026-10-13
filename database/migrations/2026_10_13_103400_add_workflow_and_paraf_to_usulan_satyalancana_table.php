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
        Schema::table('usulan_satyalancana', function (Blueprint $table) {
            // Periode Pengusulan (cth: Periode Mei 2027, Periode Agustus 2027)
            $table->string('periode_usulan', 50)->nullable()->after('tahun_usulan');
            
            // Verifikasi Admin / Pengelola Kepegawaian
            $table->timestamp('verifikasi_admin_at')->nullable()->after('diverifikasi_at');
            $table->foreignId('verifikasi_admin_by')->nullable()->constrained('users')->nullOnDelete()->after('verifikasi_admin_at');
            
            // Paraf 1: Kepala Bagian Umum (Kabag)
            $table->timestamp('paraf_kabag_at')->nullable()->after('verifikasi_admin_by');
            $table->foreignId('paraf_kabag_by')->nullable()->constrained('pegawai')->nullOnDelete()->after('paraf_kabag_at');
            
            // Paraf 2: Ka Pokja Keu-Kepeg
            $table->timestamp('paraf_kapokja_at')->nullable()->after('paraf_kabag_by');
            $table->foreignId('paraf_kapokja_by')->nullable()->constrained('pegawai')->nullOnDelete()->after('paraf_kapokja_at');
            
            // Pengesahan / Tanda Tangan Pimpinan (Wakil Dekan II)
            $table->timestamp('ttd_wd2_at')->nullable()->after('paraf_kapokja_by');
            $table->foreignId('ttd_wd2_by')->nullable()->constrained('pegawai')->nullOnDelete()->after('ttd_wd2_at');
            
            // File Dokumen Bundel Kolektif / SK
            $table->string('file_surat_pengantar')->nullable()->after('file_surat_pernyataan_tidak_hukdis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usulan_satyalancana', function (Blueprint $table) {
            $table->dropForeign(['verifikasi_admin_by']);
            $table->dropForeign(['paraf_kabag_by']);
            $table->dropForeign(['paraf_kapokja_by']);
            $table->dropForeign(['ttd_wd2_by']);
            
            $table->dropColumn([
                'periode_usulan',
                'verifikasi_admin_at',
                'verifikasi_admin_by',
                'paraf_kabag_at',
                'paraf_kabag_by',
                'paraf_kapokja_at',
                'paraf_kapokja_by',
                'ttd_wd2_at',
                'ttd_wd2_by',
                'file_surat_pengantar',
            ]);
        });
    }
};
