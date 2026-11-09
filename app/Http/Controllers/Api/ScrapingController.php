<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;

class ScrapingController extends Controller
{
    /**
     * Get dashboard tracking info for a specific company.
     */
    public function show(Company $company): JsonResponse
    {
        // Eager load relations with active jobs scope and latest postings
        $company->load([
            'indeedCredentials',
            'scrapingJobs' => function ($query) {
                $query->latest();
            },
            'scrapingJobs.postings',
        ]);

        try {
            $loginEmail = $company->resolveIndeedLoginEmail();
        } catch (\Exception $e) {
            $loginEmail = null;
        }

        return response()->json([
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'serial_number' => $company->serial_number,
                'airwork_enabled' => $company->airwork_enabled,
                'resolved_indeed_email' => $loginEmail,
            ],
            'active_jobs' => $company->scrapingJobs()->active()->get(),
            'all_jobs' => $company->scrapingJobs,
        ]);
    }

    /**
     * List all companies with brief summary counts.
     */
    public function index(): JsonResponse
    {

        $companies = Company::with(['indeedCredentials'])
            ->withCount('scrapingJobs')
            ->get()
            ->map(function ($company) {
                // Safe email resolution for the summary row
                try {
                    $resolvedEmail = $company->resolveIndeedLoginEmail();
                } catch (\Exception $e) {
                    $resolvedEmail = 'Missing Credentials';
                }

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'serial_number' => $company->serial_number,
                    'airwork_enabled' => $company->airwork_enabled,
                    'resolved_indeed_email' => $resolvedEmail,
                    'total_scraping_jobs' => $company->scraping_jobs_count,
                ];
            });

        return response()->json([
            'companies' => $companies,
        ]);
    }
}
