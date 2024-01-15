<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CityController;

Route::group(['prefix' => 'city'], function () {
    Route::get('/getAll', [CityController::class, 'getAll']);
});