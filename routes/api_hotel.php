<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Controllers\Api\Hoster\ChainCustomizationController;

Route::group(['prefix' => 'hotel'], function () {

    Route::post('/appearence', [ChainCustomizationController::class, 'update']);
    Route::get('/appearence-findOne', [ChainCustomizationController::class, 'findOne']);

    Route::get('/getAll', [HotelController::class, 'getAll']);
    Route::get('/getHotelsByUser', [HotelController::class, 'getHotelsByUser']);
    Route::post('/updateDefaultHotel', [HotelController::class, 'updateDefaultHotel']);
    Route::get('/findByParams', [HotelController::class, 'findByParams']);
    Route::post('/profile', [HotelController::class, 'updateProfile']);
    Route::get('/getAllCrossellings', [HotelController::class, 'getAllCrossellings']);
    Route::get('/create/subdomain', [SubdomainController::class, 'createDNSRecord']);
    Route::get('/getChatHours', [HotelController::class, 'getChatHours']);
    Route::post('/placeVisivility', [HotelController::class, 'updateVisivilityPlaces']);
    Route::post('/updateSenderMailMask', [HotelController::class, 'updateSenderMailMask']);
    Route::post('/facilityVisivility', [HotelController::class, 'updateVisivilityFacilities']);
    Route::post('/experienceVisivility', [HotelController::class, 'updateVisivilityExperiences']);
    Route::post('/categoriVisivility', [HotelController::class, 'updateVisivilityCategory']);
    Route::post('/typePlaceVisivility', [HotelController::class, 'updateVisivilityTypePlace']);
    Route::get('/verifySubdomainExistPerHotel', [HotelController::class, 'verifySubdomainExistPerHotel']);
    Route::post('/customization', [HotelController::class, 'updateCustomization']);
    Route::post('/updateShowButtons', [HotelController::class, 'updateShowButtons']);

});
