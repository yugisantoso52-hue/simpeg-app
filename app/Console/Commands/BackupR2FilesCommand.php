<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackupR2FilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simpeg:backup-r2 
                            {--dest= : Direktori lokal tujuan backup (default: C:\simpeg-backup\files)}
                            {--sync-storage : Sinkronkan juga langsung ke storage/app lokal agar PC dev memiliki semua berkas}
                            {--dry-run : Hanya cek file yang akan diunduh tanpa mendownload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menarik / mencadangkan semua file (SK, PDF, Ijazah, Foto) dari Cloudflare R2 ke penyimpanan PC lokal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('====================================================');
        $this->info('  SIKAP - ONE-WAY BACKUP CLOUDFLARE R2 ➔ PC LOKAL  ');
        $this->info('====================================================');

        // Cek ketersediaan konfigurasi R2
        $bucket = config('filesystems.disks.r2.bucket');
        $key = config('filesystems.disks.r2.key');

        if (empty($bucket) || empty($key)) {
            $this->warn('Konfigurasi Cloudflare R2 belum lengkap di .env.');
            $this->line('Pastikan R2_ACCESS_KEY_ID, R2_SECRET_ACCESS_KEY, dan R2_BUCKET sudah diisi.');
            return 1;
        }

        $r2Disk = Storage::disk('r2');

        $this->info("Menghubungi Cloudflare R2 Bucket: [{$bucket}]...");

        try {
            $files = $r2Disk->allFiles();
        } catch (\Throwable $e) {
            $this->error('Gagal mengambil daftar file dari Cloudflare R2: ' . $e->getMessage());
            return 1;
        }

        $totalFiles = count($files);
        $this->info("Ditemukan {$totalFiles} berkas di Cloudflare R2.");

        if ($totalFiles === 0) {
            $this->info('Tidak ada berkas untuk diunduh.');
            return 0;
        }

        $backupBaseDir = $this->option('dest') ?: 'C:\simpeg-backup\files';
        $syncToLocalDevStorage = $this->option('sync-storage');
        $dryRun = $this->option('dry-run');

        if (!is_dir($backupBaseDir) && !$dryRun) {
            @mkdir($backupBaseDir, 0755, true);
        }

        $downloadedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;

        $bar = $this->output->createProgressBar($totalFiles);
        $bar->start();

        foreach ($files as $file) {
            $targetPath = $backupBaseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
            $targetDir  = dirname($targetPath);

            $needsDownload = true;
            try {
                $remoteSize = $r2Disk->size($file);
                if (file_exists($targetPath) && filesize($targetPath) === $remoteSize) {
                    $needsDownload = false;
                }
            } catch (\Throwable) {
                // Lanjutkan jika gagal mengambil ukuran
            }

            if ($needsDownload) {
                if ($dryRun) {
                    $downloadedCount++;
                    $bar->advance();
                    continue;
                }

                if (!is_dir($targetDir)) {
                    @mkdir($targetDir, 0755, true);
                }

                try {
                    $stream = $r2Disk->readStream($file);
                    if ($stream !== false) {
                        $localFile = fopen($targetPath, 'wb');
                        stream_copy_to_stream($stream, $localFile);
                        fclose($localFile);
                        if (is_resource($stream)) {
                            fclose($stream);
                        }
                        $downloadedCount++;

                        // Jika opsi --sync-storage aktif, salin juga ke storage lokal Laravel
                        if ($syncToLocalDevStorage) {
                            $devStoragePath = storage_path('app/public' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file));
                            $devStorageDir = dirname($devStoragePath);
                            if (!is_dir($devStorageDir)) {
                                @mkdir($devStorageDir, 0755, true);
                            }
                            @copy($targetPath, $devStoragePath);
                        }
                    } else {
                        $failedCount++;
                    }
                } catch (\Throwable $e) {
                    $failedCount++;
                }
            } else {
                $skippedCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Pencadangan Berkas Selesai:");
        $this->line(" - Berkas baru diunduh  : {$downloadedCount}");
        $this->line(" - Berkas sudah ada     : {$skippedCount}");
        if ($failedCount > 0) {
            $this->warn(" - Berkas gagal unduh   : {$failedCount}");
        }
        $this->info("Lokasi cadangan lokal   : {$backupBaseDir}");

        return 0;
    }
}
