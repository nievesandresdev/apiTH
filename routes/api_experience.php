<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ExperienceController;
use App\Http\Controllers\Api\Hoster\ExperienceController as ExperienceSaasController;

// Route::group(['prefix' => 'e'], function () {
//     Route::get('/findByAutocomplete', [HotelOtaController::class, 'getAll']);
// });
Route::group(['prefix' => 'experience'], function () {
    Route::get('/getAll', [ExperienceController::class, 'getAll']);
    Route::get('/findBySlug', [ExperienceController::class, 'findBySlug']);
    Route::get('/getNumbersByFilters', [ExperienceController::class, 'getNumbersByFilters']);
    Route::get('/findInVIatorByShortId', [ExperienceController::class, 'findInVIatorByShortId']);
    Route::get('/findSchedulesInVIator', [ExperienceController::class, 'findSchedulesInVIator']);
    Route::group(['prefix' => 'saas'], function () {
        Route::post('/getAll', [ExperienceSaasController::class, 'getAll']);
        Route::post('/getNumbersByFilters', [ExperienceSaasController::class, 'getNumbersByFilters']);
        Route::post('/position', [ExperienceSaasController::class, 'updatePosition']);
        Route::post('/resetPosition', [ExperienceSaasController::class, 'resetPosition']);
        Route::post('/visibility', [ExperienceSaasController::class, 'updateVisibility']);
    Route::post('/recommendation', [ExperienceSaasController::class, 'updateRecommendation']);
        Route::post('/', [ExperienceSaasController::class, 'update']);
    });
});
