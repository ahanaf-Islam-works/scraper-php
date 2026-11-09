<?php

namespace App\Jobs;

use App\Models\ScrapedPosting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncPostingJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $postingId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $posting = ScrapedPosting::query()->find($this->postingId);

        if ($posting === null) {
            return;
        }

        $posting->update(['synced_at' => now()]);

        Log::info('Posting synced', ['id' => $posting->id, 'external_id' => $posting->external_id]);
    }
}
