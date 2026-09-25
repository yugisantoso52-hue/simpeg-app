<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class DeployWebhookController extends Controller
{
    /**
     * Webhook Endpoint untuk Auto-Deploy (git pull, migrate, optimize)
     * Dapat dipanggil via HTTP GET atau POST dengan secret key
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
        $logs = [];

        try {
            $basePath = base_path();

            // 1. Jalankan git pull origin main
            $gitCmd = "cd {$basePath} && git pull origin main 2>&1";
            $gitOutput = shell_exec($gitCmd);
            $logs['git_pull'] = trim((string)$gitOutput);

            // 2. Jalankan migrasi database
            Artisan::call('migrate', ['--force' => true]);
            $logs['migrate'] = trim(Artisan::output());

            // 3. Bersihkan & optimalkan cache
            Artisan::call('optimize:clear');
            $logs['optimize_clear'] = trim(Artisan::output());

            Log::info('Auto-Deploy Webhook Berhasil Eksekusi:', $logs);

            return response()->json([
                'status'  => 'success',
                'message' => 'Alhamdulillah! Server SIKAP FKP UNRI Berhasil Di-Update Otomatis.',
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
}
