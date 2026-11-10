<?php

namespace App\Console\Commands;

use App\Models\ScrapingJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SimulateScraper extends Command
{
    protected $signature = 'scraper:simulate
        {job : ScrapingJob id}
        {--fail : Report a failure}
        {--update : Change the first record so it is detected as updated}
        {--prefix= : Prefix external IDs so each demo run gets fresh postings}';

    protected $description = 'Act as the scraper: send a signed completion callback to the API';

    public function handle(): int
    {
        $jobId = (int) $this->argument('job');

        if ($jobId < 1) {
            $this->error('A valid scraping job ID is required. Example: php artisan scraper:simulate 1');

            return self::FAILURE;
        }

        $job = ScrapingJob::query()->findOrFail($jobId);
        $prefix = trim((string) $this->option('prefix')) ?: $job->spider->value;

        $records = [];
        foreach (range(1, 3) as $i) {
            $title = "Demo posting {$i}".($this->option('update') && $i === 1 ? ' (updated)' : '');
            $records[] = [
                'external_id' => "{$prefix}-{$i}",
                'record' => ['title' => $title, 'location' => 'Tokyo', 'salary' => 1000 + $i * 100],
            ];
        }

        $payload = [
            'job_id' => $job->id,
            'status' => $this->option('fail') ? 'failed' : 'success',
            'records' => $this->option('fail') ? [] : $records,
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $body, $job->callback_secret);
        $url = rtrim(config('app.url'), '/').$job->spider->callbackPath();

        $response = Http::withHeaders(['X-HMAC-Signature' => $signature, 'Accept' => 'application/json'])
            ->withBody($body, 'application/json')
            ->post($url);

        $this->info("POST {$url} -> HTTP {$response->status()}");
        $this->line($response->body());

        return $response->successful() ? self::SUCCESS : self::FAILURE;
    }
}
