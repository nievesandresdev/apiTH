<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\Hoster\ChatSettingsController;
use App\Http\Controllers\Api\Hoster\StayChatHosterController;

Route::group(['prefix' => 'chat'], function () {
    Route::post('/sendMsgToHoster', [ChatController::class, 'sendMsgToHoster']);
    Route::post('/loadMessages', [ChatController::class, 'loadMessages']);
    Route::post('/markMsgsAsRead', [ChatController::class, 'markMsgsAsRead']);
    Route::get('/unreadMsgs', [ChatController::class, 'unreadMsgs']);
    Route::get('/getAvailavilityByHotel/', [ChatController::class, 'getAvailavilityByHotel']);
    //hoster endpoints
    Route::group(['prefix' => 'hoster'], function () {

        Route::get('/getDataRoom', [StayChatHosterController::class, 'getDataRoom']);
        Route::post('/sendMsg', [StayChatHosterController::class, 'sendMsg']);
        Route::post('/togglePending', [StayChatHosterController::class, 'togglePending']);
        Route::get('/getGuestListWNoti', [StayChatHosterController::class, 'getGuestListWNoti']);
        Route::get('/pendingCountByHotel', [StayChatHosterController::class, 'pendingCountByHotel']);
        Route::get('/pendingCountByStay/{stayId}', [StayChatHosterController::class, 'pendingCountByStay']);
        Route::post('/markGuesMsgstAsRead/{stayId}/{guestId}', [StayChatHosterController::class, 'markGuesMsgstAsRead']);
        //settings endopoints
        Route::group(['prefix' => 'settings'], function () {
            Route::get('/getAll', [ChatSettingsController::class, 'getAll']);
            Route::post('/updateNotificationsEmail', [ChatSettingsController::class, 'updateNotificationsEmail']);
            Route::get('/getSettings', [ChatSettingsController::class, 'getSettings']);
            Route::post('/searchLang', [ChatSettingsController::class, 'searchLang']);
            Route::post('/storeGeneralSetting', [ChatSettingsController::class, 'storeGeneralSetting']);
            Route::post('/updateAvailability', [ChatSettingsController::class, 'updateAvailability']);
            Route::post('/updateResponses', [ChatSettingsController::class, 'updateResponses']);
        });

    });
});
