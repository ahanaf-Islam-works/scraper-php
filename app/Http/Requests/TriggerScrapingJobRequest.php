<?php

namespace App\Http\Requests;

use App\Enums\ScrapingSpiderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TriggerScrapingJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'spider' => ['sometimes', 'string', Rule::enum(ScrapingSpiderEnum::class)],
        ];
    }
}
