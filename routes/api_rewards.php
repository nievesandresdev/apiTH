<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RewardsController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'rewards'], function () {

        Route::get('/getRewards', [RewardsController::class, 'getRewards']);
        Route::post('/storeOrUpdateRewards', [RewardsController::class, 'storeOrUpdateRewards']);
        Route::post('/createCodeReferent', [RewardsController::class, 'createCodeReferent']);

    });
});
