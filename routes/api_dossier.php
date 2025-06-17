<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\{
    DossierController
};

// Route::middleware('auth.either:user,guest')->group(function () {
    //prefix dossier
    Route::group(['prefix' => 'dossier'], function () {
        Route::get('/getDossier/{domain}/{type}', [DossierController::class, 'getDossier']);
        Route::get('/getDossierData/{tabNumber}/{type?}/{domain?}', [DossierController::class, 'getDossierData']);
        Route::post('/storeUpdateOrCreate', [DossierController::class, 'storeUpdateOrCreate']);
        Route::post('/createNewScenario', [DossierController::class, 'storeDossierData']);
        Route::post('/createInitialDossier', [DossierController::class, 'createInitialDossier']);
        Route::delete('/deleteDossierData/{id}', [DossierController::class, 'deleteDossierData']);
    });
// });
