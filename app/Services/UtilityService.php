<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\User;
use App\Models\Stay;

use App\Http\Resources\{PlaceResource};
use App\Services\Apis\ApiHelpersServices;
use Google\Service\ApigeeRegistry\Api;

class UtilityService {

    public $facilityService;
    public $serviceExperience;
    public $cityService;
    public $servicePlace;
    public $api_helpers_service;


    function __construct(
        FacilityService $_FacilityService,
        ExperienceService $_ExperienceService,
        CityService $_CityService,
        PlaceService $_PlaceService,
        ApiHelpersServices $_api_helpers_service
    )
    {
        $this->facilityService = $_FacilityService;
        $this->serviceExperience = $_ExperienceService;
        $this->cityService = $_CityService;
        $this->servicePlace = $_PlaceService;
        $this->api_helpers_service = $_api_helpers_service;
    }

    public function getExpAndPlace ($request, $modelHotel) {
        try {
            $routeName = $request->routeName;
            $search = $request->search ?? null;
            $user = $modelHotel->user()->first();
            $city = $modelHotel->zone;

            $data = collect([]);
            $total_length = 8;

            if($routeName == 'places.list' || $route_name == 'places.show'){
                $data = $this->service->getPlacesBySearch ($search, $placelang, $total_length, $city, $hotel, $user);
            }else{
                $data = $this->getExperiencesBySearch($search, $actlang, $total_length, $city, $hotel, $user);
            };

        } catch (\Exception $e) {
            $e;
        }

    }

    public function getCrossellingHotelForMail ($modelHotel, $chainSubdomain) {

        try {
            $url_bucket  = config('app.url_bucket');
            $facilities = [];
            $citySlug = \Str::slug($modelHotel->zone);
            $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);

            if($modelHotel->show_facilities){
                $facilities = $this->facilityService->getCrosselling($modelHotel, 3);

                //$crossellingPlacesWhatvisit = PlaceResource::collection($placesWhatvisit)->toArray(request());
                $facilities = $facilities->map(function ($item) use($modelHotel, $chainSubdomain, $url_bucket){
                    return [
                        'title' => $item->title,
                        'url_webapp' => buildUrlWebApp($chainSubdomain, $modelHotel->subdomain,"ver-instalacion/{$item->id}"),
                        'url_image' => $url_bucket.$item->images[0]->url
                    ];
                });
            }
            $helpers = $this->api_helpers_service->get_crosseling_hotel($modelHotel);
            //
            return [
                'facilities' => $facilities,
                'helpers' => $helpers

            ];
        } catch (\Exception $e) {
            $e;
        }

    }


}
