<?php

use App\Http\Controllers\Api\JobIngestController;
use App\Http\Controllers\Api\ScrapingController;
use Illuminate\Support\Facades\Route;

Route::get('/companies', [ScrapingController::class, 'index']);
Route::get('/companies/{company}', [ScrapingController::class, 'show']);
Route::post('/ingest/{provider}', [JobIngestController::class, 'store']);
