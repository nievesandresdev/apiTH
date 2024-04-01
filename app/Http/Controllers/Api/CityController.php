<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\CityService;

use App\Http\Resources\CityResource;

use App\Utils\Enums\EnumResponse;

class CityController extends Controller
{
    public $service;

    function __construct(
        CityService $_CityService
    )
    {
        $this->service = $_CityService;
    }

    public function getAll (Request $request) {
        try {

            $responseService = $this->service->getAll($request);

            $data = CityResource::collection($responseService);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function getNearCitiesData (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $response = $this->service->getNearCitiesData($hotel->zone,$hotel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $response);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getNearCitiesData');
        }
    }

}
