<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ChainController;

Route::group(['prefix' => 'chain'], function () {
    Route::get('/verifySubdomainExist', [ChainController::class, 'verifySubdomainExist']);
});
