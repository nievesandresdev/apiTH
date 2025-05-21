<?php

use App\Http\Controllers\Api\ServicesController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StayController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'service'], function () {
        Route::post('/AddColorAndAcronymToGuest', [ServicesController::class, 'AddColorAndAcronymToGuest']);
    });
});
