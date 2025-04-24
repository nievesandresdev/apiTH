<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\HotelController;
use App\Http\Controllers\Subdomain\SubdomainController;
use App\Http\Controllers\Api\Hoster\ChainCustomizationController;
use App\Http\Controllers\Api\Hoster\HotelHosterController;
use App\Http\Controllers\Api\Hoster\HotelWifiNetworksController;
use App\Http\Controllers\Api\HotelCommunicationController;

Route::post('/appearence', [ChainCustomizationController::class, 'update']);

Route::group(['prefix' => 'hotel'], function () {

    Route::group(['prefix' => 'hoster'], function () {
        Route::post('/deleteImageByHotel', [HotelHosterController::class, 'deleteImageByHotel']);
        Route::post('/toggleChatService', [HotelHosterController::class, 'toggleChatService']);
        Route::post('/toggleCheckinService', [HotelHosterController::class, 'toggleCheckinService']);
        Route::post('/toggleReviewsService', [HotelHosterController::class, 'toggleReviewsService']);
    });


    Route::post('/appearence', [ChainCustomizationController::class, 'update']);
    Route::get('/appearence/findOne', [ChainCustomizationController::class, 'findOne']);

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
    Route::post('/serviceVisivility', [HotelController::class, 'updateVisivilityServices']);
    Route::post('/categoriVisivility', [HotelController::class, 'updateVisivilityCategory']);
    Route::post('/typePlaceVisivility', [HotelController::class, 'updateVisivilityTypePlace']);
    Route::get('/verifySubdomainExistPerHotel', [HotelController::class, 'verifySubdomainExistPerHotel']);
    Route::post('/customization', [HotelController::class, 'updateCustomization']);
    Route::post('/updateShowButtons', [HotelController::class, 'updateShowButtons']);
    Route::get('buildUrlWebApp', [HotelController::class, 'buildUrlWebApp']);
    Route::get('getMainData', [HotelController::class, 'getMainData']);
    Route::get('getDataLegal', [HotelController::class, 'getDataLegal']);
    //findById
    Route::get('findById/{id}', [HotelController::class, 'findById']);
    Route::get('getRewardsByHotel', [HotelController::class, 'getRewardsByHotel']);

    //handleShowReferrals
    Route::post('handleShowReferrals', [HotelController::class, 'handleShowReferrals']);

    //manage communication hotel
    Route::group(['prefix' => 'communication'], function () {
        Route::post('getHotelCommunication', [HotelCommunicationController::class, 'getHotelCommunication']);
        Route::post('updateOrStoreHotelCommunication', [HotelCommunicationController::class, 'updateOrStoreHotelCommunication']);
    });

    //manage wifi networks hotel
    Route::group(['prefix' => 'wifiNetworks'], function () {
        Route::post('store', [HotelWifiNetworksController::class, 'store']);
        Route::post('updateById', [HotelWifiNetworksController::class, 'updateById']);
        Route::post('updateVisibilityNetwork', [HotelWifiNetworksController::class, 'updateVisibilityNetwork']);
        Route::get('getAllByHotel', [HotelWifiNetworksController::class, 'getAllByHotel']);
        Route::get('getAllByHotelAndVisible', [HotelWifiNetworksController::class, 'getAllByHotelAndVisible']);
    });

});


