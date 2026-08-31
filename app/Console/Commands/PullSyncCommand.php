<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class PullSyncCommand extends Command
{
    protected $signature = 'simpeg:pull-sync {--all : Pull all records regardless of last sync time}';
    protected $description = 'Pull updates from central online server into local database safely without truncate';

    protected array $modelMap = [
        'pegawai' => \App\Models\Pegawai::class,
        'users' => \App\Models\User::class,
        'riwayat_jabatan' => \App\Models\RiwayatJabatan::class,
        'riwayat_pangkat' => \App\Models\RiwayatPangkat::class,
        'riwayat_pendidikan' => \App\Models\RiwayatPendidikan::class,
        'riwayat_diklat' => \App\Models\RiwayatDiklat::class,
        'mutasi_pegawai' => \App\Models\MutasiPegawai::class,
        'pengajuan_cuti' => \App\Models\PengajuanCuti::class,
        'tugas_belajar' => \App\Models\TugasBelajar::class,
        'riwayat_str_sip' => \App\Models\RiwayatStrSip::class,
        'riwayat_skp' => \App\Models\RiwayatSkp::class,
        'riwayat_penghargaan' => \App\Models\RiwayatPenghargaan::class,
        'riwayat_organisasi' => \App\Models\RiwayatOrganisasi::class,
        'riwayat_publikasi' => \App\Models\RiwayatPublikasi::class,
    ];

    public function handle()
    {
        $baseUrl = env('ONLINE_SYNC_URL', 'http://localhost/api/v1/sync/receive');
        $changesUrl = str_replace('/receive', '/changes', $baseUrl);
        $secretKey = env('SYNC_SECRET_KEY', 'default_secret_key_123!');

        $stateFile = storage_path('app/sync_pull_state.json');
        $lastSyncTime = null;

        if (!$this->option('all') && File::exists($stateFile)) {
            $state = json_decode(File::get($stateFile), true);
            $lastSyncTime = $state['last_pulled_at'] ?? null;
        }

        $this->info("Connecting to online server: {$changesUrl}");
        if ($lastSyncTime) {
            $this->line("Fetching changes since: {$lastSyncTime}");
        } else {
            $this->line("Fetching all available changes (Full Pull)");
        }

        try {
            $response = Http::withHeaders([
                'X-Sync-Secret' => $secretKey,
                'Accept' => 'application/json'
            ])->timeout(30)->get($changesUrl, array_filter([
                'since' => $lastSyncTime
            ]));

            if (!$response->successful()) {
                $this->error("Failed to fetch changes. HTTP Status: " . $response->status());
                $this->error($response->body());
                return 1;
            }

            $data = $response->json();
            $changes = $data['changes'] ?? [];
            $serverTime = $data['server_time'] ?? now()->toDateTimeString();

            if (empty($changes)) {
                $this->info("✅ Local database is already up to date. No new changes from cloud.");
                File::put($stateFile, json_encode(['last_pulled_at' => $serverTime]));
                return 0;
            }

            $totalUpdated = 0;
            $totalInserted = 0;

            foreach ($changes as $tableName => $records) {
                $modelClass = $this->modelMap[$tableName] ?? null;
                if (!$modelClass) {
                    continue;
                }

                $this->line("Processing table: <comment>{$tableName}</comment> (" . count($records) . " records)...");

                $modelClass::withoutEvents(function () use ($modelClass, $tableName, $records, &$totalUpdated, &$totalInserted) {
                    foreach ($records as $row) {
                        $uuid = $row['sync_uuid'] ?? null;
                        if (!$uuid) {
                            continue;
                        }

                        // Remove id to avoid local primary key collision
                        unset($row['id']);

                        $existing = $modelClass::where('sync_uuid', $uuid)->first();

                        if (!$existing) {
                            if ($tableName === 'users' && !empty($row['email'])) {
                                $existing = $modelClass::where('email', $row['email'])->first();
                            } elseif ($tableName === 'pegawai' && !empty($row['nip'])) {
                                $existing = $modelClass::where('nip', $row['nip'])->first();
                            }
                        }

                        $row['last_synced_at'] = now();

                        if ($existing) {
                            $existing->forceFill($row)->save();
                            $totalUpdated++;
                        } else {
                            if ($tableName === 'users' && empty($row['password'])) {
                                $row['password'] = \Illuminate\Support\Facades\Hash::make('password');
                            }
                            $instance = new $modelClass;
                            $instance->forceFill($row)->save();
                            $totalInserted++;
                        }
                    }
                });
            }

            File::put($stateFile, json_encode(['last_pulled_at' => $serverTime]));

            $this->info("\n✅ Pull sync completed successfully!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Records Updated', $totalUpdated],
                    ['Records Inserted', $totalInserted],
                    ['Server Sync Time', $serverTime]
                ]
            );

            return 0;
        } catch (\Exception $e) {
            $this->error("Sync Exception: " . $e->getMessage());
            return 1;
        }
    }
}