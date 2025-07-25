<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\PlaceService;

use App\Http\Resources\PlacePaginateResource;
use App\Http\Resources\PlaceResource;
use App\Services\CityService;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Str;

use App\Http\Resources\TypePlaceResource;

class PlaceController extends Controller
{
    public $service;
    public $cityService;
    function __construct(
        PlaceService $_PlaceService,
        CityService $_CityService
    )
    {
        $this->service = $_PlaceService;
        $this->cityService = $_CityService;
    }

    public function getAll (Request $request) {
        try {

            $modelHotel = $request->attributes->get('hotel');

            $hotelId = $modelHotel->id;
            $search = $request->search ?? null;
            $cityName = $request->city ?? $modelHotel->zone;
            $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';
            $points = $request->points ?? [];

            $typeplace = $request->typeplace ?? null;
            $categoriplace = $request->categoriplace ?? null;


            //crear array de ciudades para la consulta
            $citySlug = Str::slug($modelHotel->zone);
            $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            $dataFilter = [
                'city' => $cityName,
                'cityData' => $cityData,
                'search' => $search,
                'points' => $points,
                'featured' => $featured,
                'typeplace' => $typeplace,
                'categoriplace' => $categoriplace
            ];

            $response = $this->service->getAll($request, $dataFilter, $modelHotel);
            $placesCollection = $response['places'];
            $countOtherCities = $response['countOtherCities'];
            // return count($placesCollection);
            $data = [
                'places' => new PlacePaginateResource($placesCollection),
                'countOtherCities' => $countOtherCities
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function getCategoriesByType (Request $request) {
        try {

            $modelHotel = $request->attributes->get('hotel');

            $hotelId = $modelHotel->id;
            $search = $request->search ?? null;
            $cityName = $request->city ?? $modelHotel->zone;
            $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';
            $points = $request->points ?? [];
            $all = $request->all ?? null;
            $withNumbersPlaces = $request->withNumbersPlaces ?? false;

            $citySlug = Str::slug($modelHotel->zone);
            $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            $typeplace = $request->typeplace ?? null;
            $categoriplace = $request->categoriplace ?? null;

            $dataFilter = [
                'city' => $cityName,
                'cityData' => $cityData,
                'typeplace' => $typeplace,
                'categoriplace' => $categoriplace,
                'all' => $all,
                'search' => $search,
                'points' => $points,
                'featured' => $featured,
                'withNumbersPlaces' => $withNumbersPlaces,
            ];

            $categoriesCollection = $this->service->getCategoriesByType($request, $dataFilter, $modelHotel);
            // return $categoriesCollection;
            // $data = new ExperiencePaginateResource($experiencesCollection);


            return bodyResponseRequest(EnumResponse::ACCEPTED, $categoriesCollection);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getCategoriesByType');
        }

    }

    public function getTypePlaces (Request $request) {

        try {

            $modelHotel = $request->attributes->get('hotel');

            $typePlacesCollection = $this->service->getTypePlaces($request, $modelHotel);

            $typePlacesCollection = TypePlaceResource::collection($typePlacesCollection);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $typePlacesCollection);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getTypePlaces');
        }

    }

    public function findById (Request $request) {

        try {
            $data = $this->service->findById($request);
            $model = new PlaceResource($data);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findById');
        }
    }

    public function getRatingCountsPlaces (Request $request) {

        try {
            $modelHotel = $request->attributes->get('hotel');
            $data = $this->service->getRatingCountsPlaces($request, $modelHotel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getRatingCountsPlaces');
        }
    }

    public function getDataReviews(Request $request){
        try {
            $data = $this->service->getDataReviews($request->id);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getDataReviews');
        }
    }

    public function getReviewsByRating(Request $request){
        try {
            $data = $this->service->getReviewsByRating($request);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getReviewsByRating');
        }
    }



}
