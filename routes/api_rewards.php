<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\RewardsController;





Route::group(['prefix' => 'rewards'], function () {

    Route::get('/getRewards', [RewardsController::class, 'getRewards']);
    Route::post('/storeOrUpdateRewards', [RewardsController::class, 'storeOrUpdateRewards']);



});
