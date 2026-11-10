<?php

namespace App\Console\Commands;

use App\Enums\ScrapingSpiderEnum;
use App\Services\Scraping\ScrapingJobService;
use Illuminate\Console\Command;

class ScrapingDispatchCompanies extends Command
{
    protected $signature = 'scraping:dispatch-companies {--spider=airregi-jobs : Spider name}';

    protected $description = 'Dispatch scraping jobs for all eligible companies';

    public function handle(ScrapingJobService $service): int
    {
        $spider = ScrapingSpiderEnum::tryFrom((string) $this->option('spider'));

        if ($spider === null) {
            $this->error('Unknown spider. Valid: ' . implode(', ', array_column(ScrapingSpiderEnum::cases(), 'value')));

            return self::FAILURE;
        }

        $eligible = $service->eligibleCompaniesQuery($spider)->count();
        $this->info("{$eligible} eligible company(ies) for {$spider->value}");

        $jobs = $service->dispatchAll($spider);
        $this->info("Dispatched {$jobs->count()} job(s)");

        return self::SUCCESS;
    }
}
