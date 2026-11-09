<?php

namespace App\Enums;

enum ScrapingSpiderEnum: string
{
    case IndeedJobDetails = 'indeed-job-details';
    case IndeedJobApplications = 'indeed-job-applications';
    case AirregiJobs = 'airregi-jobs';

    public function isIndeed(): bool
    {
        return match ($this) {
            self::IndeedJobDetails, self::IndeedJobApplications => true,
            self::AirregiJobs => false,
        };
    }

    public function provider(): string
    {
        return $this->isIndeed() ? 'indeed' : 'airwork';
    }

    public function callbackPath(): string
    {
        return "/api/scraping-jobs/{$this->value}/complete";
    }
}
