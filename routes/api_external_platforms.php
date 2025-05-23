<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Hoster\ExternalPlatformsController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'platforms'], function () {
        Route::post('/requestChangeUrl', [ExternalPlatformsController::class, 'requestChangeUrl']);
        Route::get('/getDataOtas', [ExternalPlatformsController::class, 'getDataOtas']);
        Route::post('/updateBulkOTAS', [ExternalPlatformsController::class, 'updateBulkOTAS']);
    });
});
