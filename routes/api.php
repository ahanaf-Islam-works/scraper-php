<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'message' => 'API working',
    ]);
});


Route::group([], function () {
    require __DIR__ . '/api/message.php';
});

Route::group([], function () {
    require __DIR__ . '/api/scraper.php';
});


Route::group([], function () {
    require __DIR__ . '/api/admin.php';
});
