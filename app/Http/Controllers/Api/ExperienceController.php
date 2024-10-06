<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\ExperienceService;

use App\Http\Resources\ExperienceResource;
use App\Http\Resources\ExperienceDetailResource;
use App\Http\Resources\ExperiencePaginateResource;
use Illuminate\Support\Str;
use App\Utils\Enums\EnumResponse;
use App\Services\CityService;

class ExperienceController extends Controller
{
    public $service;
    public $cityService;

    function __construct(
        ExperienceService $_ExperienceService,
        CityService $_CityService
    )
    {
        $this->service = $_ExperienceService;
        $this->cityService = $_CityService;
    }

    public function getAll (Request $request) {
        try {

            $modelHotel = $request->attributes->get('hotel');
            $lengthAExpFeatured = 12;
            $hotelId = $modelHotel->id;
            $priceMin = $request->price_min ?? null;
            $priceMax = $request->price_max ?? null;
            $search = $request->search ?? null;
            $cityName = $request->city ?? $modelHotel->zone;
            $all_cities = boolval($request->all_cities) ?? false;
            // $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';
            $featured = boolval($request->featured) ?? false;
            // $free_cancelation = $request->free_cancelation && $request->free_cancelation != 'false' && $request->free_cancelation != '0';
            $free_cancelation = boolval($request->free_cancelation) ?? false;
            $one_exp_id = $request->one_exp_id ?? null;
            $duration = [];
            if (!empty($request->duration)) {
                $duration = gettype($request->duration) == 'string' ? json_decode($request->duration, true) : $request->duration;
            }
            //crear array de ciudades para la consulta
            $citySlug = Str::slug($modelHotel->zone);
            $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);
            $dataFilter = [
                'city' => $cityName,
                'cityData' => $cityData,
                'search' => $search,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'all_cities' => $all_cities,
                'free_cancelation' => $free_cancelation,
                'duration' => $duration,
                'score' => $request->score ?? [],
                'one_exp_id' => $one_exp_id,
                'featured' => $featured,
            ];

            $response = $this->service->getAll($request, $modelHotel, $dataFilter);
            $expsCollection = $response['experiences'];
            $countOtherCities = $response['countOtherCities'];
            $data = [
                'experiences' => new ExperiencePaginateResource($expsCollection),
                'countOtherCities' => $countOtherCities
            ];

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function findBySlug (Request $request) {
        // try {

            $experienceModel = $this->service->findBySlug($request);

            if(!$experienceModel){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new ExperienceDetailResource($experienceModel);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        // } catch (\Exception $e) {
        //     return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findBySlug');
        // }
    }
    
    public function getNumbersByFilters (Request $request) {
        try {

            $modelHotel = $request->attributes->get('hotel');
            $lengthAExpFeatured = 12;
            $hotelId = $modelHotel->id;
            $priceMin = $request->price_min ?? null;
            $priceMax = $request->price_max ?? null;
            $search = $request->search ?? null;
            $cityName = $request->city ?? $modelHotel->zone;    
            $citySlug = Str::slug($modelHotel->zone);
            $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);
            // $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';   
            $featured = boolval($request->featured) ?? false;
            $free_cancelation = boolval($request->free_cancelation) ?? false;
            $one_exp_id = $request->one_exp_id ?? null;
            $all_cities = boolval($request->all_cities) ?? false;
            $duration = [];
            if (!empty($request->duration)) {
                $duration = gettype($request->duration) == 'string' ? json_decode($request->duration, true) : $request->duration;
            }

            $dataFilter = [
                'city' => $cityName,
                'cityData' => $cityData,
                'search' => $search,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'duration' => $duration,
                'all_cities' => $all_cities,
                'free_cancelation' => $free_cancelation,
                'duration' => $duration,
                'score' => $request->score ?? [],
                'one_exp_id' => $one_exp_id,
                'featured' => $featured,
            ];

            $numbersByFilters = $this->service->getNumbersByFilters($request, $modelHotel, $dataFilter);
            return $numbersByFilters;
            $data = ['duration' => $numbersByFilters];

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getNumbersByFilters');
        }
    }

    public function findInVIatorByShortId (Request $request) {
        try {
            $shortId = $request->shortId ?? null;
            $experience = $this->service->findInVIatorByShortId($shortId);
            if(!$experience){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $experience);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findInVIatorByShortId');
        }
    }

    public function findSchedulesInVIator (Request $request) {
        try {
            $shortId = $request->shortId ?? null;
            $schedules = $this->service->findSchedulesInVIator($shortId);
            if(!$schedules){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $schedules);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findSchedulesInVIator');
        }
    }

}
