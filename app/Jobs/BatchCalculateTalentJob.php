<?php

namespace App\Jobs;

use App\Services\TalentScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BatchCalculateTalentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 600;

    public function __construct(
        public int $year
    ) {}

    public function handle(TalentScoringService $talentScoringService): void
    {
        Log::info("Memulai kalkulasi batch manajemen talenta untuk tahun {$this->year} via background job...");

        try {
            $talentScoringService->batchCalculate($this->year);
            Log::info("Kalkulasi batch manajemen talenta tahun {$this->year} berhasil diselesaikan.");
        } catch (\Throwable $e) {
            Log::error("Gagal melakukan batchCalculate talenta tahun {$this->year}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }
}
