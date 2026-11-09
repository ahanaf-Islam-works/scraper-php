<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProviderRegistry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JobIngestController extends Controller
{
    // Laravel automatically injects our ProviderRegistry here via Service Container
    public function __construct(
        protected ProviderRegistry $registry
    ) {}

    public function store(Request $request, string $provider): JsonResponse
    {
        try {
            // 1. Ask the registry for the correct store based on the URL parameter (e.g., 'indeed')
            // If someone types an invalid provider, this automatically throws an exception.
            $store = $this->registry->rawStore($provider);

            // 2. Extract incoming data sent by the external scraper
            $externalId = $request->input('external_id');
            $record = $request->input('record');
            $hash = $request->input('hash', md5(json_encode($record)));
            $scrapingJobId = $request->input('scraping_job_id');

            // 3. Run the upsert using the store interface
            [$posting, $outcome] = $store->upsert(
                externalId: $externalId,
                record: $record,
                hash: $hash,
                scrapingJobId: $scrapingJobId
            );

            // 4. Return a successful JSON response back to the external scraper
            return response()->json([
                'status' => 'success',
                'provider' => $provider,
                'outcome' => $outcome->value, // e.g., 'created', 'updated', etc.
                'posting_id' => $posting->id,
            ], 200);
        } catch (\InvalidArgumentException $e) {
            // Catches unknown providers (e.g., someone hitting /api/ingest/bad-provider)
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        } catch (\Throwable $e) {
            // Catches database or unexpected errors
            Log::error("Job ingest failed for {$provider}: " . $e->getMessage());

            return response()->json([
                'error' => 'Internal server error processing ingestion.'
            ], 500);
        }
    }
}
