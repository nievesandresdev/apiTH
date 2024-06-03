<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UtilsController;
use App\Http\Controllers\Api\StayController;

Route::group(['prefix' => 'utils'], function () {
    Route::post('/authPusher', [UtilsController::class, 'authPusher']);
});

//test
Route::group(['prefix' => 'utils'], function () {
    Route::get('/test', [UtilsController::class, 'test']);
    Route::get('/stayTest', [StayController::class, 'testMail']);
});

//test

