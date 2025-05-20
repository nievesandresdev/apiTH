<?php

use App\Http\Controllers\Api\GuestAuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\Hoster\GuestHosterController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'guest'], function () {
        Route::get('/findByIdApi/{id}', [GuestController::class, 'findById']);
        Route::post('/saveOrUpdateApi', [GuestController::class, 'saveOrUpdate']);
        Route::post('/updateLanguageApi', [GuestController::class, 'updateLanguage']);
        Route::get('/findAndValidLastStay', [GuestController::class, 'findAndValidLastStay']);
        // Route::get('/saveAndFindValidLastStay', [GuestController::class, 'saveAndFindValidLastStay']);
        Route::get('/findByEmail', [GuestController::class, 'findByEmail']);
        Route::post('/sendMailTo', [GuestController::class, 'sendMailTo']);
        Route::post('/createAccessInStay', [GuestController::class, 'createAccessInStay']);
        Route::post('/deleteGuestOfStay', [GuestController::class, 'deleteGuestOfStay']);
        Route::post('/saveCheckinData', [GuestController::class, 'saveCheckinData']);
        Route::post('/deleteCheckinData', [GuestController::class, 'deleteCheckinData']);
        //update data huesped
        Route::post('/updatePasswordGuest', [GuestController::class, 'updatePasswordGuest']);
        //updateDataGuest
        Route::post('/updateDataGuest', [GuestController::class, 'updateDataGuest']);
        Route::post('/sendContactEmail', [GuestController::class, 'sendContactEmail']);
        Route::get('/getContactEmailsByStayId', [GuestController::class, 'getContactEmailsByStayId']);

        Route::group(['prefix' => 'hoster'], function () {
            Route::post('/inviteToHotel', [GuestHosterController::class, 'inviteToHotel']);
            Route::get('/findById', [GuestHosterController::class, 'findById']);
        });

    });
});
Route::group(['prefix' => 'guest'], function () {
    Route::get('/saveAndFindValidLastStay', [GuestController::class, 'saveAndFindValidLastStay']);
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/registerOrLogin', [GuestAuthController::class, 'registerOrLogin']);
        Route::post('/updateById', [GuestAuthController::class, 'updateById']);
        Route::post('/sendResetLinkEmail', [GuestAuthController::class, 'sendResetLinkEmail']);
        Route::post('/confirmPassword', [GuestAuthController::class, 'confirmPassword']);
        Route::post('/resetPassword', [GuestAuthController::class, 'resetPassword']);
        
        Route::post('/google/login', [GuestAuthController::class, 'autenticateByGoogle']);
        Route::get('/google', [GuestAuthController::class, 'getDataByGoogle']);
        Route::get('/google/callback', [GuestAuthController::class, 'handleGoogleCallback']);
    
        Route::get('/facebook', [GuestAuthController::class, 'authWithFacebook']);
        Route::post('/facebook/deleteData', [GuestAuthController::class, 'deleteFacebookData']);
        Route::get('/facebook/callback', [GuestAuthController::class, 'handleFacebookCallback']);

        Route::post('/guestDefault', [GuestAuthController::class, 'autenticateGuestDefault']);
    });
});
