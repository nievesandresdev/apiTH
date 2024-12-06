<?php

use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Api\revieNotificationController;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Controllers\Api\{
    LanguageController,
    DasboardController
};
use App\Http\Controllers\Api\Auth\{
    AuthController,
    ForgotPasswordController
};
use App\Http\Controllers\Api\Users\{
    UsersController,
    WorkPositionController
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
Route::post('/language/getforItem', [LanguageController::class, 'getLanguageForItem']);


Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    //loginAdmin
    Route::post('/loginAdmin', [AuthController::class, 'loginAdmin']);
    Route::post('/logout', [AuthController::class, 'logout']);
    //resetPassword
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    Route::post('password/reset', [ForgotPasswordController::class, 'reset'])->name('password.update');
    Route::get('password/reset/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');

    Route::post('password/verify-token', [ForgotPasswordController::class, 'verifyToken'])->name('password.verify');
});

//prefix users
Route::middleware('auth:api')->group(function () {

    Route::group(['prefix' => 'users'], function () {
        Route::get('/work-position', [WorkPositionController::class, 'getAllWorkPosition']);
        Route::post('/work-position', [WorkPositionController::class, 'store']);
        Route::post('/work-position/update', [WorkPositionController::class, 'update']);
        Route::post('/work-position/delete', [WorkPositionController::class, 'delete']);

        Route::post('/store', [UsersController::class, 'store']);
        Route::post('/update', [UsersController::class, 'update']);
        Route::post('/update-profile', [UsersController::class, 'updateProfile']);
        Route::get('/getUsers', [UsersController::class, 'getUsers']);
        Route::get('/getUser', [UsersController::class, 'getUser']);

        Route::get('/getTrial', [UsersController::class, 'getTrial']);
        //getUserData
        Route::get('/getUserData', [AuthController::class, 'getUserData']);
        Route::post('/delete', [UsersController::class, 'delete']);
        Route::post('disabled', [UsersController::class, 'disabled']);
        Route::post('enabled', [UsersController::class, 'enabled']);

        //verifyExistMail
        Route::post('/verifyExistMail', [UsersController::class, 'verifyExistMail']);

        Route::get('/get-subscription-status', [UsersController::class, 'getStatusSubscription']);


        //test mail
        Route::get('/testMail', [UsersController::class, 'testMail']);
    });

    //dashboard
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/dataCustomerExperience', [DasboardController::class, 'dataCustomerExperience']);
        Route::get('/dataFeedback', [DasboardController::class, 'dataFeedback']);
        Route::get('/getDataReviewOTA', [DasboardController::class, 'getDataReviewOTA']);
    }); 
    
});


Route::group(['prefix' => 'review/notification'], function () {
    Route::post('/', [revieNotificationController::class, 'send']);
});







