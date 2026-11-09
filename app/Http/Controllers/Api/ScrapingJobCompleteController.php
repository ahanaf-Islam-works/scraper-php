<?php

namespace App\Http\Controllers\Api;

use App\Enums\ScrapingSpiderEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScrapingJobCompleteRequest;
use App\Http\Resources\ScrapingJobResource;
use App\Services\Scraping\ScrapingJobService;
use Illuminate\Http\JsonResponse;

class ScrapingJobCompleteController extends Controller
{
    public function __construct(private ScrapingJobService $service) {}

    public function __invoke(ScrapingJobCompleteRequest $request, string $spider): JsonResponse
    {
        $spiderEnum = ScrapingSpiderEnum::tryFrom($spider) ?? abort(404, 'Unknown spider');

        $validated = $request->validated();
        $job = $request->scrapingJob();

        abort_if($job->spider !== $spiderEnum, 422, 'Spider does not match the job');

        $result = $this->service->handleComplete(
            job: $job,
            status: $validated['status'],
            records: $validated['records'] ?? [],
        );

        return response()->json([
            'message' => 'Scraping job updated',
            'data' => ScrapingJobResource::make($result),
        ]);
    }
}
