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




class UtilityService {

    public $facilityService;
    public $serviceExperience;
    public $cityService;
    public $servicePlace;

    function __construct(
        FacilityService $_FacilityService,
        ExperienceService $_ExperienceService,
        CityService $_CityService,
        PlaceService $_PlaceService
    )
    {
        $this->facilityService = $_FacilityService;
        $this->serviceExperience = $_ExperienceService;
        $this->cityService = $_CityService;
        $this->servicePlace = $_PlaceService;
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
                $experiences = $this->serviceExperience->getCrosselling($modelHotel, $cityData);
                $placesLeisure = $this->servicePlace->getCrosselling('Ocio', $modelHotel);
                //$crossellingPlacesLeisure = PlaceResource::collection($placesLeisure)->toArray(request());

                $placesWhereeat = $this->servicePlace->getCrosselling('Dónde comer', $modelHotel);
                //$crossellingPlacesWhereeat = PlaceResource::collection($placesWhereeat)->toArray(request());

                $placesWhatvisit = $this->servicePlace->getCrosselling('Qué visitar', $modelHotel);
                //$crossellingPlacesWhatvisit = PlaceResource::collection($placesWhatvisit)->toArray(request());
                $facilities = $facilities->map(function ($item) use($modelHotel, $chainSubdomain, $url_bucket){
                    return [
                        'title' => $item->title,
                        'url_webapp' => buildUrlWebApp($chainSubdomain, $modelHotel->subdomain,"ver-instalacion/{$item->id}"),
                        'url_image' => $url_bucket.$item->images[0]->url
                    ];
                });
            }
            //
            return [
                'facilities' => $facilities,
                'experiences' => $experiences,
                'placesLeisure' => $placesLeisure,
                'placesWhereeat' => $placesWhereeat,
                'placesWhatvisit' => $placesWhatvisit
            ];
        } catch (\Exception $e) {
            $e;
        }

    }


}
