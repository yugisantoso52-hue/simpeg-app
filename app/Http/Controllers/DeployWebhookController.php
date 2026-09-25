<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DeployWebhookController extends Controller
{
    /**
     * Webhook Endpoint untuk Auto-Deploy 100% Otomatis (Pure PHP Engine)
     */
    public function handle(Request $request)
    {
        $inputKey = $request->query('key') ?? $request->header('X-Deploy-Key') ?? $request->input('key');
        $expectedKey = env('DEPLOY_WEBHOOK_KEY', 'sikap_deploy_sec_2026_unri');

        if (empty($inputKey) || !hash_equals((string)$expectedKey, (string)$inputKey)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak. Token deploy tidak valid.'
            ], 401);
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');
        $logs = [];

        try {
            // 1. Eksekusi Pembaruan Kode via Pure PHP Engine (100% Bebas shell_exec)
            $logs['code_update'] = $this->updateCodeFromGithubZip();

            // 2. Jalankan migrasi database (jika ada penambahan kolom/tabel baru)
            Artisan::call('migrate', ['--force' => true]);
            $logs['migrate'] = trim(Artisan::output());

            // 3. Bersihkan & optimalkan cache
            Artisan::call('optimize:clear');
            $logs['optimize_clear'] = trim(Artisan::output());

            Log::info('Auto-Deploy Webhook Berhasil Eksekusi:', $logs);

            return response()->json([
                'status'  => 'success',
                'message' => 'Alhamdulillah! Server SIKAP FKP UNRI Berhasil Di-Update Otomatis 100%.',
                'logs'    => $logs
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Auto-Deploy Webhook Error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat deploy: ' . $e->getMessage(),
                'logs'    => $logs
            ], 500);
        }
    }

    /**
     * Update file proyek dari GitHub via Pure PHP Zip Engine (100% Bebas shell_exec)
     */
    protected function updateCodeFromGithubZip(): string
    {
        $zipUrl = 'https://github.com/yugisantoso52-hue/simpeg-app/archive/refs/heads/main.zip';
        $tempZip = storage_path('app/temp_deploy_main.zip');
        $tempExtractDir = storage_path('app/temp_deploy_extract');

        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "User-Agent: SIKAP-UNRI-Deployer\r\n"
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ];
        $context = stream_context_create($opts);

        $zipData = @file_get_contents($zipUrl, false, $context);
        if (!$zipData && function_exists('curl_init')) {
            $ch = curl_init($zipUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SIKAP-UNRI-Deployer');
            $zipData = curl_exec($ch);
            curl_close($ch);
        }

        if (empty($zipData)) {
            return "PHP Engine: Gagal mengunduh file update zip dari GitHub.";
        }

        File::put($tempZip, $zipData);

        if (!class_exists('ZipArchive')) {
            return "PHP Engine: Ekstensi ZipArchive tidak ditemukan pada PHP server.";
        }

        $zip = new \ZipArchive();
        if ($zip->open($tempZip) === true) {
            if (File::isDirectory($tempExtractDir)) {
                File::deleteDirectory($tempExtractDir);
            }
            File::makeDirectory($tempExtractDir, 0755, true);

            $zip->extractTo($tempExtractDir);
            $zip->close();
            @unlink($tempZip);

            $sourceDir = $tempExtractDir . '/simpeg-app-main';
            if (!File::isDirectory($sourceDir)) {
                $subdirs = File::directories($tempExtractDir);
                if (!empty($subdirs)) {
                    $sourceDir = $subdirs[0];
                }
            }

            if (File::isDirectory($sourceDir)) {
                $excluded = ['.env', 'storage', 'vendor', 'node_modules', '.git'];
                $copiedCount = 0;

                $allFiles = File::allFiles($sourceDir, true);
                foreach ($allFiles as $file) {
                    $relativePath = str_replace('\\', '/', $file->getRelativePathname());

                    $skip = false;
                    foreach ($excluded as $exc) {
                        if ($relativePath === $exc || str_starts_with($relativePath, $exc . '/')) {
                            $skip = true;
                            break;
                        }
                    }
                    if ($skip) continue;

                    $targetFile = base_path($relativePath);
                    File::ensureDirectoryExists(dirname($targetFile));
                    copy($file->getRealPath(), $targetFile);
                    $copiedCount++;
                }

                File::deleteDirectory($tempExtractDir);

                return "Pure PHP Engine: Berhasil memperbarui {$copiedCount} file kode dari GitHub main.zip!";
            }
        }

        return "PHP Engine: Gagal mengekstrak berkas zip.";
    }
}
