<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FullSyncCommand extends Command
{
    protected $signature = 'simpeg:sync {--all : Perform full pull synchronization}';
    protected $description = 'Perform two-way synchronization: push local outboxes then pull cloud updates';

    public function handle()
    {
        $this->info("==================================================");
        $this->info("   SIMPEG TWO-WAY SYNCHRONIZATION (OFFLINE <-> ONLINE)   ");
        $this->info("==================================================");

        $this->line("\n[1/2] PUSH: Sending local changes to online server...");
        $pushExit = $this->call('simpeg:push-sync');

        $this->line("\n[2/2] PULL: Fetching latest changes from online server...");
        $pullParams = $this->option('all') ? ['--all' => true] : [];
        $pullExit = $this->call('simpeg:pull-sync', $pullParams);

        $this->info("\n==================================================");
        if ($pushExit === 0 && $pullExit === 0) {
            $this->info("🎉 TWO-WAY SYNC COMPLETED SUCCESSFULLY!");
        } else {
            $this->warn("⚠️ Two-way sync completed with warnings or errors. Please review output above.");
        }
        $this->info("==================================================");

        return ($pushExit === 0 && $pullExit === 0) ? 0 : 1;
    }
}