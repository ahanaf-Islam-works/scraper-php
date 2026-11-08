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
        };
    }
}
