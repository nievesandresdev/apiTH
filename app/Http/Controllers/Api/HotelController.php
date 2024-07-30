<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;
use App\Models\Hotel;

use App\Services\HotelService;
use App\Services\FacilityService;
use App\Services\ExperienceService;
use App\Services\PlaceService;
use App\Services\CityService;

use App\Http\Resources\HotelResource;
use App\Http\Resources\FacilityResource;
use App\Http\Resources\ExperienceResource;
use App\Http\Resources\PlaceResource;
use App\Http\Resources\HotelBasicDataResource;

use App\Utils\Enums\EnumResponse;

use App\Http\Requests\Hotel\UpdateProfileRequest;

class HotelController extends Controller
{
    function __construct(
        HotelService $_HotelService,
        FacilityService $_FacilityService,
        ExperienceService $_ExperienceService,
        PlaceService $_PlaceService,
        CityService $_CityService
    )
    {
        $this->service = $_HotelService;
        $this->serviceFacility = $_FacilityService;
        $this->serviceExperience = $_ExperienceService;
        $this->servicePlace = $_PlaceService;
        $this->cityService = $_CityService;
    }

    public function getAll (Request $request) {
        try {
            $modelHotel = $request->attributes->get('hotel');
            $hotelsCollection = $this->service->getAll($request, $modelHotel);
            $data = HotelBasicDataResource::collection($hotelsCollection);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
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
            $modelHotel = $request->attributes->get('hotel');

            // $modelTypePlaces = TypePlaces::all();

            //crear array de ciudades para la consulta
            $citySlug = \Str::slug($modelHotel->zone);
            $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            // $leisureId = $modelTypePlaces->where('name','Ocio')->first()->id;
            // $whereeatId = $modelTypePlaces->where('name','Dónde comer')->first()->id;
            // $whatvisitId = $modelTypePlaces->where('name','Qué visitar')->first()->id;
            

            $facilities = $this->serviceFacility->getCrosselling($modelHotel);
            $crossellingFacilities = FacilityResource::collection($facilities);
            
            $experiences = $this->serviceExperience->getCrosselling($modelHotel, $cityData);
            $crossellingExperiences = ExperienceResource::collection($experiences);

            // $placesLeisure = $this->servicePlace->getCrosselling('Ocio', $modelHotel);
            // $crossellingPlacesLeisure = PlaceResource::collection($placesLeisure)->toArray(request());

            // $placesWhereeat = $this->servicePlace->getCrosselling('Dónde comer', $modelHotel);
            // $crossellingPlacesWhereeat = PlaceResource::collection($placesWhereeat)->toArray(request());

            // $placesWhatvisit = $this->servicePlace->getCrosselling('Qué visitar', $modelHotel);
            // $crossellingPlacesWhatvisit = PlaceResource::collection($placesWhatvisit)->toArray(request());

            $data = [
                'crosselling_facilities' => $crossellingFacilities,
                'crosselling_experiences' => $crossellingExperiences,
                // 'crosselling_places_leisure' => $crossellingPlacesLeisure,
                // 'crosselling_places_whereeat' => $crossellingPlacesWhereeat,
                // 'crosselling_places_whatvisit' => $crossellingPlacesWhatvisit,
                // 'leisure_id' => $leisureId,
                // 'whereeat_id' => $whereeatId,
                // 'whatvisit_id' => $whatvisitId
            ];

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllCrossellings');
        }
    }

    public function getChatHours (Request $request) {
        try {

            $hotel = $request->attributes->get('hotel');
            $hotelId = $hotel->id;
            $model = $this->service->getChatHours($hotelId);

            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getChatHours');
        }
    }

    public function updateProfile (UpdateProfileRequest $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            
            $traslationProfile = $this->service->processTranslateProfile($request, $hotelModel);
            
            $hotelModel = $this->service->updateProfile($request, $hotelModel);

            $this->service->asyncImages($request, $hotelModel);

            $hotelModel->refresh();
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateProfile');
        }
    }

    public function updateVisivilityFacilities (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            
            $this->service->updateVisivilityFacilities($hotelModel);

            $hotelModel->refresh();
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisivilityFacilities');
        }
    }
    
    public function updateVisivilityExperiences (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            
            $this->service->updateVisivilityExperiences($hotelModel);

            $hotelModel->refresh();
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisivilityFacilities');
        }
    }

    public function updateVisivilityPlaces (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            
            $this->service->updateVisivilityPlaces($hotelModel);
            $hotelModel->refresh();
            $data = new HotelResource($hotelModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisivilityPlaces');
        }
    }

    public function updateVisivilityCategory (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            
            $this->service->updateVisivilityCategory($request, $hotelModel);

            $hotelModel->refresh();
            $data = new HotelResource($hotelModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisivilityCategory');
        }
    }

    public function updateVisivilityTypePlace (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }

            $this->service->updateVisivilityTypePlace($request, $hotelModel);

            $hotelModel->refresh();
            $data = new HotelResource($hotelModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisivilityTypePlace');
        }
    }

}
