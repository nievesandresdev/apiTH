<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Test\TestsController;

Route::group(['prefix' => 'test'], function () {
    Route::post('/verify-faces', [TestsController::class, 'verifyFace']);
    //Route::post('/change/password', [TestsController::class, 'updtPasswordAdmin']);
    Route::post('/enviar-parte', [TestsController::class, 'enviarParteDelViajero']);
    Route::post('/send-whatsapp', [TestsController::class, 'sendMessage']);
});