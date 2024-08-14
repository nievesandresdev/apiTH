<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Legal\LegalGeneralController;



Route::group(['prefix' => 'legal'], function () {
    Route::get('/general', [LegalGeneralController::class, 'getGeneralLegal']);
    Route::post('/general', [LegalGeneralController::class, 'storeGeneralLegal']);
});
