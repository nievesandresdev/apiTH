<?php

use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Controllers\Api\LanguageController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'utility'], function () {
    Route::get('/getExpAndPlaceBySaearch', [UtilityController::class, 'getExpAndPlace']);
    Route::get('/getPhoneCodesApi', [UtilityController::class, 'getPhoneCodesApi']);
});

Route::post('/send-message-to-thehoster', [ContactController::class, 'send_message_to_thehoster']);
Route::post('/create-dns-record', [SubdomainController::class, 'createDNSRecord']);

Route::get('/language/getAll', [LanguageController::class, 'getAll']);
