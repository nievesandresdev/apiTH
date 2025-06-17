<?php

use App\Http\Controllers\Api\UtilsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/test', [UtilsController::class, 'test']);
Route::get('/testDissatisfiedGuest', [UtilsController::class, 'testDissatisfiedGuest']);
Route::get('/testEmailPostCheckout', [UtilsController::class, 'testEmailPostCheckout']);
Route::get('/testPrepareYourArrival', [UtilsController::class, 'testPrepareYourArrival']);
Route::get('/testEmailGeneral', [UtilsController::class, 'testEmailGeneral']);
Route::get('/testEmailReferent', [UtilsController::class, 'testEmailReferent']);
Route::get('/testPostCheckin', [UtilsController::class, 'testPostCheckin']);