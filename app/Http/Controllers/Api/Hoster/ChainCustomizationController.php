<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Hoster\ChainCustomizationServices;
use App\Http\Resources\CustomizationResource;

use App\Utils\Enums\EnumResponse;

class ChainCustomizationController extends Controller
{
    public function __construct(
        ChainCustomizationServices $_chain_customization_services
        )
    {
        $this->chainCustomizationServices = $_chain_customization_services;
    }

    public function update (Request $request) {
        $hotelModel = $request->attributes->get('hotel');

        $chainModel = $hotelModel->chain;

        $data = $this->chainCustomizationServices->createOrUpdate($request, $hotelModel, $chainModel);

        return bodyResponseRequest(EnumResponse::SUCCESS, $data);
    }

    public function findOne (Request $request) {
        $hotelModel = $request->attributes->get('hotel');
        
        $chainModel = $hotelModel->chain;

        $customizationModel = $chainModel->customization;

        $data = new CustomizationResource($customizationModel);

        return bodyResponseRequest(EnumResponse::SUCCESS, $data);
    }

}
