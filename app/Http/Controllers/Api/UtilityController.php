<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Guest;
use App\Models\hotel;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\UtilityService;
use App\Services\ExperienceService;
use App\Services\GuestService;
use App\Services\PlaceService;

// use App\Http\Resources\AutocompleteResource;

use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UtilityController extends Controller
{
    protected $service;
    protected $experienceService;
    protected $placeService;
    protected $guestServices;

    function __construct(
        UtilityService $_UtilityService,
        ExperienceService $_ExperienceService,
        PlaceService $_PlaceService,
        GuestService $_GuestService
    )
    {
        $this->service = $_UtilityService;
        $this->experienceService = $_ExperienceService;
        $this->placeService = $_PlaceService;
        $this->guestServices = $_GuestService;
    }

    public function getExpAndPlace (Request $request) {
        try {
            $modelHotel = $request->attributes->get('hotel');
            $typeSearch = $request->typeSearch;
            $typePlace = $request->typePlace;
            $categoryPlace = $request->categoryPlace;
            $search = $request->search ?? null;
            $city = $modelHotel->zone;

            $data = collect([]);
            $totalLength = 8;
            if($typeSearch == 'place'){
                $responseService = $this->placeService->getPlacesBySearch ($modelHotel, $search, $totalLength, $typePlace, $categoryPlace);
            }else{
                $responseService = $this->experienceService->getExperiencesBySearch($modelHotel, $search, $totalLength);
            };
            return bodyResponseRequest(EnumResponse::ACCEPTED, $responseService);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getExpAndPlace');
        }
    }


    public function getPhoneCodesApi(Request $request)
    {
        
        $data = json_decode(Storage::disk('local')->get('phone-codes.json'), true);
        if($data){
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        }
        $data = [
            'message' => __('response.bad_request_long')
        ];
        return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
        
    }

    public function updateGuestsAcronyms(Request $request){
        
        $guests = Guest::select('id','name','email','phone','lang_web','acronym')->get();
        // return $gi= $guests->where('id',70)->first();
        // return $this->guestService->updateById($gi);
        foreach ($guests as $guest) {
            $this->guestServices->updateById($guest);
        }
        return 'terminado con exito!';
     }


}