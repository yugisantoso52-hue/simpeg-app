<?php

use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\CekKgbHarian;
use App\Console\Commands\CekKpHarian;
use App\Console\Commands\CekSatyalancanaHarian;
use App\Console\Commands\CekPensiunHarian;

/*
|--------------------------------------------------------------------------
| SIMPEG Automations & Cron Jobs
|--------------------------------------------------------------------------
*/

// 1. Kenaikan Gaji Berkala (KGB) - Jam 01:00 WIB
Schedule::command(CekKgbHarian::class)
    ->dailyAt('01:00')
    ->timezone('Asia/Jakarta')
    ->onOneServer()
    ->runInBackground();

// 2. Kenaikan Pangkat (KP) - Jam 01:30 WIB
Schedule::command(CekKpHarian::class)
    ->dailyAt('01:30')
    ->timezone('Asia/Jakarta')
    ->onOneServer()
    ->runInBackground();

// 3. Satyalancana Karya Satya - Jam 02:00 WIB
Schedule::command(CekSatyalancanaHarian::class)
    ->dailyAt('02:00')
    ->timezone('Asia/Jakarta')
    ->onOneServer()
    ->runInBackground();

// 4. Batas Usia Pensiun (BUP) - Jam 02:30 WIB
Schedule::command(CekPensiunHarian::class)
    ->dailyAt('02:30')
    ->timezone('Asia/Jakarta')
    ->onOneServer()
    ->runInBackground();

// 5. Auto Backup Database & Storage - Setiap Hari Jam 00:00 WIB
Schedule::command(\App\Console\Commands\BackupDatabaseCommand::class)
    ->dailyAt('00:00')
    ->timezone('Asia/Jakarta')
    ->onOneServer()
    ->runInBackground();

/*
|--------------------------------------------------------------------------
| System Maintenance & Cleanup (Pemeliharaan Otomatis)
|--------------------------------------------------------------------------
*/

// Bersihkan notifikasi database yang sudah dibaca / berusia > 30 hari (Setiap Minggu Jam 03:00 WIB)
Schedule::command('model:prune')
    ->weekly()
    ->at('03:00')
    ->timezone('Asia/Jakarta');