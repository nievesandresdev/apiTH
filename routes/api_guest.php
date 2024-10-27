<?php

use App\Http\Controllers\Api\GuestAuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\Hoster\GuestHosterController;

Route::group(['prefix' => 'guest'], function () {
    Route::get('/findByIdApi/{id}', [GuestController::class, 'findById']);
    Route::post('/saveOrUpdateApi', [GuestController::class, 'saveOrUpdate']);
    Route::post('/updateLanguageApi', [GuestController::class, 'updateLanguage']);
    Route::get('/findLastStayApi/{id}', [GuestController::class, 'findLastStay']);
    Route::get('/findByEmail', [GuestController::class, 'findByEmail']);
    Route::post('/sendMailTo', [GuestController::class, 'sendMailTo']);
    
    Route::group(['prefix' => 'hoster'], function () {
        Route::post('/inviteToHotel', [GuestHosterController::class, 'inviteToHotel']);
    });
    
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/registerOrLogin', [GuestAuthController::class, 'registerOrLogin']);
        Route::post('/updateById', [GuestAuthController::class, 'updateById']);

        Route::get('/google', [GuestAuthController::class, 'getDataByGoogle']);
        Route::get('/google/callback', [GuestAuthController::class, 'handleGoogleCallback']);

        
        Route::get('/facebook', [GuestController::class, 'authWithFacebook']);
        Route::post('/facebook/deleteData', [GuestController::class, 'deleteFacebookData']);
        Route::get('/facebook/callback', [GuestController::class, 'handleFacebookCallback']);

        // ->middleware('auth:sanctum')
    });
});