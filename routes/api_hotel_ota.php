<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HotelOtaController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'hotelOta'], function () {
        Route::get('/getAll', [HotelOtaController::class, 'getAll']);
    });
});