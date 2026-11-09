<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API working',
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/admin/test', function () {
        return response()->json([
            'message' => 'Authorized',
        ]);
    });
});

Route::group([], function () {
    require __DIR__.'/api/message.php';
});

Route::group([], function () {
    require __DIR__.'/api/scraper.php';
});
