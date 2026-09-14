<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogsCommand extends Command
{
    /**
     * Nama dan tanda tangan perintah konsol.
     */
    protected $signature = 'app:prune-activity-logs {--days=90 : Jumlah hari penyimpanan log aktivitas}';

    /**
     * Deskripsi perintah konsol.
     */
    protected $description = 'Bersihkan log aktivitas yang sudah melebihi batas hari penyimpanan agar database tetap ringan';

    /**
     * Eksekusi perintah konsol.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $deleted = ActivityLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Berhasil membersihkan {$deleted} baris log aktivitas yang berusia lebih dari {$days} hari.");
        return 0;
    }
}
