<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tambah kolom Kontak & Domisili serta Kepegawaian Teknis — SIMPEG Fak. Keperawatan UNRI
     * -----------------------------------------------------------------------------------------
     * Kontak & Domisili : alamat_domisili, kode_pos, kota_domisili, provinsi,
     *                     no_hp_darurat, nama_kontak_darurat, hubungan_kontak_darurat
     * Kepegawaian Teknis: jenis_jabatan, angka_kredit, batas_usia_pensiun, tanggal_pensiun,
     *                     no_sk_pensiun, tmt_pensiun, jenis_kontrak,
     *                     tanggal_kontrak_mulai, tanggal_kontrak_selesai
     */
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {

            // ── Kontak & Domisili ──────────────────────────────────────────────
            if (!Schema::hasColumn('pegawai', 'alamat_domisili')) {
                $table->text('alamat_domisili')->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('pegawai', 'kode_pos')) {
                $table->string('kode_pos', 10)->nullable()->after('alamat_domisili');
            }
            if (!Schema::hasColumn('pegawai', 'kota_domisili')) {
                $table->string('kota_domisili', 100)->nullable()->after('kode_pos');
            }
            if (!Schema::hasColumn('pegawai', 'provinsi')) {
                $table->string('provinsi', 100)->nullable()->after('kota_domisili');
            }
            if (!Schema::hasColumn('pegawai', 'no_hp_darurat')) {
                $table->string('no_hp_darurat', 20)->nullable()->after('no_hp');
            }
            if (!Schema::hasColumn('pegawai', 'nama_kontak_darurat')) {
                $table->string('nama_kontak_darurat', 100)->nullable()->after('no_hp_darurat');
            }
            if (!Schema::hasColumn('pegawai', 'hubungan_kontak_darurat')) {
                $table->string('hubungan_kontak_darurat', 50)->nullable()->after('nama_kontak_darurat');
            }

            // ── Kepegawaian Teknis ─────────────────────────────────────────────
            if (!Schema::hasColumn('pegawai', 'jenis_jabatan')) {
                $table->enum('jenis_jabatan', ['Struktural', 'Fungsional', 'Pelaksana', 'Lainnya'])
                      ->nullable()
                      ->after('status_asn');
            }
            if (!Schema::hasColumn('pegawai', 'angka_kredit')) {
                $table->decimal('angka_kredit', 8, 2)
                      ->nullable()
                      ->default(0)
                      ->after('jenis_jabatan')
                      ->comment('Angka kredit kumulatif jabatan fungsional/dosen');
            }
            if (!Schema::hasColumn('pegawai', 'batas_usia_pensiun')) {
                $table->unsignedTinyInteger('batas_usia_pensiun')
                      ->nullable()
                      ->after('angka_kredit')
                      ->comment('BUP dalam tahun: 56/58/60/65');
            }
            if (!Schema::hasColumn('pegawai', 'tanggal_pensiun')) {
                $table->date('tanggal_pensiun')
                      ->nullable()
                      ->after('batas_usia_pensiun')
                      ->comment('Auto-hitung dari tanggal_lahir + BUP');
            }
            if (!Schema::hasColumn('pegawai', 'no_sk_pensiun')) {
                $table->string('no_sk_pensiun', 100)->nullable()->after('tanggal_pensiun');
            }
            if (!Schema::hasColumn('pegawai', 'tmt_pensiun')) {
                $table->date('tmt_pensiun')->nullable()->after('no_sk_pensiun');
            }
            if (!Schema::hasColumn('pegawai', 'jenis_kontrak')) {
                $table->string('jenis_kontrak', 100)
                      ->nullable()
                      ->after('tmt_pensiun')
                      ->comment('Khusus PHL: per bulan/tahun dll');
            }
            if (!Schema::hasColumn('pegawai', 'tanggal_kontrak_mulai')) {
                $table->date('tanggal_kontrak_mulai')->nullable()->after('jenis_kontrak');
            }
            if (!Schema::hasColumn('pegawai', 'tanggal_kontrak_selesai')) {
                $table->date('tanggal_kontrak_selesai')
                      ->nullable()
                      ->after('tanggal_kontrak_mulai')
                      ->comment('Tanggal berakhir kontrak PHL aktif');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $columns = [
                'alamat_domisili', 'kode_pos', 'kota_domisili', 'provinsi',
                'no_hp_darurat', 'nama_kontak_darurat', 'hubungan_kontak_darurat',
                'jenis_jabatan', 'angka_kredit', 'batas_usia_pensiun', 'tanggal_pensiun',
                'no_sk_pensiun', 'tmt_pensiun', 'jenis_kontrak',
                'tanggal_kontrak_mulai', 'tanggal_kontrak_selesai',
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('pegawai', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
