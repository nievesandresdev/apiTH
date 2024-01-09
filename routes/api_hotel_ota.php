<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HotelOtaController;

Route::group(['prefix' => 'hotelOta'], function () {
    Route::get('/getAll', [HotelOtaController::class, 'getAll']);
});