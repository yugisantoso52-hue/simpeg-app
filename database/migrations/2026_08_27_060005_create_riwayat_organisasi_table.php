<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Buat tabel riwayat_organisasi — SIMPEG Fak. Keperawatan UNRI
     * --------------------------------------------------------------
     * Menyimpan riwayat keanggotaan organisasi profesi/kemasyarakatan pegawai.
     * tahun_selesai nullable berarti pegawai masih aktif di organisasi tersebut.
     */
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_organisasi')) {
            Schema::create('riwayat_organisasi', function (Blueprint $table) {
                $table->id();

                $table->foreignId('pegawai_id')
                      ->constrained('pegawai')
                      ->cascadeOnDelete();

                $table->string('nama_organisasi', 150);

                $table->string('jabatan_organisasi', 100)
                      ->nullable()
                      ->comment('Ketua, Sekretaris, Anggota, dll');

                $table->year('tahun_mulai')->nullable();

                $table->year('tahun_selesai')
                      ->nullable()
                      ->comment('Nullable berarti masih aktif');

                $table->boolean('masih_aktif')->default(true);

                $table->text('keterangan')->nullable();

                $table->timestamps();

                $table->index('pegawai_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_organisasi');
    }
};
