<?php

namespace App\Services\Scraping;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use App\Events\ScrapingJobCompleted;
use App\Exceptions\MissingCredentialsException;
use App\Exceptions\ScrapingJobAlreadyRunningException;
use App\Models\Company;
use App\Models\ScrapingJob;
use App\Services\IngestPostingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ScrapingJobService
{
    public function __construct(private IngestPostingService $ingest) {}

    /** Create a job and "send it to the scraper" (here: write the payload to the log). */
    public function dispatch(Company $company, ScrapingSpiderEnum $spider): ScrapingJob
    {
        $credentials = $this->buildCredentials($company, $spider);

        $lock = Cache::lock("scraping-job:dispatch:{$company->id}:{$spider->value}", 10);

        if (! $lock->get()) {
            throw new ScrapingJobAlreadyRunningException('Another dispatch is in progress, try again.');
        }

        try {
            if ($this->runningJob($company->id, $spider) !== null) {
                throw new ScrapingJobAlreadyRunningException(
                    "A {$spider->value} job is already running for company {$company->id}"
                );
            }

            $job = ScrapingJob::query()->create([
                'company_id' => $company->id,
                'spider' => $spider,
                'status' => ScrapingJobStatusEnum::Pending,
                'callback_secret' => Str::random(40),
            ]);

            Log::info('Scraping payload ready (production: sent to SQS)', [
                'job_id' => $job->id,
                'spider' => $spider->value,
                'credentials' => $credentials,
                'callback_url' => url($spider->callbackPath()),
            ]);

            return $job->refresh();
        } finally {
            $lock->release();
        }
    }

    public function runningJob(int $companyId, ScrapingSpiderEnum $spider): ?ScrapingJob
    {
        return ScrapingJob::query()
            ->where('company_id', $companyId)
            ->where('spider', $spider->value)
            ->active()
            ->where('created_at', '>=', now()->subMinutes(config('scraping.stale_job_timeout')))
            ->latest('id')
            ->first();
    }

    /**
     * Called by the scraper callback.
     *
     * @param  array<int, array{external_id: string, record: array<string, mixed>}>  $records
     */
    public function handleComplete(ScrapingJob $job, string $status, array $records): ScrapingJob
    {
        if ($status !== 'success') {
            $job->update([
                'status' => ScrapingJobStatusEnum::Failed,
                'error_message' => 'Scraper reported a failure',
                'completed_at' => now(),
            ]);

            ScrapingJobCompleted::dispatch($job);

            return $job->refresh();
        }

        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'failed' => 0];

        foreach ($records as $entry) {
            try {
                $outcome = $this->ingest->ingest($job->spider->provider(), $entry, $job->id);
                $counts[$outcome->value]++;
            } catch (Throwable $e) {
                $counts['failed']++;
                Log::error('Ingest failed for one record', [
                    'job_id' => $job->id,
                    'external_id' => $entry['external_id'] ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $job->update([
            'status' => ScrapingJobStatusEnum::Completed,
            'records_created' => $counts['created'],
            'records_updated' => $counts['updated'],
            'records_unchanged' => $counts['unchanged'],
            'records_failed' => $counts['failed'],
            'completed_at' => now(),
        ]);

        ScrapingJobCompleted::dispatch($job);

        return $job->refresh();
    }

    public function retry(ScrapingJob $failed): ScrapingJob
    {
        $new = $this->dispatch($failed->company, $failed->spider);
        $new->update(['retry_count' => $failed->retry_count + 1]);

        return $new;
    }

    /** @return Collection<int, ScrapingJob> */
    public function dispatchAll(ScrapingSpiderEnum $spider): Collection
    {
        return $this->eligibleCompaniesQuery($spider)
            ->get()
            ->map(function (Company $company) use ($spider): ?ScrapingJob {
                try {
                    return $this->dispatch($company, $spider);
                } catch (ScrapingJobAlreadyRunningException|MissingCredentialsException $e) {
                    Log::warning('Skipped company during dispatchAll', [
                        'company_id' => $company->id,
                        'reason' => $e->getMessage(),
                    ]);

                    return null;
                }
            })
            ->filter()
            ->values();
    }

    /** One place decides who is eligible for which spider (cron, command, and button cannot drift). */
    public function eligibleCompaniesQuery(ScrapingSpiderEnum $spider): Builder
    {
        if ($spider->isIndeed()) {
            return Company::query()->where(function (Builder $query): void {
                $query
                    ->whereHas('indeedCredentials', fn (Builder $q) => $q->whereNotNull('email'))
                    ->orWhereNotNull('serial_number');
            });
        }

        return Company::query()->where('airwork_enabled', true);
    }

    /** @return array<string, mixed> */
    private function buildCredentials(Company $company, ScrapingSpiderEnum $spider): array
    {
        return match (true) {
            $spider->isIndeed() => ['email' => $company->resolveIndeedLoginEmail()],
            $company->airwork_enabled => ['airwork' => true],
            default => throw new MissingCredentialsException("Company {$company->id} is not enabled for Airwork"),
        };
    }
}
