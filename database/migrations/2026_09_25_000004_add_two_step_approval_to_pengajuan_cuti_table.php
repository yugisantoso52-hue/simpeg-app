<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for 2-Step Cuti Approval (Pertimbangan Atasan Langsung + Keputusan PYBMC PerBKN 7/2022).
     */
    public function up(): void
    {
        Schema::table('pengajuan_cuti', function (Blueprint $table) {
            if (!Schema::hasColumn('pengajuan_cuti', 'atasan_langsung_id')) {
                $table->foreignId('atasan_langsung_id')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'pertimbangan_atasan')) {
                $table->string('pertimbangan_atasan', 50)->nullable()->after('atasan_langsung_id'); // Disetujui, Perubahan, Ditangguhkan, Ditolak
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'catatan_atasan_langsung')) {
                $table->text('catatan_atasan_langsung')->nullable()->after('pertimbangan_atasan');
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'pertimbangan_atasan_at')) {
                $table->dateTime('pertimbangan_atasan_at')->nullable()->after('catatan_atasan_langsung');
            }
            if (!Schema::hasColumn('pengajuan_cuti', 'pybmc_id')) {
                $table->foreignId('pybmc_id')->nullable()->after('pertimbangan_atasan_at')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_cuti', function (Blueprint $table) {
            $table->dropForeign(['atasan_langsung_id']);
            $table->dropForeign(['pybmc_id']);
            $table->dropColumn([
                'atasan_langsung_id',
                'pertimbangan_atasan',
                'catatan_atasan_langsung',
                'pertimbangan_atasan_at',
                'pybmc_id',
            ]);
        });
    }
};
