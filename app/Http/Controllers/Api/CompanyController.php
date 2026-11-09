<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Resources\CompanyResource;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return CompanyResource::collection(
            Company::query()->with('indeedCredentials')->orderBy('id')->get()
        );
    }

    public function store(StoreCompanyRequest $request): JsonResponse
    {
        $data = $request->validated() + ['airwork_enabled' => false];

        $company = DB::transaction(function () use ($data) {
            $company = Company::query()->create(Arr::only($data, ['name', 'serial_number', 'airwork_enabled']));

            if (filled($data['indeed_email'] ?? null)) {
                $company->indeedCredentials()->create(['email' => $data['indeed_email']]);
            }

            return $company;
        });

        return CompanyResource::make($company->load('indeedCredentials'))
            ->response()
            ->setStatusCode(201);
    }
}
