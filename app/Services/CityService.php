<?php

namespace App\Services;

use App\Http\Resources\CityResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\City;
use Illuminate\Support\Str;
use App\Http\Resources\FacilityResource;

class CityService {

    function __construct()
    {

    }

    public function getAll ($request) {
        try {

            $modelHotel = $request->attributes->get('hotel');
            $search = $request->search ?? null;

            $collectionCity = City::search($search)->limit(8)->get();

            return $collectionCity;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findByParams ($params) {
        try {
            $slug = $params['slug'] ?? null;

            $query = City::where(function($query) use($slug){
                if ($slug) {
                    $query->where('slug',$slug);
                }
            });
            
            $model = $query->first();

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }
    
    public function getNearCitiesData ($slug_city,$hotel) {
        try {
            
            $cityData  = $this->findByParams([ 'slug' => $slug_city]);
            $near_distances_km = [];
            foreach ($cityData->near as $item) {
                if(ucwords($item['name']) !== ucwords($hotel->zone)){
                    $near_distances_km[Str::slug($item['name'])] = round($item['distance'] / 1000, 2);
                }
            }
            return $near_distances_km;
        } catch (\Exception $e) {
            return $e;
        }
    }
}