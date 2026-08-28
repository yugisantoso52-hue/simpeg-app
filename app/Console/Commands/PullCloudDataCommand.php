<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class PullCloudDataCommand extends Command
{
    protected $signature = 'simpeg:pull-cloud 
                            {--url=https://sikap-app.up.railway.app : URL endpoint aplikasi di Railway Cloud}
                            {--key=sikap-sync-secret-key-2026 : Kunci keamanan sinkronisasi}
                            {--skip-files : Lewati pengunduhan berkas fisik (hanya database)}';

    protected $description = 'Menarik dan menyinkronkan seluruh database serta berkas pegawai dari Railway Cloud ke Localhost Laragon';

    public function handle(): int
    {
        $this->info('===========================================================');
        $this->info('  SIMPEG ENTERPRISE - CLOUD DATA PULL (RAILWAY -> LOCAL)  ');
        $this->info('===========================================================');

        $cloudUrl = rtrim($this->option('url'), '/');
        $syncKey = $this->option('key') ?: env('SYNC_TOKEN', 'sikap-sync-secret-key-2026');
        $skipFiles = $this->option('skip-files');

        $this->info("Menghubungkan ke Cloud Railway: {$cloudUrl} ...");

        try {
            $response = Http::timeout(60)->get("{$cloudUrl}/api/cloud-sync/export", [
                'key' => $syncKey,
            ]);

            if (!$response->successful()) {
                $this->error("Gagal terhubung ke Cloud. HTTP Status: " . $response->status());
                $this->line("Pesan: " . $response->body());
                return Command::FAILURE;
            }

            $payload = $response->json();

            if (!isset($payload['tables']) || !is_array($payload['tables'])) {
                $this->error("Format respon dari Railway Cloud tidak valid.");
                return Command::FAILURE;
            }

            $this->info("✓ Berhasil mengunduh snapshot data (" . count($payload['tables']) . " tabel ditemukan).");

            // 1. Sinkronisasi Data Tabel ke Database Lokal
            $this->info("\n[1/2] Menyinkronkan seluruh tabel data ke MySQL Localhost...");

            // Nonaktifkan Foreign Key Checks sementara
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($payload['tables'] as $tableName => $rows) {
                if (DB::getSchemaBuilder()->hasTable($tableName)) {
                    // Truncate tabel lokal
                    DB::table($tableName)->truncate();

                    if (!empty($rows)) {
                        // Insert data dalam batch
                        $chunks = array_chunk($rows, 100);
                        foreach ($chunks as $chunk) {
                            $insertData = array_map(function ($item) {
                                return (array)$item;
                            }, $chunk);
                            DB::table($tableName)->insert($insertData);
                        }
                        $this->line("  ✓ [{$tableName}] : " . count($rows) . " baris data disinkronkan.");
                    } else {
                        $this->line("  - [{$tableName}] : Kosong.");
                    }
                } else {
                    $this->warn("  ! Tabel [{$tableName}] belum ada di database lokal. Jalankan migrate.");
                }
            }

            // Aktifkan kembali Foreign Key Checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info("✓ Seluruh data pegawai, akun, jabatan, dan riwayat berhasil diperbarui di Localhost.");

            // 2. Sinkronisasi Berkas Dokumen & Foto Pegawai
            if (!$skipFiles && !empty($payload['files'])) {
                $this->info("\n[2/2] Menyinkronkan berkas dokumen & foto pegawai...");
                $localStorage = storage_path('app/public');
                if (!File::exists($localStorage)) {
                    File::makeDirectory($localStorage, 0755, true);
                }

                $fileCount = 0;
                $downloadCount = 0;
                $bar = $this->output->createProgressBar(count($payload['files']));
                $bar->start();

                foreach ($payload['files'] as $relPath) {
                    $destFile = $localStorage . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
                    $destDir = dirname($destFile);

                    if (!File::exists($destDir)) {
                        File::makeDirectory($destDir, 0755, true);
                    }

                    if (!File::exists($destFile)) {
                        // Unduh berkas dari cloud
                        $fileResponse = Http::timeout(30)->get("{$cloudUrl}/api/cloud-sync/file/{$relPath}", [
                            'key' => $syncKey,
                        ]);

                        if ($fileResponse->successful()) {
                            File::put($destFile, $fileResponse->body());
                            $downloadCount++;
                        }
                    }
                    $fileCount++;
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
                $this->info("✓ {$downloadCount} berkas baru berhasil diunduh (Total {$fileCount} berkas dicek).");
            }

            $this->info("\n===========================================================");
            $this->info("  SINKRONISASI DARI CLOUD RAILWAY KE LOCALHOST SELESAI!   ");
            $this->info("===========================================================");

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error("\nTerjadi kesalahan saat sinkronisasi: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
