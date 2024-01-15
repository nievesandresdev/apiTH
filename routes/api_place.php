<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PlaceController;

// Route::group(['prefix' => 'e'], function () {
//     Route::get('/findByAutocomplete', [HotelOtaController::class, 'getAll']);
// });
Route::group(['prefix' => 'place'], function () {
    Route::get('/getAll', [PlaceController::class, 'getAll']);
    Route::get('/getCategoriesByType', [PlaceController::class, 'getCategoriesByType']);
    Route::get('/getTypePlaces', [PlaceController::class, 'getTypePlaces']);
});