<?php

use App\Http\Controllers\Api\ScrapingController;
use Illuminate\Support\Facades\Route;

Route::get('/companies', [ScrapingController::class, 'index']);
Route::get('/companies/{company}/scrape-tracker', [ScrapingController::class, 'show']);
