<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Implementasi Manajemen Talenta ASN sesuai PermenPAN-RB No. 3 Tahun 2020 & UU No. 20 Tahun 2023
     */
    public function up(): void
    {
        // 1. Tabel Penilaian Asesmen Kompetensi & Potensi (Assessment Center BKN / Mandiri)
        if (!Schema::hasTable('talent_assessments')) {
            Schema::create('talent_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
                $table->date('tanggal_asesmen');
                $table->string('nomor_surat')->nullable();
                $table->string('lembaga_penyelenggara')->default('Pusat Penilaian Kompetensi ASN BKN');
                $table->string('metode_asesmen')->default('Assessment Center / CACT BKN');
                
                // Dimensi Kompetensi (Skala 0 - 100)
                $table->decimal('skor_manajerial', 5, 2)->nullable()->comment('Kompetensi Manajerial (Integritas, Kerjasama, Komunikasi, Pelayanan Publik)');
                $table->decimal('skor_sosio_kultural', 5, 2)->nullable()->comment('Kompetensi Sosio Kultural (Perekat Bangsa)');
                $table->decimal('skor_teknis', 5, 2)->nullable()->comment('Kompetensi Teknis sesuai bidang jabatan');
                $table->decimal('skor_potensi', 5, 2)->nullable()->comment('Potensi Psikometri / Intelegensi / Sikap Kerja');
                $table->decimal('skor_total', 5, 2)->comment('Rata-rata tertimbang atau skor komposit asesmen (0-100)');
                
                $table->string('kategori_kelayakan')->default('Memenuhi Syarat (MS)')->comment('MS, Masih Memenuhi Syarat (MMS), Kurang (KMS)');
                $table->text('ringkasan_kompetensi')->nullable();
                $table->text('rekomendasi_pengembangan')->nullable();
                $table->string('file_laporan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['pegawai_id', 'tanggal_asesmen']);
            });
        }

        // 2. Tabel Snapshot Pemetaan 9-Kotak Talenta ASN (9-Box Grid PermenPAN-RB No. 3/2020)
        if (!Schema::hasTable('talent_mappings')) {
            Schema::create('talent_mappings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
                $table->unsignedSmallInteger('tahun')->index();
                $table->string('periode', 20)->default('Tahunan');
                
                $table->foreignId('jabatan_saat_ini_id')->nullable()->constrained('jabatan')->nullOnDelete();
                $table->foreignId('unit_kerja_saat_ini_id')->nullable()->constrained('unit_kerja')->nullOnDelete();

                // SUMBU X: KINERJA (Performance 0 - 100)
                $table->decimal('skor_skp_n', 5, 2)->nullable()->comment('Nilai SKP Tahun Berjalan N');
                $table->string('predikat_skp_n', 50)->nullable();
                $table->decimal('skor_skp_n_minus_1', 5, 2)->nullable()->comment('Nilai SKP Tahun N-1');
                $table->string('predikat_skp_n_minus_1', 50)->nullable();
                $table->decimal('skor_kinerja_skp', 5, 2)->comment('Agregasi Nilai SKP (0-100)');
                $table->decimal('skor_disiplin_kehadiran', 5, 2)->nullable()->comment('Indikator perilaku kehadiran');
                $table->decimal('skor_aktivitas_logbook', 5, 2)->nullable()->comment('Indikator keaktifan logbook');
                $table->decimal('sumbu_kinerja_nilai', 5, 2)->index()->comment('Nilai Komposit Sumbu Kinerja X (0-100)');
                $table->enum('sumbu_kinerja_kategori', ['Rendah', 'Sedang', 'Tinggi'])->index();

                // SUMBU Y: POTENSI (Potential 0 - 100)
                $table->decimal('skor_kualifikasi_pendidikan', 5, 2)->comment('Skor Jenjang & Kualifikasi Pendidikan');
                $table->decimal('skor_pengembangan_kompetensi', 5, 2)->comment('Skor Pemenuhan 20 JP Diklat');
                $table->decimal('skor_rekam_jejak', 5, 2)->comment('Skor Masa Kerja, Pangkat, Penghargaan');
                $table->decimal('skor_asesmen_kompetensi', 5, 2)->nullable()->comment('Skor dari Assessment Center jika ada');
                $table->decimal('sumbu_potensi_nilai', 5, 2)->index()->comment('Nilai Komposit Sumbu Potensi Y (0-100)');
                $table->enum('sumbu_potensi_kategori', ['Rendah', 'Sedang', 'Tinggi'])->index();

                // HASIL 9-BOX MATRIX PERMENPAN-RB 3/2020
                $table->unsignedTinyInteger('kuadran_box')->index()->comment('Kotak 1 s.d 9');
                $table->string('box_name')->comment('Nama Resmi Kuadran');
                $table->string('status_talenta')->comment('Kategori Talenta (cth: Siap Dipromosikan, Dipertahankan, Dibina)');
                $table->text('rekomendasi_kebijakan')->comment('Rekomendasi tindak lanjut suksesi & pengembangan');
                $table->boolean('is_suksesi_eligible')->default(false)->index()->comment('Masuk Talent Pool Suksesi (Kotak 9/8)');

                // Validasi Komite Talenta
                $table->enum('status_validasi', ['Draft', 'Ditinjau Komite', 'Ditetapkan PPK'])->default('Draft');
                $table->text('catatan_komite')->nullable();
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validated_at')->nullable();

                $table->timestamps();

                $table->unique(['pegawai_id', 'tahun', 'periode'], 'uniq_talent_pegawai_thn_periode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('talent_mappings');
        Schema::dropIfExists('talent_assessments');
    }
};
