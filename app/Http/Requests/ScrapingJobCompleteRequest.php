<?php

namespace App\Http\Requests;

use App\Models\ScrapingJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ScrapingJobCompleteRequest extends FormRequest
{
    private ?ScrapingJob $scrapingJob = null;

    public function scrapingJob(): ?ScrapingJob
    {
        $id = $this->input('job_id');

        if (! is_numeric($id)) {
            return null;
        }

        return $this->scrapingJob ??= ScrapingJob::query()->find((int) $id);
    }

    public function authorize(): bool
    {
        $job = $this->scrapingJob();

        if ($job === null) {
            return false;
        }

        // The signature is computed over the RAW request body with a per-job secret.
        $expected = hash_hmac('sha256', $this->getContent(), $job->callback_secret);

        return hash_equals($expected, (string) $this->header('X-HMAC-Signature'));
    }

    public function rules(): array
    {
        return [
            'job_id' => ['required', 'integer'],
            'status' => ['required', Rule::in(['success', 'failed'])],
            'records' => ['sometimes', 'array'],
            'records.*.external_id' => ['required', 'string', 'max:255'],
            'records.*.record' => ['required', 'array'],
        ];
    }
}
