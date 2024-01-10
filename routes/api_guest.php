<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\GuestController;

Route::group(['prefix' => 'guest'], function () {
    Route::get('/findByIdApi/{id}', [GuestController::class, 'findById']);
});