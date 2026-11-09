<?php

namespace App\Service;

use App\Enums\IngestOutcomeEnum;
use App\Jobs\SyncPostingJob;
use App\Services\ProviderRegistry;

class IngestPostingService
{
    public function __construct(private ProviderRegistry $registry) {}

    public function ingest(string $provider, array $entry, ?int $scrapingJobId = null): IngestOutcomeEnum
    {
        $hash = hash('sha256', json_encode($entry['record'], JSON_THROW_ON_ERROR));

        [$posting, $outcome] = $this->registry
            ->rawStore($provider)
            ->upsert($entry['external_id'], $entry['record'], $hash, $scrapingJobId);

        if ($outcome !== IngestOutcomeEnum::Unchanged) {
            SyncPostingJob::dispatch($posting->id);
        }

        return $outcome;
    }
}
