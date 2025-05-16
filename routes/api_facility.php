<?php

use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\Hoster\FacilityHosterController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'facility'], function () {
        Route::get('/getAll', [FacilityController::class, 'getAll']);
        Route::get('/findById/{id}', [FacilityController::class, 'findById']);
        Route::post('/order', [FacilityController::class, 'updateOrder']);
        Route::post('/visible', [FacilityController::class, 'updateVisible']);
        Route::post('/storeOrUpdate', [FacilityController::class, 'storeOrUpdate']);
        Route::delete('/{id}', [FacilityController::class, 'destroy']);
    
        Route::group(['prefix' => 'hoster'], function () {
    
            Route::get('/getAll', [FacilityHosterController::class, 'getAll']);
    
        });
    });
});