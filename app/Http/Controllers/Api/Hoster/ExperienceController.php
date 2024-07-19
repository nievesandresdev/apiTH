<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Products;

use App\Services\Hoster\ExperienceService;

use App\Http\Resources\ExperienceResource;
use App\Http\Resources\ExperienceDetailResource;

use Illuminate\Support\Str;
use App\Utils\Enums\EnumResponse;
use App\Services\CityService;

use App\Http\Resources\ExperiencePaginateResource;

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

            $hotelModel = $request->attributes->get('hotel');
            return $hotelModel;
            $lengthAExpFeatured = 12;
            $hotelId = $hotelModel->id;
            $priceMin = $request->price_min ?? null;
            $priceMax = $request->price_max ?? null;
            $priceMax = $request->price_max ?? null;
            $priceMax = $request->price_max ?? null;
            $search = $request->search ?? null;
            $cityName = $request->city ?? $hotelModel->zone;       
            $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';
            $all_cities = boolval($request->all_cities) ?? false;
            $city_latitude = $request->city_latitude;
            $city_longitude = $request->city_longitude;
            $one_exp_id = $request->one_exp_id ?? null;
            $visibility = $request->visibility ?? null;
            $duration = [];
            if (!empty($request->duration)) {
                $duration = gettype($request->duration) == 'string' ? json_decode($request->duration, true) : $request->duration;
            }

            //crear array de ciudades para la consulta
            $citySlug = Str::slug($hotelModel->zone);
            $cityModel  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            $dataFilter = [
                'city' => $cityName,
                'all_cities' => $all_cities,
                'search' => $search,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'duration' => $duration,
                'featured' => $featured,
                'one_exp_id' => $one_exp_id,
                'visibility' => $visibility,
            ];

            $queryExperiences = $this->service->queryGetAll($request, $hotelModel, $dataFilter, $cityModel);

            $queryExperiencesVisibles = clone $queryExperiences;

            $countVisible = $queryExperiencesVisibles->where(function ($query) use ($hotelModel) {
                $query->whereHas('toggleableHotels', function ($q) use ($hotelModel) {
                    $q->where('hotel_id', $hotelModel->id);
                });
            })->count();

            $productspaginate = $queryExperiences->paginate(20)->appends(request()->except('page'));
            
            // return $products->total();
            $countHidden = $productspaginate->total() - $countVisible;

            $productsCollection = new ExperiencePaginateResource($productspaginate);
            
            $data = [
                'visibleNumbers' => $countVisible,
                'hiddenNumbers' => $countHidden,
                'experiences' => $productsCollection,
            ];
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return $e;
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
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
            $featured = $request->featured && $request->featured != 'false' && $request->featured != '0';   
            $duration = [];
            if (!empty($request->duration)) {
                $duration = gettype($request->duration) == 'string' ? json_decode($request->duration, true) : $request->duration;
            }

            $dataFilter = [
                'city' => $cityName,
                'search' => $search,
                'price_min' => $priceMin,
                'price_max' => $priceMax,
                'duration' => $duration,
                'featured' => $featured,
            ];

            $numbersByFilters = $this->service->getNumbersByFilters($request, $modelHotel, $dataFilter);
            $data = ['duration' => $numbersByFilters];

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getNumbersByFilters');
        }
    }
}