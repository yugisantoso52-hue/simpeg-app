<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class PushSyncCommand extends Command
{
    protected $signature = 'simpeg:push-sync';
    protected $description = 'Push local sync outboxes to central online server';

    public function handle()
    {
        $targetUrl = env('ONLINE_SYNC_URL', 'http://localhost/api/v1/sync/receive');
        $secretKey = env('SYNC_SECRET_KEY', 'default_secret_key_123!');

        $outboxes = DB::table('sync_outboxes')
            ->whereIn('status', ['PENDING', 'FAILED'])
            ->where('attempts', '<', 3)
            ->orderBy('id', 'asc')
            ->limit(50)
            ->get();

        if ($outboxes->isEmpty()) {
            $this->info('No pending sync data found.');
            return 0;
        }

        $this->info('Found ' . $outboxes->count() . ' records to sync.');

        foreach ($outboxes as $box) {
            try {
                $response = Http::withHeaders([
                    'X-Sync-Secret' => $secretKey,
                    'Accept' => 'application/json'
                ])->timeout(10)->post($targetUrl, [
                    'table_name' => $box->table_name,
                    'sync_uuid' => $box->sync_uuid,
                    'action' => $box->action,
                    'payload' => $box->payload
                ]);

                if ($response->successful()) {
                    DB::table('sync_outboxes')->where('id', $box->id)->update([
                        'status' => 'SYNCED',
                        'updated_at' => now()
                    ]);
                    $this->line("✅ Synced: {$box->table_name} ({$box->action})");
                } else {
                    $this->markFailed($box->id, 'HTTP ' . $response->status());
                }
            } catch (\Exception $e) {
                $this->markFailed($box->id, $e->getMessage());
            }
        }
        return 0;
    }

    protected function markFailed($id, $error)
    {
        DB::table('sync_outboxes')->where('id', $id)->update([
            'status' => 'FAILED',
            'error_message' => substr($error, 0, 255),
            'attempts' => DB::raw('attempts + 1'),
            'updated_at' => now()
        ]);
        $this->error("❌ Failed Sync ID: {$id}");
    }
}