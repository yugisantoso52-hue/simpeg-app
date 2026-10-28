<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master kelompok/unit penugasan non-struktural yang tampil di Peta Jabatan
        if (!Schema::hasTable('kelompok_penugasan')) {
            Schema::create('kelompok_penugasan', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->unique();
                $table->string('nama', 150);
                $table->string('kategori', 30); // kjfd | unit_fungsional | laboratorium
                $table->unsignedInteger('kebutuhan')->nullable(); // target formasi, null = belum ditetapkan
                $table->unsignedSmallInteger('urutan')->default(0);
                $table->timestamps();
            });
        }

        // Penugasan/keanggotaan pegawai (satu pegawai boleh banyak penugasan)
        if (!Schema::hasTable('pegawai_penugasan')) {
            Schema::create('pegawai_penugasan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->foreignId('kelompok_penugasan_id')->constrained('kelompok_penugasan')->cascadeOnDelete();
                $table->string('peran', 30)->default('Anggota'); // Koordinator | Anggota | Staf
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();

                $table->unique(['pegawai_id', 'kelompok_penugasan_id'], 'uniq_pegawai_kelompok');
            });
        }

        // Pengajuan jabatan struktural oleh pegawai, menunggu verifikasi berdasarkan SK
        if (!Schema::hasTable('pengajuan_jabatan')) {
            Schema::create('pengajuan_jabatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
                $table->foreignId('jabatan_id')->constrained('jabatan')->cascadeOnDelete();
                $table->foreignId('unit_kerja_id')->constrained('unit_kerja')->cascadeOnDelete();
                $table->string('nomor_sk', 100);
                $table->date('tanggal_sk');
                $table->date('tmt_jabatan');
                $table->string('file_sk', 255)->nullable();
                $table->string('status', 20)->default('menunggu'); // menunggu | disetujui | ditolak
                $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('diverifikasi_at')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();

                $table->index(['status', 'jabatan_id']);
            });
        }

        $this->seedKelompok();
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_jabatan');
        Schema::dropIfExists('pegawai_penugasan');
        Schema::dropIfExists('kelompok_penugasan');
    }

    private function seedKelompok(): void
    {
        $daftar = [
            'kjfd' => ['Medikal Bedah', 'Gawat Darurat', 'Maternitas', 'Anak', 'Keluarga Komunitas', 'Gerontik', 'Jiwa', 'Klinik', 'Komunitas'],
            'unit_fungsional' => ['Etik Riset', 'Komite Etik', 'Computer Based Test (CBT)', 'Kerjasama', 'Penelitian dan Pengabmas', 'Nursing Education Development Unit (NEDU)', 'Bimbingan Konseling', 'Humas', 'PPID'],
            'laboratorium' => ['Biomedik', 'Medikal Bedah', 'Gawat Darurat', 'Jiwa', 'Keluarga Komunitas', 'Gerontik', 'Maternitas', 'Anak', 'Tumbuh Kembang Anak'],
        ];

        $now = now();
        foreach ($daftar as $kategori => $items) {
            foreach ($items as $i => $nama) {
                $kode = $kategori . '-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
                if (!DB::table('kelompok_penugasan')->where('kode', $kode)->exists()) {
                    DB::table('kelompok_penugasan')->insert([
                        'kode' => $kode,
                        'nama' => $nama,
                        'kategori' => $kategori,
                        'kebutuhan' => null,
                        'urutan' => $i + 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
};
