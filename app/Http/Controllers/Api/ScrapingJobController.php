<?php

namespace App\Http\Controllers\Api;

use App\Enums\ScrapingJobStatusEnum;
use App\Enums\ScrapingSpiderEnum;
use App\Exceptions\MissingCredentialsException;
use App\Exceptions\ScrapingJobAlreadyRunningException;
use App\Http\Controllers\Controller;
use App\Http\Requests\TriggerScrapingJobRequest;
use App\Http\Resources\ScrapingJobResource;
use App\Models\Company;
use App\Models\ScrapingJob;
use App\Services\Scraping\ScrapingJobService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ScrapingJobController extends Controller
{
    public function __construct(private ScrapingJobService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'company_id' => ['sometimes', 'integer'],
            'status' => ['sometimes', Rule::enum(ScrapingJobStatusEnum::class)],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $jobs = ScrapingJob::query()
            ->with('company')
            ->when($filters['company_id'] ?? null, fn(Builder $q, $id) => $q->where('company_id', $id))
            ->when($filters['status'] ?? null, fn(Builder $q, $status) => $q->where('status', $status))
            ->latest('id')
            ->paginate($filters['per_page'] ?? 15);

        return ScrapingJobResource::collection($jobs);
    }

    public function show(ScrapingJob $scrapingJob): ScrapingJobResource
    {
        return ScrapingJobResource::make($scrapingJob->load('company'));
    }

    public function status(Request $request): JsonResponse
    {
        $data = $request->validate([
            'company_id' => ['required', 'integer'],
            'spider' => ['required', Rule::enum(ScrapingSpiderEnum::class)],
        ]);

        $spider = ScrapingSpiderEnum::from($data['spider']);
        $running = $this->service->runningJob((int) $data['company_id'], $spider);

        return response()->json(['data' => [
            'company_id' => (int) $data['company_id'],
            'spider' => $spider->value,
            'is_running' => $running !== null,
        ]]);
    }

    public function store(TriggerScrapingJobRequest $request): JsonResponse
    {
        $company = Company::query()
            ->with('indeedCredentials')
            ->findOrFail($request->validated('company_id'));

        $spider = $request->enum('spider', ScrapingSpiderEnum::class)
            ?? ScrapingSpiderEnum::AirregiJobs;

        try {
            $job = $this->service->dispatch($company, $spider);
        } catch (ScrapingJobAlreadyRunningException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (MissingCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Scraping job dispatched',
            'data' => ScrapingJobResource::make($job->load('company')),
        ], 201);
    }

    public function triggerAll(Request $request): JsonResponse
    {
        $request->validate(['spider' => ['sometimes', Rule::enum(ScrapingSpiderEnum::class)]]);

        $spider = $request->enum('spider', ScrapingSpiderEnum::class) ?? ScrapingSpiderEnum::AirregiJobs;
        $jobs = $this->service->dispatchAll($spider);

        return response()->json([
            'message' => "{$jobs->count()} job(s) dispatched",
            'data' => ScrapingJobResource::collection($jobs),
        ]);
    }

    public function retry(ScrapingJob $scrapingJob): JsonResponse
    {
        if (! $scrapingJob->status->isRetryable()) {
            return response()->json(['message' => 'Only failed or timed-out jobs can be retried'], 422);
        }

        try {
            $job = $this->service->retry($scrapingJob);
        } catch (ScrapingJobAlreadyRunningException | MissingCredentialsException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        return response()->json(['data' => ScrapingJobResource::make($job)], 201);
    }
}
