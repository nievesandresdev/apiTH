<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CityController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'city'], function () {
        Route::get('/getAll', [CityController::class, 'getAll']);
        Route::get('/getNearCitiesData', [CityController::class, 'getNearCitiesData']);
    });
});