<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HotelButtonsController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'buttons'], function () {
        Route::get('/getButtons', [HotelButtonsController::class, 'getButtons']);
        Route::post('/updateOrder', [HotelButtonsController::class, 'updateOrder']);
        Route::post('/updateButtonVisibility', [HotelButtonsController::class, 'updateButtonVisibility']);
    });
});
