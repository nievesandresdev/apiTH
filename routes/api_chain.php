<?php

use App\Http\Controllers\Api\ChainController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'chain'], function () {
    Route::get('/getHotelsList', [ChainController::class, 'getHotelsList']);
});
