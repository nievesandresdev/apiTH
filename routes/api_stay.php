<?php

use App\Http\Controllers\Api\Hoster\StayHosterController;
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

    Route::group(['prefix' => 'hoster'], function () {
        Route::post('/getAllByHotel', [StayHosterController::class, 'getAllByHotel']);
        Route::get('/statisticsByHotel', [StayHosterController::class, 'statisticsByHotel']);
        Route::get('/getdetailData', [StayHosterController::class, 'getdetailData']);
        Route::post('/updateData', [StayHosterController::class, 'updateData']);
        //notes
        Route::get('/getAllNotesByStay', [StayHosterController::class, 'getAllNotesByStay']);
        Route::post('/createOrupdateStayNote', [StayHosterController::class, 'createOrupdateStayNote']);
        Route::post('/deleteStayNote', [StayHosterController::class, 'deleteStayNote']);
        Route::post('/createOrupdateGuestNote', [StayHosterController::class, 'createOrupdateGuestNote']);
        Route::post('/deleteGuestNote', [StayHosterController::class, 'deleteGuestNote']);
        //sessions
        Route::post('/createSession', [StayHosterController::class, 'createSession']);
        Route::post('/deleteSession', [StayHosterController::class, 'deleteSession']);
    });
});


