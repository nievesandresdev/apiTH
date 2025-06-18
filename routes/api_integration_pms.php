<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IntegrationPmsController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'pms'], function () {
        Route::get('/getIntegrationPms', [IntegrationPmsController::class, 'getIntegrationPms']);
        Route::post('/updateOrCreateCredentials', [IntegrationPmsController::class, 'updateOrCreateCredentials']);
        Route::post('/deleteCredentialsPms', [IntegrationPmsController::class, 'deleteCredentials']);
        Route::get('/getPmswithFilters/{name?}', [IntegrationPmsController::class, 'getPmswithFilters']);
    });
});
