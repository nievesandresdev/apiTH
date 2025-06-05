<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\EmailController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'email'], function () {
        Route::post('/disabledEmail', [EmailController::class, 'disabledEmail']);
        Route::post('/reactivateEmail', [EmailController::class, 'enabledEmail']);
    });
});
