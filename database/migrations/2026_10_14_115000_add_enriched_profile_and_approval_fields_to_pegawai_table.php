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
        Schema::table('pegawai', function (Blueprint $table) {
            // 1. Identitas Kependudukan, Pajak, dan Jaminan Sosial
            if (!Schema::hasColumn('pegawai', 'nik')) {
                $table->string('nik', 20)->nullable()->after('nip');
            }
            if (!Schema::hasColumn('pegawai', 'npwp')) {
                $table->string('npwp', 30)->nullable()->after('karpeg_karis_karsu');
            }
            if (!Schema::hasColumn('pegawai', 'bpjs_kesehatan')) {
                $table->string('bpjs_kesehatan', 30)->nullable()->after('npwp');
            }
            if (!Schema::hasColumn('pegawai', 'bpjs_ketenagakerjaan')) {
                $table->string('bpjs_ketenagakerjaan', 30)->nullable()->after('bpjs_kesehatan');
            }

            // 2. Legalitas Profesi Kesehatan (STR & SIP Dosen / Nakes)
            if (!Schema::hasColumn('pegawai', 'nomor_str')) {
                $table->string('nomor_str', 50)->nullable()->after('nidn_nuptk');
            }
            if (!Schema::hasColumn('pegawai', 'tanggal_kadaluarsa_str')) {
                $table->date('tanggal_kadaluarsa_str')->nullable()->after('nomor_str');
            }
            if (!Schema::hasColumn('pegawai', 'file_str')) {
                $table->string('file_str', 255)->nullable()->after('tanggal_kadaluarsa_str');
            }
            if (!Schema::hasColumn('pegawai', 'nomor_sip')) {
                $table->string('nomor_sip', 50)->nullable()->after('file_str');
            }
            if (!Schema::hasColumn('pegawai', 'tanggal_kadaluarsa_sip')) {
                $table->date('tanggal_kadaluarsa_sip')->nullable()->after('nomor_sip');
            }
            if (!Schema::hasColumn('pegawai', 'file_sip')) {
                $table->string('file_sip', 255)->nullable()->after('tanggal_kadaluarsa_sip');
            }

            // 3. Hirarki Persetujuan Khusus (Verifikator Kinerja & Atasan Cuti)
            if (!Schema::hasColumn('pegawai', 'pejabat_penilai_id')) {
                $table->unsignedBigInteger('pejabat_penilai_id')->nullable()->after('atasan_id');
                $table->foreign('pejabat_penilai_id')->references('id')->on('pegawai')->nullOnDelete();
            }
            if (!Schema::hasColumn('pegawai', 'atasan_cuti_id')) {
                $table->unsignedBigInteger('atasan_cuti_id')->nullable()->after('pejabat_penilai_id');
                $table->foreign('atasan_cuti_id')->references('id')->on('pegawai')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            if (Schema::hasColumn('pegawai', 'atasan_cuti_id')) {
                $table->dropForeign(['atasan_cuti_id']);
                $table->dropColumn('atasan_cuti_id');
            }
            if (Schema::hasColumn('pegawai', 'pejabat_penilai_id')) {
                $table->dropForeign(['pejabat_penilai_id']);
                $table->dropColumn('pejabat_penilai_id');
            }

            $columns = [
                'nik', 'npwp', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan',
                'nomor_str', 'tanggal_kadaluarsa_str', 'file_str',
                'nomor_sip', 'tanggal_kadaluarsa_sip', 'file_sip',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('pegawai', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
