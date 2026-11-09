<?php

namespace App\Services;

use App\Services\Interfaces\ProviderRawStoreInterface;
use App\Traits\UpsertsPostings;

class GenericRawStore implements ProviderRawStoreInterface
{
    use UpsertsPostings;

    public function __construct(protected string $provider) {}

    public function provider(): string
    {
        return $this->provider;
    }

    public function upsert(string $externalId, array $record, string $hash, ?int $scrapingJobId): array
    {
        return $this->upsertPosting($this->provider, $externalId, $record, $hash, $scrapingJobId);
    }
}
