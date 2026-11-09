<?php

namespace App\Traits;

use App\Enums\IngestOutcomeEnum;
use App\Models\ScrapedPosting;

trait UpsertsPostings
{

    /**
     * @param  array<string, mixed>  $record
     * @return array{0: ScrapedPosting, 1: IngestOutcomeEnum}
     */

    protected function upsertPosting(
        string $provider,
        string $externalId,
        array $record,
        string $hash,
        ?int $scrapingJobId
    ): array {

        $posting = ScrapedPosting::query()
            ->where('provider', $provider)
            ->where('external_id', $externalId)
            ->first();

        if ($posting === null) {
            $posting = ScrapedPosting::query()->create([
                'scraping_job_id' => $scrapingJobId,
                'provider' => $provider,
                'external_id' => $externalId,
                'row_hash' => $hash,
                'payload' => $record,
            ]);

            return [$posting, IngestOutcomeEnum::Created];
        }

        if ($posting->row_hash === $hash) {
            return [$posting, IngestOutcomeEnum::Unchanged];
        }

        $posting->update([
            'scraping_job_id' => $scrapingJobId,
            'row_hash' => $hash,
            'payload' => $record,
            'synced_at' => null,
        ]);

        return [$posting, IngestOutcomeEnum::Updated];
    }
}
