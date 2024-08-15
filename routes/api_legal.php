<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Legal\LegalGeneralController;
use App\Http\Controllers\Api\Legal\LegalPolicyController;

Route::group(['prefix' => 'legal'], function () {
    Route::get('/general', [LegalGeneralController::class, 'getGeneralLegal']);
    Route::post('/general', [LegalGeneralController::class, 'storeGeneralLegal']);


    //policies
    Route::get('/getPolicies', [LegalPolicyController::class, 'getPolicyLegal']);
    Route::post('/policies', [LegalPolicyController::class, 'storePolicylLegal']);
    Route::post('/updatePolicies', [LegalPolicyController::class, 'updatePolicylLegal']);
    Route::post('/deletePolicy', [LegalPolicyController::class, 'deletePolicylLegal']);
});
