<?php

namespace App\Services\Interfaces;

use App\Enums\IngestOutcomeEnum;
use App\Models\ScrapedPosting;

interface ProviderRawStoreInterface
{
    /** Provider key, e.g. "indeed". */
    public function provider(): string;

    /**
     * @param  array<string, mixed>  $record
     * @return array{0: ScrapedPosting, 1: IngestOutcomeEnum}
     */
    public function upsert(string $externalId, array $record, string $hash, ?int $scrapingJobId): array;
}
