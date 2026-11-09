<?php

namespace Database\Factories;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use App\Models\Company;
use App\Models\ScrapingJob;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ScrapingJob>
 */
class ScrapingJobFactory extends Factory
{
    protected $model = ScrapingJob::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'spider' => ScrapingSpiderEnum::IndeedJobApplications,
            'status' => ScrapingJobStatusEnum::Pending,
            'callback_secret' => Str::random(40),
        ];
    }
}
