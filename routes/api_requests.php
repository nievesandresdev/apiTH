<?php

use App\Http\Controllers\Api\RequestSettingController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'request-settings'], function () {
    Route::get('/getAll', [RequestSettingController::class, 'getAll']);
    Route::get('/getPostStayRequestData', [RequestSettingController::class, 'getPostStayRequestData']);
    Route::group(['prefix' => 'hoster'], function () {
        Route::get('/updateData', [RequestSettingController::class, 'updateData']);
    });
});