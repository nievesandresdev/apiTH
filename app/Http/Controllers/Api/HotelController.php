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
use App\Http\Resources\HotelMainDataWebappResource;

class HotelController extends Controller
{
    protected $service;
    protected $serviceFacility;
    protected $serviceExperience;
    protected $servicePlace;
    protected $cityService;

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
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function getHotelsByUser(Request $request) {
        try {
            $modelHotel = $request->attributes->get('hotel');
            $hotelsCollection = $this->service->getHotelsByUser($request, $modelHotel);
            $data = HotelBasicDataResource::collection($hotelsCollection);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getHotelsByUser');
        }
    }

    public function updateDefaultHotel(Request $request){
        try {
            $hotel = $this->service->updateDefaultHotel($request);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotel);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateDefaultHotel');
        }
    }

    public function findById($id){
        try {

            $data = $this->service->findById($id);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findById');
        }
    }

    public function getRewardsByHotel(Request $request){
        try {
            $modelHotel = $request->attributes->get('hotel');
            $data = $this->service->getRewardsByHotel($modelHotel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getRewardsByHotel');
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

    public function getMainData (Request $request) {
        try {
            $model = $this->service->getMainData($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }

            $data = new HotelMainDataWebappResource($model);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getMainData');
        }
    }

    public function getAllCrossellings (Request $request) {
        try {
            $modelHotel = $request->attributes->get('hotel');
            //return bodyResponseRequest(EnumResponse::ACCEPTED, $modelHotel);
            // $modelTypePlaces = TypePlaces::all();

            //crear array de ciudades para la consulta
            $citySlug = \Str::slug($modelHotel->zone);
            $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            // $leisureId = $modelTypePlaces->where('name','Ocio')->first()->id;
            // $whereeatId = $modelTypePlaces->where('name','Dónde comer')->first()->id;
            // $whatvisitId = $modelTypePlaces->where('name','Qué visitar')->first()->id;


            $facilities = $this->serviceFacility->getCrosselling($modelHotel);
            $crossellingFacilities = FacilityResource::collection($facilities);

            // $experiences = $this->serviceExperience->getCrosselling($modelHotel, $cityData);
            // $crossellingExperiences = ExperienceResource::collection($experiences);

            // $placesLeisure = $this->servicePlace->getCrosselling('Ocio', $modelHotel);
            // $crossellingPlacesLeisure = PlaceResource::collection($placesLeisure)->toArray(request());

            // $placesWhereeat = $this->servicePlace->getCrosselling('Dónde comer', $modelHotel);
            // $crossellingPlacesWhereeat = PlaceResource::collection($placesWhereeat)->toArray(request());

            // $placesWhatvisit = $this->servicePlace->getCrosselling('Qué visitar', $modelHotel);
            // $crossellingPlacesWhatvisit = PlaceResource::collection($placesWhatvisit)->toArray(request());

            $data = [
                'crosselling_facilities' => $crossellingFacilities,
                // 'crosselling_experiences' => $crossellingExperiences,
                // 'crosselling_places_leisure' => $crossellingPlacesLeisure,
                // 'crosselling_places_whereeat' => $crossellingPlacesWhereeat,
                // 'crosselling_places_whatvisit' => $crossellingPlacesWhatvisit,
                // 'leisure_id' => $leisureId,
                // 'whereeat_id' => $whereeatId,
                // 'whatvisit_id' => $whatvisitId
            ];

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
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

    public function updateProfile(UpdateProfileRequest $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);

            // return bodyResponseRequest(EnumResponse::ACCEPTED, [$hotelModel,$request->all()]);
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

    public function buildUrlWebApp (Request $request) {
        try {
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $hotelSlug = $request->slugHotel ?? null;
            $uri = $request->uri ?? null;
            $paramsString = $request->paramsString ?? null;

            $url = buildUrlWebApp($chainSubdomain, $hotelSlug, $uri, $paramsString);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $url);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.buildUrlWebApp');
        }
    }



    public function updateShowButtons(Request $request)
    {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);

            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }


            $hotelModel = $this->service->updateShowButtons($request, $hotelModel);

            //$this->service->asyncImages($request, $hotelModel);

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

    public function updateVisivilityServices (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $this->service->updateVisivilityServices($request, $hotelModel);

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

    public function updateSenderMailMask (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }

            $this->service->updateSenderMailMask($hotelModel, $request->email);
            $hotelModel->refresh();
            $data = new HotelResource($hotelModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateSenderMailMask');
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

            $r = $this->service->updateVisivilityCategory($request, $hotelModel);

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
            \DB::beginTransaction();
            $hotelModel = $request->attributes->get('hotel');
            // return $hotelModel;
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            if(!$hotelModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }

            $r = $this->service->updateVisivilityTypePlace($request, $hotelModel);
            // return $r;
            \DB::commit();
            $hotelModel->refresh();
            $data = new HotelResource($hotelModel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            \DB::rollback();
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisivilityTypePlace');
        }
    }

    public function verifySubdomainExist (Request $request) {
        $hotel_id = $request->hotel_id;
        $subdomain = $request->subdomain;
        $hotel = hotel::find($hotel_id);
        if (!$hotel || $hotel->subdomain == $subdomain) {
            return  false;
        }
        $exist = hotel::where(['subdomain' => $subdomain])->exists();
        return $exist;
    }

    public function verifySubdomainExistPerHotel (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $subdomain = $request->subdomain;

            $exist = $this->service->verifySubdomainExistPerHotel($subdomain, $hotelModel);
            $data = [
                "exist" => $exist
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.verifySubdomainExistPerHotel');
        }
    }

    public function storeSubdomain (Request $request) {
        $subdomain = $request->subdomain ?? null;
        $response = createSubdomainHotelServe($request->subdomain);
        return $response;
    }

    public function updateCustomization (Request $request) {
        try {
            $environment = env('APP_ENV');
            $hotelModel = $request->attributes->get('hotel');
            $hotelModel = Hotel::with('translations')->find($hotelModel->id);
            \DB::beginTransaction();

            $subdomainChain = $request->subdomain_chain;
            $exitsSubdomain = $this->service->verifySubdomainExistPerHotel($subdomain, $hotelModel);
            $subdomainIsNotNew = $this->service->verifySubdomainExist($subdomain, $hotelModel);
            $newSubdomainParam = false;
            if (!$exitsSubdomain && !$subdomainIsNotNew) {
                if ($environment !== 'LOCAL') {
                    // $r_s = $this->service->createSubdomainInCloud($subdomain, $environment);
                    $newSubdomainParam = true;
                }
            }
            $this->service->updateSubdomain($subdomain, $hotelModel);

            $this->service->updateCustomization($request, $hotelModel);


            \DB::commit();

            $hotelModel->refresh();

            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel);
        } catch (\Exception $e) {
            \DB::rollback();
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateCustomization');
        }
    }

    public function handleShowReferrals (Request $request) {
        try {
            $hotelModel = $request->attributes->get('hotel');
            $data = $this->service->handleShowReferrals($hotelModel);
            $hotelModel->refresh();
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.handleShowReferrals');
        }
    }

    public function getDataLegal (Request $request) {
        try {
            $environment = env('APP_ENV');
            $hotelModel = $request->attributes->get('hotel');
            if($hotelModel && $hotelModel->generalLegal){
                return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel->generalLegal);
            }else{
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelModel);
        } catch (\Exception $e) {
            \DB::rollback();
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getDataLegal');
        }
    }


}
