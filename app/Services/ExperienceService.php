<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

use App\Models\Products;
use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\User;


use App\Http\Resources\FacilityResource;

class ExperienceService {

    function __construct()
    {

    }

    public function getAll ($request, $modelHotel, $dataFilter) {
        try {
            
            $queryExperience = $this->filter($request, $modelHotel, $dataFilter);
            $productsCountOtherCities = clone $queryExperience;
            $productsCountOtherCities->get();
            $countOtherCities = $productsCountOtherCities->whereDiffLocaleCity($modelHotel->zone)->count();
            // ->scopeOrderByCityAndFeatures($modelHotel->zone, $modelHotel->id)
            $collectionExperiences = $queryExperience->orderByCityAndFeatures($modelHotel->zone, $modelHotel->id)
                // ->orderByASpecificCity($modelHotel->zone)
                // ->orderByFeatured($modelHotel->id)
                ->orderBy('distance', 'asc')
                // ->orderByWeighing($modelHotel->id)
                ->paginate(20)
                ->appends(request()->except('page'));
            return ['experiences' => $collectionExperiences, 'countOtherCities' => $countOtherCities];

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getNumbersByFilters ($request, $modelHotel, $dataFilter) {
        try {
            
            $queryExperience = $this->filter($request, $modelHotel, $dataFilter);
            
            $countByFilterDuration = [
                '1' => ['name' =>'Hasta una hora', 'count' => $this->filter($request, $modelHotel, [...$dataFilter, 'duration'=>['1']])->count() ],
                '2' => ['name' =>'Entre 1 y 3 horas', 'count' => $this->filter($request, $modelHotel, [...$dataFilter, 'duration'=>['2']])->count() ],
                '3' => ['name' =>'Medio día', 'count' => $this->filter($request, $modelHotel, [...$dataFilter, 'duration'=>['3']])->count() ],
                '4' => ['name' =>'Día completo', 'count' => $this->filter($request, $modelHotel, [...$dataFilter, 'duration'=>['4']])->count()]
            ];
                            
            return $countByFilterDuration;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function filter ($request, $modelHotel, $dataFilter) {
        $durations =  [['i'=>0,'f'=>60],['i'=>61,'f'=>180],['i'=>181,'f'=>480],['i'=>481,'f'=>100000]];

        $user = $modelHotel['user'][0];

        $queryExperience = Products::activeToShow()
        ->select(
            'products.id',
            'products.status',
            'products.destacado',
            'products.slug',
            'products.recomend',
            'products.select',
            'products.from_price',
            'products.reviews',
            \DB::raw("(
                SELECT ST_Distance_Sphere(
                    point(a.metting_point_longitude, a.metting_point_latitude),
                    point(".$dataFilter['cityData']->long.", ".$dataFilter['cityData']->lat.")
                )
                FROM activities a
                WHERE a.products_id = products.id
                ORDER BY a.id ASC
                LIMIT 1
            ) AS distance"),
        );
        
        $queryExperience->whereVisibleByHoster($modelHotel->id);
        
        // if(isset($dataFilter['cities'])){
        //     $queryExperience->whereCities($dataFilter['cities']);
        // }else{
        //     $queryExperience->whereCity($dataFilter['city']);
        // }
        

        if($dataFilter['search']){
            $queryExperience->whereHas('activities', function($query) use($dataFilter){
                $query->where('title','like',  ['%'.$dataFilter['search'].'%']);
            });
        }
        
        if (!empty($dataFilter['price_min'])) {
            $queryExperience->where('from_price', '>=', floatval($dataFilter['price_min']));
        }
        if (!empty($dataFilter['price_max'])) {
            $queryExperience->where('from_price', '<=', floatval($dataFilter['price_max']));
        }    

        if (count($dataFilter['duration']) > 0) {
            $queryExperience->whereHas('translate', function($query) use($dataFilter, $durations){
                foreach ($dataFilter['duration'] as $key => $item) {
                    $d = intval($item) - 1;
                    $interval = $durations[$d];
                    if ($key == 0)
                        $query->whereBetween('duration', [$interval['i'], $interval['f']]);
                    else
                        $query->orWhereBetween('duration', [$interval['i'], $interval['f']]);
                }
            });
        }

        if ($dataFilter['featured']) {
            $queryExperience->whereFeaturedHotel($modelHotel->id);
        }
        return $queryExperience;
    }

    public function getCrosselling ($modelHotel, $cityData) {
        try {
            $lengthAExpFeatured = 12;
            $hotelId = $modelHotel->id;
            $cityName = $modelHotel->zone;

            $modelExperiencesFeatured = Products::activeToShow()
                                    ->select(
                                        'products.id',
                                        'products.status',
                                        'products.destacado',
                                        'products.slug',
                                        'products.recomend',
                                        'products.select',
                                        'products.from_price',
                                        'products.reviews',
                                        \DB::raw("(
                                            SELECT ST_Distance_Sphere(
                                                point(a.metting_point_longitude, a.metting_point_latitude),
                                                point(".$cityData->long.", ".$cityData->lat.")
                                            )
                                            FROM activities a
                                            WHERE a.products_id = products.id
                                            ORDER BY a.id ASC
                                            LIMIT 1
                                        ) AS distance"),
                                    )
                                    ->whereCity($cityName)
                                    ->whereVisibleByHoster($hotelId)
                                    ->orderByFeatured($hotelId)
                                    ->orderByWeighing($modelHotel->id)
                                    ->orderBy('distance', 'asc')
                                    ->limit($lengthAExpFeatured)
                                    ->get();
                            
            return $modelExperiencesFeatured;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getExperiencesBySearch ($modelHotel, $search, $totalLength) {
        try {
            $hotelId = $modelHotel->id;
            $cityName = $modelHotel->zone;
            $experiences = Products::activeToShow()
                ->whereCity($cityName)
                ->whereVisibleByHoster($hotelId)
                ->search($search)
                ->orderByFeatured($hotelId)
                ->limit($totalLength)
                ->get();
            $experiences = $experiences->map(function($item){
                $image = $item->images()->first();
                return [
                    'id' => $item->id,
                    'type' => 'experience',
                    'title' => $item->translate->title,
                    'description' => $item->translate->description,
                    'slug' => $item->slug,
                    'price' => $item->from_price,
                    'city' => $item->translate->city_experince,
                    'image' => $image ? $image->url : null,
                ];
            })->values()->collect();
            return $experiences;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findBySlug ($request) {
        try {
            $modelHotel = $request->attributes->get('hotel');

            $slug = $request->slug ?? null;
            
            $experience = Products::where('slug', $slug)->first();
            
            return $experience;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findInVIatorByShortId ($shortId) {
        try {
            if (!$shortId) return;
            
            $response = Http::withHeaders([
                'exp-api-key' => config('app.key_viator'),
                'Accept-Language' => localeCurrent(),
                'Accept' => 'application/json;version=2.0'
            ])->get(config('app.viator')."/products/$shortId")->collect();
            
            return $response;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findSchedulesInVIator ($shortId) {
        try {
            if (!$shortId) return;
            
            $response = Http::withHeaders([
                'exp-api-key' => config('app.key_viator'),
                'Accept-Language' => localeCurrent(),
                'Accept' => 'application/json;version=2.0'
            ])->get(config('app.viator')."/availability/schedules/$shortId")->collect();
            
            return $response;

        } catch (\Exception $e) {
            return $e;
        }
    }

}
