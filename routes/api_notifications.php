<?php

use App\Http\Controllers\Api\Hoster\NotificationsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'hoster/notifications'], function () {
        Route::get('/getNotificationsByUser/{UserId}', [NotificationsController::class, 'getNotificationsByUser']);
        Route::post('/vote', [NotificationsController::class, 'vote']);
    });
});
