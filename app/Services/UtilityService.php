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
use Illuminate\Support\Str;

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
            // $citySlug = \Str::slug($modelHotel->zone);
            // $cityData  = $this->cityService->findByParams([ 'slug' => $citySlug]);



            if($modelHotel->show_facilities == 1){
                $facilities = $this->facilityService->getCrosselling($modelHotel, 3);


                //$crossellingPlacesWhatvisit = PlaceResource::collection($placesWhatvisit)->toArray(request());
                $facilities = $facilities->map(function ($item) use($modelHotel, $chainSubdomain, $url_bucket){
                    return [
                        'title' => Str::limit($item->title, 28, '...'),
                        'url_webapp' => buildUrlWebApp($chainSubdomain, $modelHotel->subdomain,"instalaciones/ver-instalacion/{$item->id}"),
                        'url_image' => count($item->images) > 0 ? $url_bucket.$item->images[0]->url : null
                    ];
                });

            }

            $helpers = $this->api_helpers_service->get_crosseling_hotel($modelHotel);
            //dd($helpers,$modelHotel->city_id);
            //dd($helpers);
            //places
            $placesArr = [];
            if (!empty($helpers['crosselling_places_whatvisit'][0])) {
                $placesArr[] = $helpers['crosselling_places_whatvisit'][0];
            }
            if (!empty($helpers['crosselling_places_whereeat'][0])) {
                $placesArr[] = $helpers['crosselling_places_whereeat'][0];
            }
            if (!empty($helpers['crosselling_places_leisure'][0])) {
                $placesArr[] = $helpers['crosselling_places_leisure'][0];
            }


            $placesArr = array_map(function($item) use($modelHotel, $chainSubdomain, $url_bucket){
                $img = null;
                if($item['image']){
                    $img = $url_bucket."/storage/places/".$item['image']['image'];
                }

                return [
                    'title' => Str::limit($item['title'], 28, '...'),
                    'image' => $img,
                    'num_stars' => str_replace(',', '.', $item['num_stars']),
                    'url_webapp' => buildUrlWebApp($chainSubdomain, $modelHotel->subdomain,"lugares/{$item['id']}"),
                ];
            }, $placesArr);


            //experiences
            $experiences = $helpers['crosselling_experiences'] ?? [];
            $experiencesArr = [];

            if (!empty($experiences[0])) {
                $experiencesArr[] = $experiences[0];
            }
            if (!empty($experiences[1])) {
                $experiencesArr[] = $experiences[1];
            }

            //dd($experiencesArr);
            /* $experiences = array_map(function($item) use($modelHotel, $chainSubdomain, $url_bucket){
                $formattedRating = number_format($item['reviews']['combined_average_rating'], 1);
                return [
                    'title' => Str::limit($item['title'], 28, '...'),
                    'url_webapp' => buildUrlWebApp($chainSubdomain, $modelHotel->subdomain,"experiencias/{$item['slug']}"),
                    'image_url' => $item['image']['url'],
                    'num_stars' => $formattedRating
                ];
            }, $experiencesArr); */

            return [
                'facilities' => $facilities,
                'places' => $placesArr,
                'experiences' => null

            ];
        } catch (\Exception $e) {
            Log::error('Error service getCrossellingHotelForMail: ' . $e->getMessage());
            $e;
        }

    }


}
