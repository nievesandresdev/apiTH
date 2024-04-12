<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\StayController;

Route::group(['prefix' => 'stay'], function () {
    Route::get('/findAndValidAccess', [StayController::class, 'findAndValidAccess']);
    Route::get('/existsAndValidate', [StayController::class, 'existsAndValidate']);
    Route::post('/createAndInviteGuest', [StayController::class, 'createAndInviteGuest']);
    Route::post('/existingStayThenMatchAndInvite', [StayController::class, 'existingStayThenMatchAndInvite']);
    Route::post('/existingThenMatchOrSave', [StayController::class, 'existingThenMatchOrSave']);
    Route::get('/getGuestsAndSortByCurrentguestIdApi/{stayId}/{guestId}', [StayController::class, 'getGuestsAndSortByCurrentguestId']);
    Route::post('/updateStayAndGuests', [StayController::class, 'updateStayAndGuests']);
    Route::post('/deleteGuestOfStay/{stayId}/{guestId}', [StayController::class, 'deleteGuestOfStay']);
});