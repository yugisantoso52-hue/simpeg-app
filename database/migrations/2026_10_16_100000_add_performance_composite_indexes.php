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
        $this->addIndexSafely('attendances', ['attendance_date', 'status'], 'idx_attendances_date_status');
        $this->addIndexSafely('attendances', ['user_id', 'attendance_date'], 'idx_attendances_user_date');

        $this->addIndexSafely('logbooks', ['pegawai_id', 'tanggal', 'status'], 'idx_logbooks_pegawai_tanggal_status');
        $this->addIndexSafely('logbooks', ['tanggal', 'status'], 'idx_logbooks_tanggal_status');

        $this->addIndexSafely('pegawai', ['status_pegawai', 'kp_berikutnya'], 'idx_pegawai_status_kp');
        $this->addIndexSafely('pegawai', ['status_pegawai', 'kgb_berikutnya'], 'idx_pegawai_status_kgb');
        $this->addIndexSafely('pegawai', ['status_pegawai', 'unit_kerja_id'], 'idx_pegawai_status_unit');

        $this->addIndexSafely('pengajuan_cuti', ['pegawai_id', 'jenis_cuti', 'status'], 'idx_cuti_pegawai_status');

        $this->addIndexSafely('talent_mappings', ['tahun', 'kuadran'], 'idx_tm_tahun_kuadran');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexSafely('attendances', 'idx_attendances_date_status');
        $this->dropIndexSafely('attendances', 'idx_attendances_user_date');
        $this->dropIndexSafely('logbooks', 'idx_logbooks_pegawai_tanggal_status');
        $this->dropIndexSafely('logbooks', 'idx_logbooks_tanggal_status');
        $this->dropIndexSafely('pegawai', 'idx_pegawai_status_kp');
        $this->dropIndexSafely('pegawai', 'idx_pegawai_status_kgb');
        $this->dropIndexSafely('pegawai', 'idx_pegawai_status_unit');
        $this->dropIndexSafely('pengajuan_cuti', 'idx_cuti_pegawai_status');
        $this->dropIndexSafely('talent_mappings', 'idx_tm_tahun_kuadran');
    }

    protected function addIndexSafely(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
        } catch (\Throwable $e) {
            // Abaikan jika index sudah ada
        }
    }

    protected function dropIndexSafely(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropIndex($indexName);
            });
        } catch (\Throwable $e) {
            // Abaikan jika index tidak ditemukan
        }
    }
};
