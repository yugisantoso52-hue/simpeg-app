<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 50)->unique();
            $table->string('nik', 50)->nullable();
            $table->string('nama', 150);
            
            // Gelar
            $table->string('gelar_depan', 50)->nullable();
            $table->string('gelar_belakang', 50)->nullable();
            
            // Lahir & Identitas Dasar
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('pendidikan', 100)->nullable();
            
            // Dokumen & Kontak
            $table->string('npwp', 50)->nullable();
            $table->string('bpjs', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->text('alamat')->nullable();
            
            // Status Keluarga
            $table->string('status_pernikahan', 50)->nullable();
            $table->string('nama_pasangan', 150)->nullable();
            $table->integer('jumlah_anak')->default(0);
            
            // Data Fisik
            $table->string('gol_darah', 5)->nullable();
            $table->integer('tinggi_badan')->default(0);
            $table->integer('berat_badan')->default(0);
            
            // Jenis & Status Kepegawaian
            $table->string('jenis_pegawai', 100)->nullable();
            $table->string('status_asn', 50)->nullable();
            
            // Foreign Key ke tabel master
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->onDelete('set null');
            $table->foreignId('jabatan_id')->nullable()->constrained('jabatan')->onDelete('set null');
            $table->foreignId('golongan_id')->nullable()->constrained('golongan')->onDelete('set null');
            
            // Riwayat Penanggalan Kerja & Kenaikan Berkala
            $table->date('tanggal_masuk')->nullable();
            
            // TMT SK Pertama & File SK
            $table->date('tmt_sk_pertama')->nullable();
            $table->string('file_sk_pertama')->nullable();
            
            // TMT Pangkat Terakhir & File SK
            $table->date('tmt_pangkat_terakhir')->nullable();
            $table->string('file_sk_pangkat_terakhir')->nullable();
            
            // TMT KGB Terakhir & File SK
            $table->date('tmt_kgb_terakhir')->nullable();
            $table->string('file_sk_kgb_terakhir')->nullable();
            
            $table->date('kgb_berikutnya')->nullable();
            $table->date('kp_berikutnya')->nullable();
            $table->string('satyalancana_terakhir', 100)->nullable();
            $table->date('satyalancana_berikutnya')->nullable();
            
            // Status Kontrak/Aktif & Berkas Foto
            $table->string('status_pegawai', 50)->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};