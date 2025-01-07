<?php

use App\Http\Controllers\Api\Hoster\StayHosterController;
use App\Http\Controllers\Api\Hoster\StaySessionsHosterController;
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
    Route::get('/findbyId/{stayId}', [StayController::class, 'findbyId']);
    
    Route::group(['prefix' => 'hoster'], function () {
        Route::post('/getAllByHotel', [StayHosterController::class, 'getAllByHotel']);
        Route::get('/statisticsByHotel', [StayHosterController::class, 'statisticsByHotel']);
        Route::get('/getdetailData', [StayHosterController::class, 'getdetailData']);
        Route::post('/updateData', [StayHosterController::class, 'updateData']);
        Route::post('/deleteTestStays', [StayHosterController::class, 'deleteTestStays']);
        Route::get('/getDefaultGuestIdAndSessions/{stayId}', [StayHosterController::class, 'getDefaultGuestIdAndSessions']);
        //notes
        Route::get('/getAllNotesByStay', [StayHosterController::class, 'getAllNotesByStay']);
        Route::post('/createOrupdateStayNote', [StayHosterController::class, 'createOrupdateStayNote']);
        Route::post('/deleteStayNote', [StayHosterController::class, 'deleteStayNote']);
        Route::post('/createOrupdateGuestNote', [StayHosterController::class, 'createOrupdateGuestNote']);
        Route::post('/deleteGuestNote', [StayHosterController::class, 'deleteGuestNote']);
        //guest
        Route::get('/getGuestListWithNoti', [StayHosterController::class, 'getGuestListWithNoti']);
        //sessions
        Route::get('/getSessions', [StaySessionsHosterController::class, 'getSessions']);
        Route::post('/createSession', [StaySessionsHosterController::class, 'createSession']);
        Route::post('/deleteSession', [StaySessionsHosterController::class, 'deleteSession']);
        Route::post('/deleteSessionWithApiKey', [StaySessionsHosterController::class, 'deleteSessionWithApiKey']);
        Route::post('/deleteSessionByHotelAndEmail', [StaySessionsHosterController::class, 'deleteSessionByHotelAndEmail']);
    });
});


