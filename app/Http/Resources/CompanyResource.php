<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'serial_number' => $this->serial_number,
            'airwork_enabled' => $this->airwork_enabled,
            'indeed_email' => $this->whenLoaded('indeedCredentials', fn() => $this->indeedCredentials?->email),
        ];
    }
}
