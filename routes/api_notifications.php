<?php

use App\Http\Controllers\Api\Hoster\NotificationsController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'notifications'], function () {
    Route::get('/getNotificationsByUser', [NotificationsController::class, 'getNotificationsByUser']);
});