<?php

use App\Http\Controllers\Api\Hoster\NotificationsController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'hoster/notifications'], function () {
    Route::get('/getNotificationsByUser', [NotificationsController::class, 'getNotificationsByUser']);
});