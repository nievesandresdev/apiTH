<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\PlaceController;

// Route::group(['prefix' => 'e'], function () {
//     Route::get('/findByAutocomplete', [HotelOtaController::class, 'getAll']);
// });
Route::middleware('auth.either:user,guest')->group(function () {
    Route::group(['prefix' => 'place'], function () {
        Route::get('/getAll', [PlaceController::class, 'getAll']);
        Route::get('/getCategoriesByType', [PlaceController::class, 'getCategoriesByType']);
        Route::get('/getTypePlaces', [PlaceController::class, 'getTypePlaces']);
        Route::get('/getRatingCountsPlaces', [PlaceController::class, 'getRatingCountsPlaces']);
        Route::get('/findById', [PlaceController::class, 'findById']);
        Route::get('/getDataReviews', [PlaceController::class, 'getDataReviews']);
        Route::get('/getReviewsByRating', [PlaceController::class, 'getReviewsByRating']);
    });
});
