<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'simpeg:backup {--only-db : Backup database saja} {--only-files : Backup berkas dokumen saja}';
    protected $description = 'Melakukan backup database dan berkas dokumen SIMPEG secara otomatis ke format arsip terkompresi';

    public function handle(): int
    {
        $this->info('=============================================');
        $this->info('  SIMPEG ENTERPRISE - AUTO BACKUP SYSTEM     ');
        $this->info('=============================================');

        $timestamp = date('Y-m-d_His');
        $backupDir = storage_path('app/backups');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $zipFile = $backupDir . DIRECTORY_SEPARATOR . "simpeg_backup_{$timestamp}.zip";
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error('Gagal membuat berkas arsip zip backup.');
            Log::error('SIMPEG BACKUP: Gagal menginisialisasi zip file di ' . $zipFile);
            return Command::FAILURE;
        }

        $dbConnection = config('database.default');
        $onlyFiles = $this->option('only-files');
        $onlyDb = $this->option('only-db');

        // 1. Backup Database
        if (!$onlyFiles) {
            $this->info("Menyimpan snapshot database [{$dbConnection}]...");

            if ($dbConnection === 'sqlite') {
                $dbPath = config('database.connections.sqlite.database');
                if (File::exists($dbPath)) {
                    $zip->addFile($dbPath, 'database/database.sqlite');
                    $this->line("  ✓ SQLite database berhasil diarsipkan.");
                }
            } else {
                try {
                    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
                    $dbName = config("database.connections.{$dbConnection}.database");
                    $keyName = "Tables_in_{$dbName}";

                    $sqlDump = "-- SIMPEG ENTERPRISE DATABASE BACKUP\n-- Generated: " . date('Y-m-d H:i:s') . "\n-- Connection: {$dbConnection}\n\n";

                    foreach ($tables as $tableObj) {
                        $tableName = $tableObj->$keyName ?? array_values((array)$tableObj)[0];
                        $createTable = \Illuminate\Support\Facades\DB::select("SHOW CREATE TABLE `{$tableName}`");
                        if (!empty($createTable)) {
                            $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                            $sqlDump .= $createTable[0]->{'Create Table'} . ";\n\n";

                            $rows = \Illuminate\Support\Facades\DB::table($tableName)->get();
                            if ($rows->count() > 0) {
                                foreach ($rows as $row) {
                                    $data = array_map(function ($val) {
                                        if (is_null($val)) return 'NULL';
                                        return "'" . addslashes((string)$val) . "'";
                                    }, (array)$row);

                                    $sqlDump .= "INSERT INTO `{$tableName}` (" . implode(', ', array_map(fn($k) => "`{$k}`", array_keys((array)$row))) . ") VALUES (" . implode(', ', $data) . ");\n";
                                }
                                $sqlDump .= "\n";
                            }
                        }
                    }

                    $zip->addFromString('database/dump.sql', $sqlDump);
                    $this->line("  ✓ MySQL data tables berhasil diekspor dan diarsipkan.");
                } catch (\Throwable $e) {
                    $this->warn("  ! Gagal mengekspor data SQL: " . $e->getMessage());
                }
            }
        }

        // 2. Backup Uploaded Files / Documents
        if (!$onlyDb) {
            $this->info('Mengarsipkan berkas dokumen & storage publik/privat...');
            $storagePaths = [
                storage_path('app/public'),
                storage_path('app/private'),
            ];

            $fileCount = 0;
            foreach ($storagePaths as $sPath) {
                if (File::exists($sPath)) {
                    $files = File::allFiles($sPath);
                    foreach ($files as $file) {
                        $relativeName = 'storage/' . str_replace('\\', '/', $file->getRelativePathname());
                        $zip->addFile($file->getRealPath(), $relativeName);
                        $fileCount++;
                    }
                }
            }
            $this->line("  ✓ Sebanyak {$fileCount} dokumen berhasil dimasukkan ke dalam arsip.");
        }

        // Tambahkan metadata berkas backup
        $zip->addFromString('backup_meta.json', json_encode([
            'system'     => 'SIKAP Enterprise',
            'created_at' => date('Y-m-d H:i:s'),
            'connection' => $dbConnection,
        ], JSON_PRETTY_PRINT));

        $zip->close();

        clearstatcache(true, $zipFile);
        $size = File::exists($zipFile) ? File::size($zipFile) : 0;
        $sizeFormatted = round($size / (1024 * 1024), 2) . ' MB';

        // 3. Housekeeping: Hapus backup yang lebih lama dari 14 hari
        $allBackups = File::glob($backupDir . '/*.zip');
        $deleted = 0;
        foreach ($allBackups as $bFile) {
            if (File::lastModified($bFile) < strtotime('-14 days')) {
                File::delete($bFile);
                $deleted++;
            }
        }

        $this->info("✅ Backup Berhasil Dibuat: {$zipFile} ({$sizeFormatted})");
        if ($deleted > 0) {
            $this->comment("  * {$deleted} berkas backup kedaluwarsa (>14 hari) telah dibersihkan.");
        }

        Log::info("SIMPEG: Backup sistem berhasil dibuat di {$zipFile} ({$sizeFormatted})");
        return Command::SUCCESS;
    }
}