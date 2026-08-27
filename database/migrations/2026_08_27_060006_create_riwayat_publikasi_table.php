<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Buat tabel riwayat_publikasi — SIMPEG Fak. Keperawatan UNRI
     * -------------------------------------------------------------
     * Menyimpan riwayat publikasi ilmiah pegawai/dosen:
     * jurnal, prosiding, buku, book chapter, paten, HKI, dll.
     * Termasuk informasi indeksasi (Scopus, WoS, SINTA, dll).
     */
    public function up(): void
    {
        if (!Schema::hasTable('riwayat_publikasi')) {
            Schema::create('riwayat_publikasi', function (Blueprint $table) {
                $table->id();

                $table->foreignId('pegawai_id')
                      ->constrained('pegawai')
                      ->cascadeOnDelete();

                $table->text('judul_publikasi');

                $table->enum('jenis_publikasi', [
                    'Jurnal',
                    'Prosiding',
                    'Buku',
                    'Book Chapter',
                    'Paten',
                    'HKI',
                    'Lainnya',
                ])->default('Jurnal');

                $table->string('nama_jurnal', 200)->nullable();

                $table->string('penerbit', 200)->nullable();

                $table->year('tahun_terbit')->nullable();

                $table->string('volume_nomor', 50)
                      ->nullable()
                      ->comment('Volume dan nomor jurnal');

                $table->string('url_doi', 255)->nullable();

                $table->enum('indeksasi', [
                    'Scopus',
                    'WoS',
                    'SINTA 1',
                    'SINTA 2',
                    'SINTA 3',
                    'SINTA 4',
                    'SINTA 5',
                    'SINTA 6',
                    'Nasional Terakreditasi',
                    'Nasional Tidak Terakreditasi',
                    'Lainnya',
                ])->nullable();

                $table->string('file_publikasi')->nullable();

                $table->text('keterangan')->nullable();

                $table->timestamps();

                $table->index(['pegawai_id', 'tahun_terbit']);
                $table->index('jenis_publikasi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_publikasi');
    }
};
