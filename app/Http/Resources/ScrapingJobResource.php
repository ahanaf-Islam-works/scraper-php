<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScrapingJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'company_name' => $this->whenLoaded('company', fn() => $this->company->name),
            'spider' => $this->spider->value,
            'status' => $this->status->value,
            'is_terminal' => $this->status->isTerminal(),
            'retry_count' => $this->retry_count,
            'error_message' => $this->error_message,
            'records' => [
                'created' => $this->records_created,
                'updated' => $this->records_updated,
                'unchanged' => $this->records_unchanged,
                'failed' => $this->records_failed,
            ],
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
