<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\HotelService;
use App\Services\FacilityService;

use App\Http\Resources\HotelResource;

use App\Utils\Enums\EnumResponse;

class HotelController extends Controller
{
    function __construct(
        HotelService $_HotelService,
        FacilityService $_FacilityService
    )
    {
        $this->service = $_HotelService;
        $this->serviceFacility = $_FacilityService;
    }

    public function findByParams (Request $request) {
        try {

            $model = $this->service->findByParams($request);

            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }

            $data = new HotelResource($model);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findByParams');
        }
    }

    public function getAllCrossellings (Request $request) {
        try {

            $hotel = $this->service->findById($request->hotelId);

            if(!$hotel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            
            $crossellingFacilities = $this->serviceFacility->getCrosselling($hotel);

            $data = [
                'crosselling_facilities' => $crossellingFacilities
            ];

            return $data;

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllCrossellings');
        }
    }

}
