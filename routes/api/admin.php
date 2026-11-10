<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PostingController;
use App\Http\Controllers\Api\ScrapingJobCompleteController;
use App\Http\Controllers\Api\ScrapingJobController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('protected')->group(function () {
    Route::get('companies', [CompanyController::class, 'index']);
    Route::post('companies', [CompanyController::class, 'store']);

    Route::get('postings', [PostingController::class, 'index']);

    Route::get('scraping-jobs', [ScrapingJobController::class, 'index']);
    Route::get('scraping-jobs/status', [ScrapingJobController::class, 'status']);
    Route::get('scraping-jobs/{scrapingJob}', [ScrapingJobController::class, 'show']);
    Route::post('scraping-jobs', [ScrapingJobController::class, 'store']);
    Route::post('scraping-jobs/trigger-all', [ScrapingJobController::class, 'triggerAll']);
    Route::post('scraping-jobs/{scrapingJob}/retry', [ScrapingJobController::class, 'retry']);
});

// Machine-to-machine callback: protected by the HMAC signature, not by the admin key.
Route::post('scraping-jobs/{spider}/complete', ScrapingJobCompleteController::class);
