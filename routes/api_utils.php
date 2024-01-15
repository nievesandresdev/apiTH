<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UtilsController;

Route::group(['prefix' => 'utils'], function () {
    Route::post('/authPusher', [UtilsController::class, 'authPusher']);
});