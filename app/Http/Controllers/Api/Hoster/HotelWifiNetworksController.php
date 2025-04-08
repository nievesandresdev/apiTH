<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Hotel\HotelWifiNetworksServices;
use App\Utils\Enums\EnumResponse;
use Illuminate\Http\Request;

use function Database\Seeders\run;

class HotelWifiNetworksController extends Controller
{
    public $service;

    function __construct(
         HotelWifiNetworksServices $_HotelWifiNetworksServices
    )
    {
        $this->service = $_HotelWifiNetworksServices;
    }

    public function store (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->store($request, $hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.store');
        }
    }

    public function updateById (Request $request) {
        try {
            // $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateById($request,$request->networkId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateById');
        }
    }

    public function updateVisibilityNetwork (Request $request) {
        try {
            // $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateVisibilityNetwork($request->id, $request->visible);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisibilityNetwork');
        }
    }

    public function getAllByHotel (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAllByHotel($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllByHotel');
        }
    }

    
    
    
}
