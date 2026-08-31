<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar 14 tabel utama yang memerlukan sinkronisasi UUID & timestamp
     */
    protected array $tables = [
        'pegawai',
        'users',
        'riwayat_pendidikan',
        'riwayat_jabatan',
        'riwayat_pangkat',
        'riwayat_diklat',
        'mutasi_pegawai',
        'pengajuan_cuti',
        'tugas_belajar',
        'riwayat_str_sip',
        'riwayat_skp',
        'riwayat_penghargaan',
        'riwayat_organisasi',
        'riwayat_publikasi',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'sync_uuid')) {
                        $table->string('sync_uuid', 36)->nullable()->unique()->after('id');
                    }
                    if (!Schema::hasColumn($tableName, 'last_synced_at')) {
                        $table->timestamp('last_synced_at')->nullable()->after('updated_at');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $columnsToDrop = [];
                    if (Schema::hasColumn($tableName, 'sync_uuid')) {
                        $columnsToDrop[] = 'sync_uuid';
                    }
                    if (Schema::hasColumn($tableName, 'last_synced_at')) {
                        $columnsToDrop[] = 'last_synced_at';
                    }
                    if (!empty($columnsToDrop)) {
                        $table->dropColumn($columnsToDrop);
                    }
                });
            }
        }
    }
};
