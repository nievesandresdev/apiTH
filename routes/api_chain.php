<?php

use App\Http\Controllers\Api\ChainController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'chain'], function () {
        Route::get('/verifySubdomainExist', [ChainController::class, 'verifySubdomainExist']);
        Route::get('/getHotelsList', [ChainController::class, 'getHotelsList']);
        Route::post('/configGeneral/update', [ChainController::class, 'updateConfigGeneral']);
        Route::get('/getStaysGuest', [ChainController::class, 'getStaysGuest']);
        Route::get('/getChainBySubdomain', [ChainController::class, 'getChainBySubdomain']);
    });
});

Route::group(['prefix' => 'chain'], function () {
    Route::get('/getCustomatizacion', [ChainController::class, 'getCustomatizacion']);
    Route::get('/findBySubdomain', [ChainController::class, 'findBySubdomain']);
});
