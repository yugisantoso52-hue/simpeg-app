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
                            {--disk= : Disk cloud yang dicadangkan (default: auto detect supabase/r2/s3)}
                            {--dest= : Direktori lokal tujuan backup (default: C:\simpeg-backup\files)}
                            {--sync-storage : Sinkronkan juga langsung ke storage/app lokal agar PC dev memiliki semua berkas}
                            {--dry-run : Hanya cek file yang akan diunduh tanpa mendownload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menarik / mencadangkan semua file (SK, PDF, Ijazah, Foto) dari Cloud Storage (Supabase S3 / Cloudflare R2) ke penyimpanan PC lokal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('====================================================');
        $this->info('  SIKAP - ONE-WAY BACKUP CLOUD STORAGE ➔ PC LOKAL  ');
        $this->info('====================================================');

        // Tentukan disk yang aktif (prioritas: opsi --disk, supabase, r2, s3)
        $diskName = $this->option('disk');
        if (!$diskName) {
            if (!empty(config('filesystems.disks.supabase.key'))) {
                $diskName = 'supabase';
            } elseif (!empty(config('filesystems.disks.r2.key'))) {
                $diskName = 'r2';
            } elseif (!empty(config('filesystems.disks.s3.key'))) {
                $diskName = 's3';
            } else {
                $diskName = 'supabase';
            }
        }

        $bucket = config("filesystems.disks.{$diskName}.bucket");
        $key    = config("filesystems.disks.{$diskName}.key");

        if (empty($bucket) || empty($key)) {
            $this->warn("Konfigurasi Cloud Storage ({$diskName}) belum lengkap di .env.");
            $this->line("Pastikan Access Key, Secret Key, dan Bucket sudah diisi (contoh: SUPABASE_STORAGE_ACCESS_KEY_ID & SUPABASE_STORAGE_SECRET_ACCESS_KEY).");
            return 1;
        }

        $cloudDisk = Storage::disk($diskName);

        $this->info("Menghubungi Cloud Storage [{$diskName}] Bucket: [{$bucket}]...");

        try {
            $files = $cloudDisk->allFiles();
        } catch (\Throwable $e) {
            $this->error("Gagal mengambil daftar file dari Cloud Storage ({$diskName}): " . $e->getMessage());
            return 1;
        }

        $totalFiles = count($files);
        $this->info("Ditemukan {$totalFiles} berkas di Cloud Storage.");

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
                $remoteSize = $cloudDisk->size($file);
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
                    $stream = $cloudDisk->readStream($file);
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
