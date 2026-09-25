<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

echo "1. Flushing all cache stores...\n";
Cache::flush();

echo "2. Re-running migration up() method...\n";
$migration = require __DIR__ . '/../database/migrations/2026_09_25_000003_reorder_and_clean_master_data.php';
$migration->up();

echo "3. Clearing Artisan optimize...\n";
Artisan::call('optimize:clear');
echo Artisan::output();

echo "\n4. Verifying Master Data Jabatan count in local database...\n";
echo "Total Jabatan now: " . \App\Models\Jabatan::count() . "\n";
foreach (\App\Models\Jabatan::orderBy("id")->get() as $j) {
    echo "ID: {$j->id} | KODE: {$j->kode_jabatan} | NAMA: {$j->nama_jabatan}\n";
}
