<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Chatgpt\TranslateController;

Route::group(['prefix' => 'chatgpt'], function () {
    Route::group(['prefix' => 'translate'], function () {
        Route::post('/', [TranslateController::class, 'load']);
        Route::post('/validate', [TranslateController::class, 'validateTranslation']);
    });
});