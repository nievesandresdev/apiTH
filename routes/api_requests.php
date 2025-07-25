<?php

use App\Http\Controllers\Api\Hoster\RequestReviewsSettingsController;
use App\Http\Controllers\Api\RequestSettingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'request-settings'], function () {
        Route::get('/getAll', [RequestSettingController::class, 'getAll']);
        Route::get('/getRequestData', [RequestSettingController::class, 'getRequestData']);
        Route::group(['prefix' => 'hoster'], function () {
            Route::post('/updateData', [RequestReviewsSettingsController::class, 'updateData']);
            Route::post('/updateDataInStay', [RequestReviewsSettingsController::class, 'updateDataInStay']);
        });
    });
});
