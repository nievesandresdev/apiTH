<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HotelController;

Route::group(['prefix' => 'hotel'], function () {
    Route::get('/findByParams', [HotelController::class, 'findByParams']);
    Route::get('/getAllCrossellings', [HotelController::class, 'getAllCrossellings']);
});