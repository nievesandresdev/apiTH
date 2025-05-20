<?php

use App\Http\Controllers\Api\Hoster\ImageGalleryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'gallery'], function () {
        Route::get('/getAll', [ImageGalleryController::class, 'getAll']);
        Route::post('/deleteBulk', [ImageGalleryController::class, 'deleteBulk']);
        Route::post('/upload', [ImageGalleryController::class, 'upload']);
    });
});