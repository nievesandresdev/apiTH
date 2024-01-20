<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\UtilityService;
use App\Services\ExperienceService;
use App\Services\PlaceService;

// use App\Http\Resources\AutocompleteResource;

use App\Utils\Enums\EnumResponse;

class UtilityController extends Controller
{
    function __construct(
        UtilityService $_UtilityService,
        ExperienceService $_ExperienceService,
        PlaceService $_PlaceService
    )
    {
        $this->service = $_UtilityService;
        $this->experienceService = $_ExperienceService;
        $this->placeService = $_PlaceService;
    }

    public function getExpAndPlace (Request $request) {
        try {
            $modelHotel = $request->attributes->get('hotel');
            $typeSearch = $request->typeSearch;
            $search = $request->search ?? null;
            $city = $modelHotel->zone;

            $data = collect([]);
            $totalLength = 8;
            if($typeSearch == 'place'){
                $responseService = $this->placeService->getPlacesBySearch ($modelHotel, $search, $totalLength);
            }else{
                $responseService = $this->experienceService->getExperiencesBySearch($modelHotel, $search, $totalLength);
            };
            return bodyResponseRequest(EnumResponse::ACCEPTED, $responseService);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getExpAndPlace');
        }
    }

}