<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawai', 'golongan_id')) {
                $table->foreignId('golongan_id')->nullable()->after('id')->constrained('golongan')->onDelete('set null');
            }
            if (!Schema::hasColumn('pegawai', 'tmt_pangkat')) {
                $table->date('tmt_pangkat')->nullable()->after('golongan_id');
            }
            if (!Schema::hasColumn('pegawai', 'kp_berikutnya')) {
                $table->date('kp_berikutnya')->nullable()->after('tmt_pangkat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropForeign(['golongan_id']);
            $table->dropColumn(['golongan_id', 'tmt_pangkat', 'kp_berikutnya']);
        });
    }
};