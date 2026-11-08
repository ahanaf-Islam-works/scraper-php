<?php

use App\Http\Controllers\Api\MessageController;
use Illuminate\Support\Facades\Route;

Route::prefix('messages')->controller(MessageController::class)->group(function () {

    Route::get('/welcome', 'welcome');

    Route::get('/', 'index');
    Route::post('/', 'store');

    Route::get('/{message}', 'show');
    Route::match(['put', 'patch'], '/{message}', 'update');
    Route::delete('/{message}', 'destroy');
});
