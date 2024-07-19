<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\Hoster\ChatSettingsController;

Route::group(['prefix' => 'chat'], function () {
    Route::post('/sendMsgToHoster', [ChatController::class, 'sendMsgToHoster']);
    Route::post('/loadMessages', [ChatController::class, 'loadMessages']);
    Route::post('/markMsgsAsRead', [ChatController::class, 'markMsgsAsRead']);
    Route::get('/unreadMsgs', [ChatController::class, 'unreadMsgs']);

    //hoster endpoints
    Route::group(['prefix' => 'hoster'], function () {

        //settings endopoints
        Route::group(['prefix' => 'settings'], function () {
            Route::get('/getAll', [ChatSettingsController::class, 'getAll']);
            Route::post('/updateNotificationsEmail', [ChatSettingsController::class, 'updateNotificationsEmail']);


            //getSettings
            Route::get('/getSettings', [ChatSettingsController::class, 'getSettings']);

            //search_lang
            Route::post('/searchLang', [ChatSettingsController::class, 'searchLang']);
            //storeGeneralSetting
            Route::post('/storeGeneralSetting', [ChatSettingsController::class, 'storeGeneralSetting']);
            //updateAvailability
            Route::post('/updateAvailability', [ChatSettingsController::class, 'updateAvailability']);
            //updateResponses
            Route::post('/updateResponses', [ChatSettingsController::class, 'updateResponses']);
        });

    });
});
