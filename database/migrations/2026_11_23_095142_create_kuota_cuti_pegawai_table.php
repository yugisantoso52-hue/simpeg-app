<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Pencatatan Saldo / Baseline Kuota Cuti Tahunan Pegawai (N, N-1, N-2)
     * Sesuai Peraturan BKN No. 24 Tahun 2017.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kuota_cuti_pegawai')) {
            Schema::create('kuota_cuti_pegawai', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->integer('tahun')->comment('Tahun Anggaran (N, N-1, N-2)');
                $table->integer('kuota_awal')->default(12)->comment('Hak Kuota Cuti Awal (Standar 12 hari kerja)');
                $table->integer('kuota_terpakai')->default(0)->comment('Jumlah hari cuti yang sudah terpakai pada tahun ini');
                $table->integer('sisa_kuota')->default(12)->comment('Sisa kuota riil/definitif');
                $table->text('catatan_penyesuaian')->nullable()->comment('Catatan penyesuaian saldo manual/SK Kepegawaian');
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['pegawai_id', 'tahun'], 'uq_pegawai_tahun_kuota');
                $table->index(['tahun', 'sisa_kuota']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kuota_cuti_pegawai');
    }
};
