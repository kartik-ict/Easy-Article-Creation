<?php

namespace App\Console\Commands;

use App\Models\BolImageImportBatch;
use App\Services\BolOfferImportService;
use Illuminate\Console\Command;

/**
 * Picks up any image-import batches still sitting at PENDING and checks Bol for a final status,
 * so a "Push images" admin action doesn't require someone to come back and click "Check status"
 * manually. Registered on the schedule in Console/Kernel.php.
 */
class PollBolImportBatches extends Command
{
    protected $signature = 'bol:poll-import-batches';

    protected $description = 'Poll Bol for the status of any still-pending image import batches';

    public function handle(BolOfferImportService $service): int
    {
        $pending = BolImageImportBatch::where('status', 'PENDING')->get();

        foreach ($pending as $batch) {
            $service->pollBatchStatus($batch);
        }

        $this->info(sprintf('Polled %d pending batch(es).', $pending->count()));

        return self::SUCCESS;
    }
}
