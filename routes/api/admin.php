<?php

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\PostingController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('protected')->group(function () {
    Route::get('companies', [CompanyController::class, 'index']);
    Route::post('companies', [CompanyController::class, 'store']);

    Route::get('postings', [PostingController::class, 'index']);
});
