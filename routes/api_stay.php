<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StayController;

Route::group(['prefix' => 'stay'], function () {
    Route::get('/findAndValidAccess', [StayController::class, 'findAndValidAccess']);
    Route::post('/createAndInviteGuest', [StayController::class, 'createAndInviteGuest']);
    Route::post('/existingStayThenMatchAndInvite', [StayController::class, 'existingStayThenMatchAndInvite']);
});