<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StaySurveyController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'staySurvey'], function () {
        Route::post('/', [StaySurveyController::class, 'store']);
        Route::get('/findByParams', [StaySurveyController::class, 'findByParams']);
    });
});
