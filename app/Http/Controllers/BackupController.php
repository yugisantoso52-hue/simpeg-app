<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupController extends Controller
{
    /**
     * Tampilkan halaman Backup dan Restore Database.
     */
    public function index()
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $dbInfo = [
            'driver' => $config['driver'] ?? $connection,
            'database' => $config['database'] ?? '-',
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => $config['port'] ?? '3306',
            'username' => $config['username'] ?? 'root',
            'table_count' => 0,
            'size_mb' => 0,
        ];

        try {
            if ($dbInfo['driver'] === 'mysql') {
                $tables = DB::select('SHOW TABLES');
                $dbInfo['table_count'] = count($tables);

                $sizeResult = DB::select(
                    "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                     FROM information_schema.TABLES 
                     WHERE table_schema = ?",
                    [$dbInfo['database']]
                );
                $dbInfo['size_mb'] = $sizeResult[0]->size_mb ?? 0;
            } elseif ($dbInfo['driver'] === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                $dbInfo['table_count'] = count($tables);
                if (file_exists($config['database'])) {
                    $dbInfo['size_mb'] = round(filesize($config['database']) / 1024 / 1024, 2);
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal mengambil metadata database: " . $e->getMessage());
        }

        return view('backup', compact('dbInfo'));
    }

    /**
     * Ekspor seluruh database MySQL ke file .sql dan unduh.
     */
    public function export()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        $database = $config['database'] ?? env('DB_DATABASE', 'laravel');
        $username = $config['username'] ?? env('DB_USERNAME', 'root');
        $password = $config['password'] ?? env('DB_PASSWORD', '');
        $host     = $config['host'] ?? env('DB_HOST', '127.0.0.1');
        $port     = $config['port'] ?? env('DB_PORT', '3306');

        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $timestamp = date('Y-m-d_His');
        $fileName = "backup_simpeg_{$timestamp}.sql";
        $filePath = "{$backupDir}/{$fileName}";

        $mysqldumpPath = $this->findMysqldumpPath();

        // 1. Coba gunakan mysqldump jika executable ditemukan
        if ($mysqldumpPath && ($config['driver'] ?? 'mysql') === 'mysql') {
            try {
                $this->runMysqldump($mysqldumpPath, $host, $port, $username, $password, $database, $filePath);

                if (File::exists($filePath) && File::size($filePath) > 0) {
                    return response()->download($filePath, $fileName, [
                        'Content-Type' => 'application/sql',
                    ])->deleteFileAfterSend(true);
                }
            } catch (\Throwable $e) {
                Log::warning("mysqldump gagal dieksekusi, beralih ke dump PHP: " . $e->getMessage());
            }
        }

        // 2. Fallback: Ekspor murni via PHP / PDO jika mysqldump tidak ada atau gagal
        try {
            $this->dumpDatabaseViaPhp($filePath, $database);

            if (File::exists($filePath) && File::size($filePath) > 0) {
                return response()->download($filePath, $fileName, [
                    'Content-Type' => 'application/sql',
                ])->deleteFileAfterSend(true);
            }

            return redirect()->route('backup.index')->with('error', 'Gagal menghasilkan file backup database.');
        } catch (\Throwable $e) {
            Log::error("Error export database via PHP: " . $e->getMessage());
            return redirect()->route('backup.index')->with('error', 'Terjadi kesalahan saat membackup database: ' . $e->getMessage());
        }
    }

    /**
     * Restore database dari file .sql yang diunggah.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => ['required', 'file', 'max:102400'], // Maksimal 100MB
        ], [
            'backup_file.required' => 'Silakan pilih file backup .sql yang ingin direstore.',
            'backup_file.file' => 'File yang diunggah tidak valid.',
            'backup_file.max' => 'Ukuran file backup tidak boleh melebihi 100MB.',
        ]);

        $file = $request->file('backup_file');
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension !== 'sql') {
            return redirect()->route('backup.index')->with('error', 'File yang diunggah wajib memiliki ekstensi .sql');
        }

        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $tempPath = $file->getRealPath();

        try {
            $sqlContent = file_get_contents($tempPath);

            if (empty(trim($sqlContent))) {
                return redirect()->route('backup.index')->with('error', 'File backup kosong atau tidak dapat dibaca.');
            }

            $driver = config('database.default');

            if ($driver === 'mysql' || config("database.connections.{$driver}.driver") === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            }

            // Jalankan seluruh sintaks SQL
            DB::unprepared($sqlContent);

            return redirect()->route('backup.index')->with('success', 'Database berhasil dipulihkan (restore) sepenuhnya dari file backup!');
        } catch (\Throwable $e) {
            Log::error("Gagal melakukan restore database: " . $e->getMessage());
            return redirect()->route('backup.index')->with('error', 'Gagal merestore database: ' . $e->getMessage());
        } finally {
            try {
                $driver = config('database.default');
                if ($driver === 'mysql' || config("database.connections.{$driver}.driver") === 'mysql') {
                    DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
                }
            } catch (\Throwable $e) {
                // Abaikan error re-enable jika koneksi terputus
            }
        }
    }

    /**
     * Jalankan perintah mysqldump menggunakan Symfony Process.
     */
    protected function runMysqldump($mysqldumpPath, $host, $port, $username, $password, $database, $outputPath)
    {
        $command = [
            $mysqldumpPath,
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
            "--single-transaction",
            "--routines",
            "--triggers",
            "--quick",
            "--result-file={$outputPath}",
        ];

        if (!empty($password)) {
            $command[] = "--password={$password}";
        }

        $command[] = $database;

        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Fallback dump database menggunakan Laravel DB/PDO saat mysqldump tidak tersedia.
     */
    protected function dumpDatabaseViaPhp($filePath, $databaseName)
    {
        $handle = fopen($filePath, 'w');

        fwrite($handle, "-- ========================================================\n");
        fwrite($handle, "-- SIMPEG DATABASE BACKUP (Generated by Laravel Simpeg)\n");
        fwrite($handle, "-- Generated at: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Database: " . $databaseName . "\n");
        fwrite($handle, "-- ========================================================\n\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
        fwrite($handle, "SET time_zone = '+00:00';\n\n");

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver") ?? $connection;
        $pdo = DB::connection()->getPdo();

        if ($driver === 'mysql') {
            $rawTables = DB::select('SHOW TABLES');
            $tables = [];
            foreach ($rawTables as $row) {
                $arr = (array) $row;
                $tables[] = reset($arr);
            }
        } elseif ($driver === 'sqlite') {
            $rawTables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = array_column($rawTables, 'name');
        } else {
            $tables = [];
        }

        foreach ($tables as $tableName) {
            // Structure
            fwrite($handle, "--\n-- Table structure for table `{$tableName}`\n--\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

            if ($driver === 'mysql') {
                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createTableRow = (array) $createTableResult[0];
                $createTableSql = $createTableRow['Create Table'] ?? reset($createTableRow);
                fwrite($handle, $createTableSql . ";\n\n");
            } elseif ($driver === 'sqlite') {
                $createResult = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$tableName]);
                if (!empty($createResult) && !empty($createResult[0]->sql)) {
                    fwrite($handle, $createResult[0]->sql . ";\n\n");
                }
            }

            // Data
            fwrite($handle, "--\n-- Dumping data for table `{$tableName}`\n--\n");
            $rows = DB::table($tableName)->get();

            if ($rows->count() > 0) {
                foreach ($rows->chunk(100) as $chunk) {
                    $insertValues = [];
                    foreach ($chunk as $row) {
                        $values = [];
                        foreach ((array) $row as $val) {
                            if (is_null($val)) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote($val);
                            }
                        }
                        $insertValues[] = '(' . implode(', ', $values) . ')';
                    }
                    fwrite($handle, "INSERT INTO `{$tableName}` VALUES " . implode(",\n", $insertValues) . ";\n");
                }
            }
            fwrite($handle, "\n");
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($handle);
    }

    /**
     * Cari lokasi binary mysqldump pada sistem Windows/Linux.
     */
    protected function findMysqldumpPath(): ?string
    {
        // 1. Cek dari .env jika didefinisikan
        if ($envPath = env('MYSQLDUMP_PATH')) {
            if (File::exists($envPath)) {
                return $envPath;
            }
        }

        // 2. Cek lokasi Laragon di Windows
        $laragonGlob = glob('C:/laragon/bin/mysql/*/bin/mysqldump.exe');
        if (!empty($laragonGlob) && File::exists($laragonGlob[0])) {
            return str_replace('/', DIRECTORY_SEPARATOR, $laragonGlob[0]);
        }

        // 3. Cek lokasi XAMPP di Windows
        $xamppPath = 'C:/xampp/mysql/bin/mysqldump.exe';
        if (File::exists($xamppPath)) {
            return str_replace('/', DIRECTORY_SEPARATOR, $xamppPath);
        }

        // 4. Cek apakah mysqldump ada di PATH sistem
        $testProcess = new Process(['mysqldump', '--version']);
        try {
            $testProcess->run();
            if ($testProcess->isSuccessful()) {
                return 'mysqldump';
            }
        } catch (\Throwable $e) {
            // Bukan di PATH
        }

        return null;
    }
}
