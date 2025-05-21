<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Test\TestsController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'test'], function () {
        Route::post('/verify-faces', [TestsController::class, 'verifyFace']);
        Route::post('/change/password', [TestsController::class, 'updtPasswordAdmin']);
        Route::post('/send/message', [TestsController::class, 'sendWhatsAppMessage']);
        Route::post('/update/data', [TestsController::class, 'updateWhatsAppProfile']);
        Route::post('/send/mail', [TestsController::class, 'sendEmail']);
        Route::get('/test-job', [TestsController::class, 'testJob']);
    });
});
