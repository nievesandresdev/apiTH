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
use App\Models\CategoriPlaces;
use App\Models\TypePlaces;

use App\Http\Resources\FacilityResource;

class PlaceService {

    function __construct()
    {

    }

    public function getAll ($request, $dataFilter, $modelHotel) {
        try {

            if (!$dataFilter['typeplace']) {
                $dataFilter['typeplace'] = TypePlaces::where(['show' => 1, 'active' => 1])->first()->id;
            }

            if (!$dataFilter['categoriplace']) {
                $dataFilter['categoriplace'] = CategoriPlaces::where(['show' => 1, 'active' => 1, 'type_places_id' => $dataFilter['typeplace']])->first()->id;
            }
            
            $queryPlace = $this->filter($dataFilter, $modelHotel);

            $collectionPlaces = $queryPlace->orderByFeatured($modelHotel->id)
                ->orderByWeighing($modelHotel->id)
                ->paginate(20)
                ->appends(request()->except('page'));
                            
            return $collectionPlaces;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getCategoriesByType ($request, $dataFilter, $modelHotel) {
        try {

            $typeplace = $dataFilter['typeplace'];
            if (!$typeplace) {
                $typeplace = TypePlaces::where(['show' => 1, 'active' => 1])->first()->id;
            }

            // $categoriplace = $dataFilter['categoriplace'];
            // if (!$categoriplace) {
            //     $categoriplace = CategoriPlaces::where(['show' => 1, 'active' => 1])->first()->id;
            // }

            $categoriplaces = CategoriPlaces::where(['show' => 1, 'active' => 1]);
            if (!$dataFilter['all']) {
                $categoriplaces->where('type_places_id', $typeplace);
            }
            $categoriplaces = $categoriplaces->get();
            
            $categoriplaces = $categoriplaces->map(function($q)use($dataFilter, $modelHotel){
                $params = [
                    'city' => $dataFilter['city'],
                    'typeplace' => $q->type_places_id,
                    'categoriplace' => $q->id,
                    'search'=>null,
                    'points'=>[],
                    'featured'=>false
                ];
                $numbersPlaces = $this->filter($params, $modelHotel)->count();
                return [
                    "categori_places_id" => $q->id,
                    "city_places" => $dataFilter['city'],
                    "type_places_id" => $q->type_places_id,
                    "id" => $q->id,
                    "name" => $q->name,
                    "count_places" => $numbersPlaces,
                ];
            })->collect();

            $orderNategoryNames = [
                'Monumentos' => 1, 'Museos' => 2, 'Zonas verdes' => 3,
                'Restaurantes' => 4, 'Cafeterías y postres' => 5, 'Heladerías' => 6,
                'Vida nocturna' => 7, 'Compras' => 8, 'Otros' => 9, 'Copas' => 10
            ];

            $categoriplaces = $categoriplaces->sortBy(function($place) use ($orderNategoryNames) {
                return $orderNategoryNames[$place['name']];
            })->values();

            return $categoriplaces;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getTypePlaces ($request, $modelHotel) {
        try {
            
            $typePLacesCollection = TypePlaces::where('active', 1)
            ->where('show', 1)
            ->with(['CategoriPlaces' => function ($query) {
                $query->where('active', 1)->where('show', 1)->orderBy('order');
            }])
            ->get();

            return $typePLacesCollection;

        } catch (\Exception $e) {
            return $e;
        }
    }

    private function filter($dataFilter, $modelHotel){

        $queryPlace = Places::activeToShow()
        ->whereCity($dataFilter['city'])
        ->whereVisibleByHoster($modelHotel->id);

        // !empty($data_filter['typecuisine']) ? $data->whereRaw("type_cuisine regexp '$typecuisine'") : '';

        if(!empty($dataFilter['typeplace'])) $queryPlace->where('type_places_id', $dataFilter['typeplace']);

        if(!empty($dataFilter['categoriplace'])) $queryPlace->where('categori_places_id', $dataFilter['categoriplace']);
       
        if($dataFilter['search']){
            $queryPlace->whereHas('translatePlace', function($q) use ($dataFilter){
                $q->where('title', 'like',  ['%' . $dataFilter['search'] . '%']);
            });
        }


        
        $rangeScore =  [['i'=>1,'f'=>1.99],['i'=>2,'f'=>2.99],['i'=>3,'f'=>3.99],['i'=>4,'f'=> 4.99],['i'=>5,'f'=> 6]];

        $points = $dataFilter['points'] ?? [];
        if (count($points) > 0 ) {
            foreach ($points as $key => $item) {
                $d = intval($item) - 1;
                $interval = $rangeScore[$d];
                if ($key == 0){
                    $queryPlace->whereRaw("CONVERT(REPLACE(num_stars, ',', '.'), DECIMAL(10,1)) BETWEEN ? AND ?", [$interval['i'], $interval['f']]);
                }else{
                    $queryPlace->orWhereRaw("CONVERT(REPLACE(num_stars, ',', '.'), DECIMAL(10,1)) BETWEEN ? AND ?", [$interval['i'], $interval['f']]);
                }
            }
        }

        if ($dataFilter['featured']) {
            $queryPlace->whereFeaturedByHotel($modelHotel->id);
        }

        return $queryPlace;
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