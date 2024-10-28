<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChainResource;
use Illuminate\Http\Request;
use App\Services\Hoster\ChainCustomizationServices;
use App\Http\Resources\CustomizationResource;
use App\Http\Resources\HotelCardResource;
use App\Services\ChainService;

use App\Utils\Enums\EnumResponse;

class ChainController extends Controller
{
    public $chainServices;

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

    public function getHotelsList (Request $request) {
        try {
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $hotels = $this->chainServices->getHotelsList($chainSubdomain);
            $hotels = HotelCardResource::collection($hotels);
            if(!$hotels){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotels);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function findBySubdomain (Request $request) {
        try {
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $chain = $this->chainServices->findBySubdomain($chainSubdomain);
            $chain = new ChainResource($chain);
            if(!$chain){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $chain);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findBySubdomain');
        }
    }

}
