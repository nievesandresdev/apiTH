<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Services\PlaceService;

use App\Http\Resources\PlacePaginateResource;
use App\Http\Resources\PlaceResource;
use App\Utils\Enums\EnumResponse;

class PlaceController extends Controller
{
    function __construct(
        PlaceService $_PlaceService
    )
    {
        $this->service = $_PlaceService;
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

            $dataFilter = [
                'city' => $cityName,
                'search' => $search,
                'points' => $points,
                'featured' => $featured,
                'typeplace' => $typeplace,
                'categoriplace' => $categoriplace
            ];

            $placesCollection = $this->service->getAll($request, $dataFilter, $modelHotel);
            $data = new PlacePaginateResource($placesCollection);
            

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

            $typeplace = $request->typeplace ?? null;
            $categoriplace = $request->categoriplace ?? null;

            $dataFilter = [
                'city' => $cityName,
                'typeplace' => $typeplace,
                'categoriplace' => $categoriplace,
                'all' => $all,
                'search' => $search,
                'points' => $points,
                'featured' => $featured,
            ];

            $categoriesCollection = $this->service->getCategoriesByType($request, $dataFilter, $modelHotel);
            // return $categoriesCollection;
            // $data = new ExperiencePaginateResource($experiencesCollection);
            

            return bodyResponseRequest(EnumResponse::ACCEPTED, $categoriesCollection);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getCategoriesByType');
        }

    }

    public function getTypePlaces (Request $request) {

        try {

            $modelHotel = $request->attributes->get('hotel');

            $typePlacesCollection = $this->service->getTypePlaces($request, $modelHotel);
            // return $typePlacesCollection;
            // $data = new ExperiencePaginateResource($experiencesCollection);
            

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
