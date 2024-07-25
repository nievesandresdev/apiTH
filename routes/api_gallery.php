<?php

use App\Http\Controllers\Api\Hoster\ImageGalleryController;
use Illuminate\Support\Facades\Route;


Route::group(['prefix' => 'gallery'], function () {
    Route::get('/getAll', [ImageGalleryController::class, 'getAll']);
});