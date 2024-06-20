<?php

use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\Auth\{
    AuthController,
    ForgotPasswordController
};

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

Route::get('/language/getAll', [LanguageController::class, 'getAll']);


Route::group(['prefix' => 'v1'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);


    //resetPassword
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    //Route::post('api/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::post('password/reset', [ForgotPasswordController::class, 'reset'])->name('password.update');
    Route::get('password/reset/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');

    Route::post('password/verify-token', [ForgotPasswordController::class, 'verifyToken'])->name('password.verify');


    Route::middleware('auth:api')->group(function () {
        Route::get('/user', [AuthController::class, 'getUsers']);
    });
});



