<?php

namespace App\Console\Commands;

use App\Models\Pegawai;
use App\Models\RiwayatJabatan;
use App\Models\RiwayatPangkat;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncInitialRiwayat extends Command
{
    protected $signature = 'simpeg:sync-initial-riwayat';
    protected $description = 'Sinkronisasi data awal nomor SK, tanggal SK, dan file SK dari data pegawai ke riwayat aktif';

    public function handle()
    {
        $this->info("=== MEMULAI SINKRONISASI DATA AWAL RIWAYAT ===");

        $pegawais = Pegawai::all();
        $this->info("Jumlah pegawai yang akan diproses: " . $pegawais->count());

        $syncedJabatan = 0;
        $syncedPangkat = 0;

        foreach ($pegawais as $pegawai) {
            $namaPegawai = $pegawai->nama_lengkap ?? $pegawai->nama ?? 'Pegawai';
            $this->line("Memproses [{$pegawai->nip}] {$namaPegawai}...");

            // 1. Sinkronisasi Jabatan
            $riwayatJabatan = RiwayatJabatan::where('pegawai_id', $pegawai->id)
                ->whereIn('status', ['aktif', 'Aktif'])
                ->first();

            // Jika tidak ada riwayat jabatan aktif, buat baru dari data utama
            if (!$riwayatJabatan && $pegawai->jabatan_id && $pegawai->unit_kerja_id) {
                $riwayatJabatan = RiwayatJabatan::create([
                    'pegawai_id'    => $pegawai->id,
                    'jabatan_id'    => $pegawai->jabatan_id,
                    'unit_kerja_id' => $pegawai->unit_kerja_id,
                    'tmt_jabatan'   => $pegawai->tanggal_masuk ?? now(),
                    'keterangan'    => 'Riwayat awal (di-generate otomatis saat sinkronisasi)',
                    'status'        => 'Aktif'
                ]);
            }

            if ($riwayatJabatan) {
                $updated = false;

                // Sinkronkan nomor_sk
                if (empty($riwayatJabatan->nomor_sk) || $riwayatJabatan->nomor_sk === '-') {
                    $tahunMasuk = $pegawai->tanggal_masuk ? Carbon::parse($pegawai->tanggal_masuk)->format('Y') : date('Y');
                    $riwayatJabatan->nomor_sk = 'SK/JAB/' . $pegawai->nip . '/' . $tahunMasuk;
                    $updated = true;
                }

                // Sinkronkan tanggal_sk
                if (empty($riwayatJabatan->tanggal_sk)) {
                    $riwayatJabatan->tanggal_sk = $pegawai->tanggal_masuk ?? now();
                    $updated = true;
                }

                // Sinkronkan file_sk
                if ((empty($riwayatJabatan->file_sk) || $riwayatJabatan->file_sk === '-') && !empty($pegawai->file_sk_pertama)) {
                    $riwayatJabatan->file_sk = $pegawai->file_sk_pertama;
                    $updated = true;
                }

                if ($updated) {
                    $riwayatJabatan->save();
                    $syncedJabatan++;
                    $tglFormatted = Carbon::parse($riwayatJabatan->tanggal_sk)->format('d-m-Y');
                    $this->line("  -> [JABATAN] Nomor SK: {$riwayatJabatan->nomor_sk}, Tanggal SK: {$tglFormatted}, File SK: {$riwayatJabatan->file_sk}");
                }
            }

            // 2. Sinkronisasi Pangkat
            $riwayatPangkat = RiwayatPangkat::where('pegawai_id', $pegawai->id)
                ->whereIn('status', ['aktif', 'Aktif'])
                ->first();

            // Jika tidak ada riwayat pangkat aktif, buat baru dari data utama
            if (!$riwayatPangkat && $pegawai->golongan_id) {
                $riwayatPangkat = RiwayatPangkat::create([
                    'pegawai_id'  => $pegawai->id,
                    'golongan_id' => $pegawai->golongan_id,
                    'tmt'         => $pegawai->tmt_pangkat_terakhir ?? now(),
                    'keterangan'  => 'Riwayat awal (di-generate otomatis saat sinkronisasi)',
                    'status'      => 'aktif'
                ]);
            }

            if ($riwayatPangkat) {
                $updated = false;

                // Sinkronkan nomor_sk
                if (empty($riwayatPangkat->nomor_sk) || $riwayatPangkat->nomor_sk === '-') {
                    $tahunPangkat = $pegawai->tmt_pangkat_terakhir ? Carbon::parse($pegawai->tmt_pangkat_terakhir)->format('Y') : date('Y');
                    $riwayatPangkat->nomor_sk = 'SK/PKT/' . $pegawai->nip . '/' . $tahunPangkat;
                    $updated = true;
                }

                // Sinkronkan tanggal_sk
                if (empty($riwayatPangkat->tanggal_sk)) {
                    $riwayatPangkat->tanggal_sk = $pegawai->tmt_pangkat_terakhir ?? now();
                    $updated = true;
                }

                // Sinkronkan file_sk
                if ((empty($riwayatPangkat->file_sk) || $riwayatPangkat->file_sk === '-') && !empty($pegawai->file_sk_pangkat_terakhir)) {
                    $riwayatPangkat->file_sk = $pegawai->file_sk_pangkat_terakhir;
                    $updated = true;
                }

                if ($updated) {
                    $riwayatPangkat->save();
                    $syncedPangkat++;
                    $tglFormatted = Carbon::parse($riwayatPangkat->tanggal_sk)->format('d-m-Y');
                    $this->line("  -> [PANGKAT] Nomor SK: {$riwayatPangkat->nomor_sk}, Tanggal SK: {$tglFormatted}, File SK: {$riwayatPangkat->file_sk}");
                }
            }
        }

        $this->info("=== SINKRONISASI SELESAI ===");
        $this->info("Total Riwayat Jabatan tersinkron: {$syncedJabatan}");
        $this->info("Total Riwayat Pangkat tersinkron: {$syncedPangkat}");

        return Command::SUCCESS;
    }
}
