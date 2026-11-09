<?php

namespace App\Listeners;

use App\Events\ScrapingJobCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class LogScrapingJobResult implements ShouldQueue
{
    public function handle(ScrapingJobCompleted $event): void
    {
        Log::info('Scraping job finished', [
            'job_id' => $event->job->id,
            'status' => $event->job->status->value,
            'created' => $event->job->records_created,
            'updated' => $event->job->records_updated,
        ]);
    }
}
