<?php

use App\Http\Controllers\Api\StayAccessController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'stayAccess'], function () {
        Route::post('/save', [StayAccessController::class, 'save']);
    });
});
