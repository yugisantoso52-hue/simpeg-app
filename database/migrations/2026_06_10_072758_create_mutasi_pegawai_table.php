<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_pegawai', function (Blueprint $table) {

            $table->id();

            $table->foreignId('pegawai_id')
                ->constrained('pegawai')
                ->cascadeOnDelete();

            $table->foreignId('unit_lama_id')
                ->constrained('unit_kerja');

            $table->foreignId('unit_baru_id')
                ->constrained('unit_kerja');

            $table->foreignId('jabatan_lama_id')
                ->constrained('jabatan');

            $table->foreignId('jabatan_baru_id')
                ->constrained('jabatan');

            $table->date('tmt');

            $table->string('nomor_sk')->nullable();

            $table->string('file_sk')->nullable();

            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_pegawai');
    }
};