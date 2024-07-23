<?php

use App\Http\Controllers\Api\Hoster\StayQueryHosterController;
use App\Http\Controllers\Api\Hoster\QuerySettingsHosterController;
use App\Http\Controllers\Api\QueryController;
use App\Http\Controllers\Api\QuerySettingsController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'query-settings'], function () {
    Route::get('/getAll', [QuerySettingsController::class, 'getAll']);
    Route::group(['prefix' => 'hoster'], function () {
        Route::post('/updateNotificationsEmail', [QuerySettingsHosterController::class, 'updateNotificationsEmail']);
        Route::get('/getPreStaySettings', [QuerySettingsHosterController::class, 'getPreStaySettings']);
        Route::post('/updatePreStaySettings', [QuerySettingsHosterController::class, 'updatePreStaySettings']);
        Route::get('/getStaySettings', [QuerySettingsHosterController::class, 'getStaySettings']);
        Route::post('/updateStaySettings', [QuerySettingsHosterController::class, 'updateStaySettings']);
        Route::get('/getPostStaySettings', [QuerySettingsHosterController::class, 'getPostStaySettings']);
        Route::post('/updatePostStaySettings', [QuerySettingsHosterController::class, 'updatePostStaySettings']);
    });
});

Route::group(['prefix' => 'query'], function () {
    Route::get('/getCurrentPeriod', [QueryController::class, 'getCurrentPeriod']);
    Route::post('/firstOrCreate', [QueryController::class, 'firstOrCreate']);
    Route::get('/getRecentlySortedResponses', [QueryController::class, 'getRecentlySortedResponses']);
    Route::post('/saveResponse', [QueryController::class, 'saveResponse']);
    Route::get('/existingPendingQuery', [QueryController::class, 'existingPendingQuery']);
    Route::post('/visited', [QueryController::class, 'visited']);
    Route::group(['prefix' => 'hoster'], function () {
        Route::get('/getFeedbackSummaryByGuest', [StayQueryHosterController::class, 'getFeedbackSummaryByGuest']);
        Route::get('/getDetailQueryByGuest', [StayQueryHosterController::class, 'getDetailQueryByGuest']);        
        Route::post('/togglePendingState', [StayQueryHosterController::class, 'togglePendingState']);        
        Route::get('/countPendingByHotel', [StayQueryHosterController::class, 'countPendingByHotel']);        
    });
});