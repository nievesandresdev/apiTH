<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Hoster\ExternalPlatformsController;



Route::group(['prefix' => 'platforms'], function () {
    Route::post('/requestChangeUrl', [ExternalPlatformsController::class, 'requestChangeUrl']);
});
