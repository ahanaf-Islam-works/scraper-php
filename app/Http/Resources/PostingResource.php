<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'external_id' => $this->external_id,
            'row_hash' => $this->row_hash,
            'payload' => $this->payload,
            'scraping_job_id' => $this->scraping_job_id,
            'synced_at' => $this->synced_at?->toISOString(),
        ];
    }
}
