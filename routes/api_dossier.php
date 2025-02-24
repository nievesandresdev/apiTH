<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\{
    DossierController
};

//prefix dossier
 Route::group(['prefix' => 'dossier'], function () {
    Route::get('/getDossier/{domain}/{type}', [DossierController::class, 'getDossier']);
    Route::get('/getDossierData/{tabNumber}', [DossierController::class, 'getDossierData']);
    Route::post('/storeUpdateOrCreate', [DossierController::class, 'storeUpdateOrCreate']);
    Route::post('/createNewScenario', [DossierController::class, 'storeDossierData']);
    Route::delete('/deleteDossierData/{id}', [DossierController::class, 'deleteDossierData']);
});
