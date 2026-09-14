<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tables = [
            'pegawai',
            'users',
            'riwayat_jabatan',
            'riwayat_pangkat',
            'riwayat_pendidikan',
            'riwayat_diklat',
            'riwayat_str_sip',
            'tugas_belajar',
            'riwayat_skp',
            'riwayat_penghargaan',
            'riwayat_organisasi',
            'riwayat_publikasi',
            'mutasi_pegawai',
            'pengajuan_cuti',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'sync_uuid')) {
                $records = DB::table($table)
                    ->whereNull('sync_uuid')
                    ->orWhere('sync_uuid', '')
                    ->select('id')
                    ->get();

                foreach ($records as $row) {
                    DB::table($table)
                        ->where('id', $row->id)
                        ->update(['sync_uuid' => (string) Str::uuid()]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed
    }
};
