<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\ExperienceController;

// Route::group(['prefix' => 'e'], function () {
//     Route::get('/findByAutocomplete', [HotelOtaController::class, 'getAll']);
// });
Route::group(['prefix' => 'experience'], function () {
    Route::get('/getAll', [ExperienceController::class, 'getAll']);
    Route::get('/findBySlug', [ExperienceController::class, 'findBySlug']);
    Route::get('/getNumbersByFilters', [ExperienceController::class, 'getNumbersByFilters']);
    Route::get('/findInVIatorByShortId', [ExperienceController::class, 'findInVIatorByShortId']);
    Route::get('/findSchedulesInVIator', [ExperienceController::class, 'findSchedulesInVIator']);
});