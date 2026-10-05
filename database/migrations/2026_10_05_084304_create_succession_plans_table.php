<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Rencana Suksesi ASN (Succession Planning) sesuai UU No. 20/2023 & PermenPAN-RB No. 3/2020
     */
    public function up(): void
    {
        if (!Schema::hasTable('succession_plans')) {
            Schema::create('succession_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('jabatan_target_id')->constrained('jabatan')->onDelete('cascade');
                $table->foreignId('pegawai_id')->constrained('pegawai')->onDelete('cascade');
                $table->unsignedSmallInteger('tahun')->index();
                
                $table->unsignedTinyInteger('peringkat_prioritas')->default(1)->comment('Peringkat prioritas suksesi (1, 2, 3...)');
                $table->decimal('match_score', 5, 2)->comment('Indeks Kecocokan Kualifikasi & Kompetensi (0-100%)');
                $table->json('gap_analysis')->nullable()->comment('Rincian kesenjangan kualifikasi, diklat & kepangkatan');
                
                $table->enum('status_kesiapan', [
                    'Siap Sekarang',             // Ready Now (< 6 Bulan)
                    'Siap 1-2 Tahun',            // Ready with Development (1 - 2 Tahun)
                    'Potensial Jangka Panjang'   // Future Ready (> 2 Tahun)
                ])->default('Siap Sekarang');

                $table->enum('status_nominasi', [
                    'Kandidat Terpilih',
                    'Dalam Seleksi',
                    'Ditetapkan PPK',
                    'Batal'
                ])->default('Kandidat Terpilih');

                $table->text('catatan_komite')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['jabatan_target_id', 'pegawai_id', 'tahun'], 'uniq_target_pegawai_thn');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('succession_plans');
    }
};
