<?php

namespace App\Console\Commands;

use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportCompaniesJson extends Command
{
    protected $signature = 'companies:export-test-json';
    protected $description = 'Exports 10 test companies into a JSON file for webhook and ingestion testing';

    public function handle(): int
    {
        $this->info('Fetching companies from database...');

        $companiesData = Company::with('indeedCredentials')->get()->map(function (Company $company) {
            // Determine resolved Indeed email or fallback status for unworkable records
            $resolvedEmail = $company->indeedCredentials?->email
                ?? ($company->serial_number ? "serial-resolved+{$company->id}@example.com" : 'Missing Credentials');

            return [
                'id' => $company->id,
                'name' => $company->name,
                'serial_number' => $company->serial_number,
                'airwork_enabled' => (bool) $company->airwork_enabled,
                'resolved_indeed_email' => $resolvedEmail,
                'total_scraping_jobs' => $company->scrapingJobs()->count() ?? 0,
            ];
        });

        $payload = [
            'companies' => $companiesData->toArray(),
        ];

        $filePath = 'test-companies.json';
        Storage::disk('local')->put($filePath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Successfully exported " . count($companiesData) . " companies!");
        $this->line("File saved to storage path: " . storage_path("app/{$filePath}"));

        return self::SUCCESS;
    }
}
