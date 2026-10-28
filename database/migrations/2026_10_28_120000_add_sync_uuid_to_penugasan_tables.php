<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kelompok_penugasan') && !Schema::hasColumn('kelompok_penugasan', 'sync_uuid')) {
            Schema::table('kelompok_penugasan', function (Blueprint $table) {
                $table->uuid('sync_uuid')->nullable()->unique()->after('id');
                $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            });
        }

        if (Schema::hasTable('pegawai_penugasan') && !Schema::hasColumn('pegawai_penugasan', 'sync_uuid')) {
            Schema::table('pegawai_penugasan', function (Blueprint $table) {
                $table->uuid('sync_uuid')->nullable()->unique()->after('id');
                $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            });
        }

        if (Schema::hasTable('pengajuan_jabatan') && !Schema::hasColumn('pengajuan_jabatan', 'sync_uuid')) {
            Schema::table('pengajuan_jabatan', function (Blueprint $table) {
                $table->uuid('sync_uuid')->nullable()->unique()->after('id');
                $table->timestamp('last_synced_at')->nullable()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kelompok_penugasan') && Schema::hasColumn('kelompok_penugasan', 'sync_uuid')) {
            Schema::table('kelompok_penugasan', function (Blueprint $table) {
                $table->dropColumn(['sync_uuid', 'last_synced_at']);
            });
        }

        if (Schema::hasTable('pegawai_penugasan') && Schema::hasColumn('pegawai_penugasan', 'sync_uuid')) {
            Schema::table('pegawai_penugasan', function (Blueprint $table) {
                $table->dropColumn(['sync_uuid', 'last_synced_at']);
            });
        }

        if (Schema::hasTable('pengajuan_jabatan') && Schema::hasColumn('pengajuan_jabatan', 'sync_uuid')) {
            Schema::table('pengajuan_jabatan', function (Blueprint $table) {
                $table->dropColumn(['sync_uuid', 'last_synced_at']);
            });
        }
    }
};
