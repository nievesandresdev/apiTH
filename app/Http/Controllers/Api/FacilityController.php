<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\HotelService;
use App\Services\FacilityService;
use App\Services\ExperienceService;
use App\Services\PlaceService;

use App\Http\Resources\HotelResource;
use App\Http\Resources\FacilityResource;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\PlaceResource;

use App\Utils\Enums\EnumResponse;

class FacilityController extends Controller
{
    public $service;
    function __construct(
        FacilityService $_FacilityService
    )
    {
        $this->service = $_FacilityService;
    }

    public function getAll (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $facilities = $this->service->getAll($hotel);

            if(!$facilities){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            //
            return $facilities;
            $data = FacilityResource::collection($facilities);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }


    public function findById ($id, Request $request) {
        try {

            $hotel = $request->attributes->get('hotel');
            $model = $this->service->findById($id,$hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }

            $data = new FacilityResource($model);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findByParams');
        }
    }


}
