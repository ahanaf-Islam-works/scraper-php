<?php

use Illuminate\Routing\Route;



Route::prefix('admin')->middleware('protected')->group(function () {
    Route::get('companies', function () {
        return 'test';
    });
});
