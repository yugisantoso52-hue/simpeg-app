<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Improve tabel mutasi_pegawai — SIMPEG Fak. Keperawatan UNRI
     * -------------------------------------------------------------
     * Tambah: jenis_mutasi, golongan_lama_id, golongan_baru_id, tanggal_sk, alasan_mutasi
     */
    public function up(): void
    {
        Schema::table('mutasi_pegawai', function (Blueprint $table) {

            if (!Schema::hasColumn('mutasi_pegawai', 'jenis_mutasi')) {
                $table->string('jenis_mutasi', 50)
                      ->nullable()
                      ->after('id')
                      ->comment('Pindah Unit, Promosi, Demosi, Alih Fungsi');
            }

            if (!Schema::hasColumn('mutasi_pegawai', 'golongan_lama_id')) {
                $table->unsignedBigInteger('golongan_lama_id')->nullable()->after('jabatan_baru_id');
                $table->foreign('golongan_lama_id')->references('id')->on('golongan')->nullOnDelete();
            }

            if (!Schema::hasColumn('mutasi_pegawai', 'golongan_baru_id')) {
                $table->unsignedBigInteger('golongan_baru_id')->nullable()->after('golongan_lama_id');
                $table->foreign('golongan_baru_id')->references('id')->on('golongan')->nullOnDelete();
            }

            if (!Schema::hasColumn('mutasi_pegawai', 'tanggal_sk')) {
                $table->date('tanggal_sk')->nullable()->after('nomor_sk');
            }

            if (!Schema::hasColumn('mutasi_pegawai', 'alasan_mutasi')) {
                $table->text('alasan_mutasi')->nullable()->after('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_pegawai', function (Blueprint $table) {
            if (Schema::hasColumn('mutasi_pegawai', 'golongan_lama_id')) {
                $table->dropForeign(['golongan_lama_id']);
            }
            if (Schema::hasColumn('mutasi_pegawai', 'golongan_baru_id')) {
                $table->dropForeign(['golongan_baru_id']);
            }
            $columns = ['jenis_mutasi', 'golongan_lama_id', 'golongan_baru_id', 'tanggal_sk', 'alasan_mutasi'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('mutasi_pegawai', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
