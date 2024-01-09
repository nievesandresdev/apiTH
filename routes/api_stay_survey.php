<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StaySurveyController;

Route::group(['prefix' => 'staySurvey'], function () {
    Route::post('/', [StaySurveyController::class, 'store']);
});