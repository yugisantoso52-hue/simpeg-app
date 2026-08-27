<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Buat tabel riwayat_penghargaan — SIMPEG Fak. Keperawatan UNRI
     * ---------------------------------------------------------------
     * Menyimpan riwayat penghargaan/tanda jasa yang diterima pegawai,
     * seperti Satyalancana, Bintang Jasa, Piagam, dll.
     */
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_penghargaan')) {
            Schema::create('riwayat_penghargaan', function (Blueprint $table) {
                $table->id();

                $table->foreignId('pegawai_id')
                      ->constrained('pegawai')
                      ->cascadeOnDelete();

                $table->string('nama_penghargaan', 150);

                $table->string('jenis_penghargaan', 100)
                      ->nullable()
                      ->comment('Satyalancana, Bintang Jasa, Piagam, dll');

                $table->string('instansi_pemberi', 150)->nullable();

                $table->date('tanggal_terima')->nullable();

                $table->string('nomor_sk', 100)->nullable();

                $table->string('file_sk')->nullable();

                $table->text('keterangan')->nullable();

                $table->timestamps();

                $table->index(['pegawai_id', 'tanggal_terima']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_penghargaan');
    }
};
