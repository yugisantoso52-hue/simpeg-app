<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Refactoring Data Pegawai — SIMPEG Fak. Keperawatan UNRI
     * -------------------------------------------------------
     * 1. DROP kolom yang tidak relevan: nik, npwp, bpjs, gol_darah, tinggi_badan, berat_badan
     * 2. RENAME kolom: pendidikan → pendidikan_terakhir (data tetap aman)
     * 3. ADD kolom baru: karpeg_karis_karsu, nidn_nuptk, mkg_tahun, mkg_bulan
     */
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {

            // ── 1. HAPUS kolom yang tidak relevan ──────────────────────────
            $dropColumns = ['nik', 'npwp', 'bpjs', 'gol_darah', 'tinggi_badan', 'berat_badan'];
            foreach ($dropColumns as $col) {
                if (Schema::hasColumn('pegawai', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // RENAME harus dijalankan dalam statement terpisah (kompatibel SQLite & MySQL)
        Schema::table('pegawai', function (Blueprint $table) {
            if (Schema::hasColumn('pegawai', 'pendidikan') && !Schema::hasColumn('pegawai', 'pendidikan_terakhir')) {
                $table->renameColumn('pendidikan', 'pendidikan_terakhir');
            }
        });

        Schema::table('pegawai', function (Blueprint $table) {
            // ── 2. TAMBAH kolom identitas ASN / Dosen ──────────────────────
            if (!Schema::hasColumn('pegawai', 'karpeg_karis_karsu')) {
                // Kartu Pegawai / KARIS / KARSU — Identitas Kartu ASN
                $table->string('karpeg_karis_karsu', 50)->nullable()->after('nip')
                      ->comment('Nomor Kartu Pegawai / KARIS / KARSU');
            }

            if (!Schema::hasColumn('pegawai', 'nidn_nuptk')) {
                // NIDN / NUPTK — Identitas Tenaga Pendidik / Dosen
                $table->string('nidn_nuptk', 20)->nullable()->after('karpeg_karis_karsu')
                      ->comment('NIDN untuk Dosen atau NUPTK untuk Tendik');
            }

            // ── 3. TAMBAH kolom Masa Kerja Golongan (MKG) ──────────────────
            if (!Schema::hasColumn('pegawai', 'mkg_tahun')) {
                $table->unsignedSmallInteger('mkg_tahun')->default(0)->after('golongan_id')
                      ->comment('Masa Kerja Golongan — Tahun');
            }

            if (!Schema::hasColumn('pegawai', 'mkg_bulan')) {
                $table->unsignedTinyInteger('mkg_bulan')->default(0)->after('mkg_tahun')
                      ->comment('Masa Kerja Golongan — Bulan (0–11)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            // Hapus kolom baru yang ditambahkan
            $newColumns = ['karpeg_karis_karsu', 'nidn_nuptk', 'mkg_tahun', 'mkg_bulan'];
            foreach ($newColumns as $col) {
                if (Schema::hasColumn('pegawai', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Kembalikan nama kolom
        Schema::table('pegawai', function (Blueprint $table) {
            if (Schema::hasColumn('pegawai', 'pendidikan_terakhir') && !Schema::hasColumn('pegawai', 'pendidikan')) {
                $table->renameColumn('pendidikan_terakhir', 'pendidikan');
            }
        });

        // Kembalikan kolom yang di-drop (nullable agar tidak error saat data ada)
        Schema::table('pegawai', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawai', 'nik')) {
                $table->string('nik', 50)->nullable()->after('nip');
            }
            if (!Schema::hasColumn('pegawai', 'npwp')) {
                $table->string('npwp', 50)->nullable();
            }
            if (!Schema::hasColumn('pegawai', 'bpjs')) {
                $table->string('bpjs', 50)->nullable();
            }
            if (!Schema::hasColumn('pegawai', 'gol_darah')) {
                $table->string('gol_darah', 5)->nullable();
            }
            if (!Schema::hasColumn('pegawai', 'tinggi_badan')) {
                $table->integer('tinggi_badan')->default(0);
            }
            if (!Schema::hasColumn('pegawai', 'berat_badan')) {
                $table->integer('berat_badan')->default(0);
            }
        });
    }
};
