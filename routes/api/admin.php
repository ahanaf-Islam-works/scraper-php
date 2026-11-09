<?php

use App\Http\Controllers\Api\CompanyController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('protected')->group(function () {
    Route::get('companies', [CompanyController::class, 'index']);
});
