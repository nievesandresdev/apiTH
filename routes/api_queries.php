<?php

use App\Http\Controllers\Api\Hoster\QueryHosterController;
use App\Http\Controllers\Api\Hoster\StayQueryHosterController;
use App\Http\Controllers\Api\Hoster\QuerySettingsHosterController;
use App\Http\Controllers\Api\QueryController;
use App\Http\Controllers\Api\QuerySettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'query-settings'], function () {
        Route::get('/getAll', [QuerySettingsController::class, 'getAll']);
        Route::group(['prefix' => 'hoster'], function () {
            Route::post('/updateNotificationsEmail', [QuerySettingsHosterController::class, 'updateNotificationsEmail']);
            Route::get('/getPreStaySettings', [QuerySettingsHosterController::class, 'getPreStaySettings']);
            Route::post('/updatePreStaySettings', [QuerySettingsHosterController::class, 'updatePreStaySettings']);
            //////////////////////
            Route::get('/getStayVeryGoodSettings', [QuerySettingsHosterController::class, 'getStayVeryGoodSettings']);
            Route::post('/updateStayVeryGoodSettings', [QuerySettingsHosterController::class, 'updateStayVeryGoodSettings']);
            Route::get('/getStayGoodSettings', [QuerySettingsHosterController::class, 'getStayGoodSettings']);
            Route::post('/updateStayGoodSettings', [QuerySettingsHosterController::class, 'updateStayGoodSettings']);
            Route::get('/getStayBadSettings', [QuerySettingsHosterController::class, 'getStayBadSettings']);
            Route::post('/updateStayBadSettings', [QuerySettingsHosterController::class, 'updateStayBadSettings']);
            //////////////////////
            Route::get('/getPostStayVeryGoodSettings', [QuerySettingsHosterController::class, 'getPostStayVeryGoodSettings']);
            Route::post('/updatePostStayVeryGoodSettings', [QuerySettingsHosterController::class, 'updatePostStayVeryGoodSettings']);
            Route::get('/getPostStayGoodSettings', [QuerySettingsHosterController::class, 'getPostStayGoodSettings']);
            Route::post('/updatePostStayGoodSettings', [QuerySettingsHosterController::class, 'updatePostStayGoodSettings']);
            Route::get('/getPostStayBadSettings', [QuerySettingsHosterController::class, 'getPostStayBadSettings']);
            Route::post('/updatePostStayBadSettings', [QuerySettingsHosterController::class, 'updatePostStayBadSettings']);
            //////////////////////
        });
    });

    Route::group(['prefix' => 'query'], function () {
        Route::get('/getCurrentPeriod', [QueryController::class, 'getCurrentPeriod']);
        Route::post('/firstOrCreate', [QueryController::class, 'firstOrCreate']);
        Route::get('/getRecentlySortedResponses', [QueryController::class, 'getRecentlySortedResponses']);
        Route::post('/saveResponse', [QueryController::class, 'saveResponse']);
        Route::get('/existingPendingQuery', [QueryController::class, 'existingPendingQuery']);
        Route::post('/visited', [QueryController::class, 'visited']);
        Route::get('/getCurrentQuery', [QueryController::class, 'getCurrentQuery']);
        Route::get('/getCurrentAndSettingsQuery', [QueryController::class, 'getCurrentAndSettingsQuery']);
        Route::group(['prefix' => 'hoster'], function () {
            Route::get('/getFeedbackSummaryByGuest', [StayQueryHosterController::class, 'getFeedbackSummaryByGuest']);
            Route::get('/getDetailQueryByGuest', [StayQueryHosterController::class, 'getDetailQueryByGuest']);        
            Route::post('/togglePendingState', [StayQueryHosterController::class, 'togglePendingState']);        
            Route::get('/countPendingByHotel', [StayQueryHosterController::class, 'countPendingByHotel']);        
            Route::get('/pendingCountByStay/{stayId}', [StayQueryHosterController::class, 'pendingCountByStay']);        
            Route::get('/getGeneralReport', [QueryHosterController::class, 'getGeneralReport']);
        });
    });
});
