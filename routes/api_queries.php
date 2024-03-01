<?php

use App\Http\Controllers\Api\QueryController;
use App\Http\Controllers\Api\QuerySettingsController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'query-settings'], function () {
    Route::get('/getAll', [QuerySettingsController::class, 'getAll']);
});

Route::group(['prefix' => 'query'], function () {
    Route::get('/getCurrentPeriod', [QueryController::class, 'getCurrentPeriod']);
    Route::post('/firstOrCreate', [QueryController::class, 'firstOrCreate']);
    Route::get('/getRecentlySortedResponses', [QueryController::class, 'getRecentlySortedResponses']);
    Route::post('/saveResponse', [QueryController::class, 'saveResponse']);
    Route::get('/existingPendingQuery', [QueryController::class, 'existingPendingQuery']);
});