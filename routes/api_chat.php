<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ChatController;

Route::group(['prefix' => 'chat'], function () {
    Route::post('/sendMsgToHoster', [ChatController::class, 'sendMsgToHoster']);
    Route::post('/loadMessages', [ChatController::class, 'loadMessages']);
    Route::post('/markMsgsAsRead', [ChatController::class, 'markMsgsAsRead']);
    Route::get('/unreadMsgs', [ChatController::class, 'unreadMsgs']);
});