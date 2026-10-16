<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DeployWebhookController extends Controller
{
    /**
     * Webhook Endpoint untuk Auto-Deploy (Secured with HMAC-SHA256)
     *
     * Accepts GitHub-style webhook payloads with X-Hub-Signature-256 header.
     * The patch_file mechanism has been removed for security (RCE risk).
     */
    public function handle(Request $request)
    {
        $secret = config('services.deploy.webhook_secret') ?: env('DEPLOY_WEBHOOK_KEY', 'sikap_deploy_sec_2026_unri');

        $inputKey  = $request->input('key') ?? $request->query('key') ?? $request->header('X-Deploy-Key');
        $signature = $request->header('X-Hub-Signature-256');

        $isAuthorized = false;

        // 1. Verifikasi via X-Hub-Signature-256 (GitHub Webhook HMAC-SHA256)
        if (!empty($signature)) {
            $computedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
            if (hash_equals($computedSignature, $signature)) {
                $isAuthorized = true;
            }
        }

        // 2. Verifikasi via Token / Deploy Key (CLI Runner / PHP Direct Invocation / POST key)
        if (!$isAuthorized && !empty($inputKey) && !empty($secret)) {
            if (hash_equals((string)$secret, (string)$inputKey)) {
                $isAuthorized = true;
            }
        }

        // 3. Fallback jika dipanggil langsung via CLI container (php -r ...)
        if (!$isAuthorized && app()->runningInConsole() && !empty($inputKey)) {
            if (hash_equals((string)$secret, (string)$inputKey)) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            Log::warning('Deploy webhook unauthorized attempt.', [
                'ip'            => $request->ip(),
                'has_signature' => !empty($signature),
                'has_key'       => !empty($inputKey),
            ]);
            abort(403, 'Akses ditolak. Token atau signature deploy tidak valid.');
        }

        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $logs = [];

        try {
            // 1. Update code from GitHub zip (with SSL verification enabled)
            $logs['code_update'] = $this->updateCodeFromGithubZip();

            // 2. Run database migrations
            Artisan::call('migrate', ['--force' => true]);
            $logs['migrate'] = trim(Artisan::output());

            // 3. Clear and optimize caches
            Artisan::call('optimize:clear');
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }
            $logs['optimize_clear'] = trim(Artisan::output());

            Log::info('Auto-Deploy Webhook executed successfully.', $logs);

            return response()->json([
                'status'  => 'success',
                'message' => 'Deployment completed successfully.',
                'logs'    => $logs
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Auto-Deploy Webhook Error: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Deployment failed: ' . $e->getMessage(),
                'logs'    => $logs
            ], 500);
        }
    }

    /**
     * Update project files from GitHub via PHP Zip Engine (with SSL verification enabled)
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
                "verify_peer" => true,
                "verify_peer_name" => true
            ]
        ];
        $context = stream_context_create($opts);

        $zipData = @file_get_contents($zipUrl, false, $context);
        if (!$zipData && function_exists('curl_init')) {
            $ch = curl_init($zipUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SIKAP-UNRI-Deployer');
            $zipData = curl_exec($ch);
            curl_close($ch);
        }

        if (empty($zipData)) {
            return "PHP Engine: Failed to download update zip from GitHub.";
        }

        File::put($tempZip, $zipData);

        if (!class_exists('ZipArchive')) {
            return "PHP Engine: ZipArchive extension not found.";
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

                return "PHP Engine: Successfully updated {$copiedCount} files from GitHub main.zip.";
            }
        }

        return "PHP Engine: Failed to extract zip file.";
    }
}
