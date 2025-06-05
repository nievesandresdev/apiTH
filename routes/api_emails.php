<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Test\EmailTestController;

Route::group(['prefix' => 'emails'], function () {
    Route::post('/sendEmails', [EmailTestController::class, 'sendEmails']);
});
