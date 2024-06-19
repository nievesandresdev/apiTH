<?php

use App\Http\Controllers\Api\ServicesController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StayController;

Route::group(['prefix' => 'service'], function () {
    Route::post('/AddColorAndAcronymToGuest', [ServicesController::class, 'AddColorAndAcronymToGuest']);
});


