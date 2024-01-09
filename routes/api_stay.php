<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StayController;

Route::group(['prefix' => 'hotel'], function () {
    Route::get('/findStayByParams', [StayController::class, 'findStayByParams']);
});