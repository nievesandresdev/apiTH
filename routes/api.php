<?php

use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['prefix' => 'utility'], function () {
    Route::get('/getExpAndPlaceBySaearch', [UtilityController::class, 'getExpAndPlace']);
    Route::get('/getPhoneCodesApi', [UtilityController::class, 'getPhoneCodesApi']);
});

Route::post('/send-message-to-thehoster', [ContactController::class, 'send_message_to_thehoster']);
Route::post('/create-dns-record', [SubdomainController::class, 'createDNSRecord']);

Route::group(['prefix' => 'v1'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'getUsers']);
    });
});

