<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Hoster\ChainCustomizationServices;
use App\Http\Resources\CustomizationResource;

use App\Services\ChainService;

use App\Utils\Enums\EnumResponse;

class ChainController extends Controller
{
    public function __construct(
        ChainService $_chain_services
        )
    {
        $this->chainServices = $_chain_services;
    }

    public function verifySubdomainExist (Request $request) {
        $hotelModel = $request->attributes->get('hotel');

        $chainModel = $hotelModel->chain;

        $data = $this->chainServices->verifySubdomainExist($request, $hotelModel, $chainModel);

        return bodyResponseRequest(EnumResponse::SUCCESS, $data);
    }

}
