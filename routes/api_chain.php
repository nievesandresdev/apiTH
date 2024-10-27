<?php

<<<<<<< HEAD
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ChainController;

Route::group(['prefix' => 'chain'], function () {
    Route::get('/verifySubdomainExist', [ChainController::class, 'verifySubdomainExist']);
=======
use App\Http\Controllers\Api\ChainController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'chain'], function () {
    Route::get('/getHotelsList', [ChainController::class, 'getHotelsList']);
>>>>>>> 614ff84c2e37eeaea547a7a73b21ae5d18d83a96
});
