<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Legal\LegalGeneralController;
use App\Http\Controllers\Api\Legal\LegalPolicyController;

Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'legal'], function () {
        Route::get('/general', [LegalGeneralController::class, 'getGeneralLegal']);
        Route::post('/general', [LegalGeneralController::class, 'storeGeneralLegal']);


        //policies
        Route::get('/getPolicies', [LegalPolicyController::class, 'getPolicyLegal']);
        Route::post('/policies', [LegalPolicyController::class, 'storePolicylLegal']);
        Route::post('/updatePolicies', [LegalPolicyController::class, 'updatePolicylLegal']);
        Route::post('/deletePolicy', [LegalPolicyController::class, 'deletePolicylLegal']);

        Route::post('/generate-pdf', [LegalPolicyController::class, 'generatePDF']);
        Route::get('/getCountPoliciesByHotel', [LegalPolicyController::class, 'getCountPoliciesByHotel']);

        //webapp routes
        Route::get('/getNormsByHotel', [LegalGeneralController::class, 'getNormsByHotel']);
    });
});
