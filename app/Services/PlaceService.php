<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Places;
use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\User;

use App\Http\Resources\FacilityResource;

class PlaceService {

    function __construct()
    {

    }

    public function getCrosselling ($typePlaceName, $modelHotel) {
        try {
            $lengthPlaceFeatured = 12;
            $hotelId = $modelHotel->id;
            $cityName = $modelHotel->zone;

            $modelExperiencesFeatured = Places::activeToShow()
                                    ->whereCity($cityName)
                                    ->whereTypePlaceByName($typePlaceName)
                                    ->whereVisibleByHoster($hotelId)
                                    ->orderByFeatured($hotelId)
                                    ->limit($lengthPlaceFeatured)
                                    ->get();
                            
            return $modelExperiencesFeatured;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getPlacesBySearch($modelHotel, $search, $totalLength) {
        try {
            $hotelId = $modelHotel->id;
            $cityName = $modelHotel->zone;
            $places = Places::activeToShow()
                ->whereVisibleByHoster($hotelId)
                ->whereCity($cityName)
                ->whereHas('translatePlace', function($query)use($search){
                    if ($search) {
                        $query->where('title','like',  ['%'.$search.'%'])
                        ->orWhere('description','like',  ['%'.$search.'%']);
                    }
                })
                ->orderByFeatured($hotelId)
                ->limit($totalLength)->get();
                
            $places = $places->map(function($item){
                $image = $item->images()->first();
                return [
                    'id' => $item->id,
                    'type' => 'place',
                    'title' => $item->translatePlace->title,
                    'price' => 0,
                    'slug' => null,
                    'city' => $item->city_places,
                    'image' => $image,
                ];
            })->values()->collect();
            return $places;
        } catch (\Exception $e) {
            return $e;
        }
    }

}
