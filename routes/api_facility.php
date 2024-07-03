<?php

use App\Http\Controllers\Api\FacilityController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'facility'], function () {
    Route::get('/getAll', [FacilityController::class, 'getAll']);
    Route::get('/findById/{id}', [FacilityController::class, 'findById']);
    Route::post('/order', [FacilityController::class, 'updateOrder']);
    Route::post('/visible', [FacilityController::class, 'updateVisible']);
});//