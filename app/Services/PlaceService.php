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
use Illuminate\Support\Facades\Storage;

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

            $placesCountOtherCities = clone $queryPlace;
            $countOtherCities = $placesCountOtherCities->where('city_places', '!=', $modelHotel->zone)->count();

            $collectionPlaces = $queryPlace->orderByFeatured($modelHotel->id)
                ->orderByWeighing($modelHotel->id)
                ->paginate(20)
                ->appends(request()->except('page'));
                
            return ['places' => $collectionPlaces, 'countOtherCities' => $countOtherCities];

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

        $queryPlace = Places::activeToShow();
        
        $queryPlace->whereVisibleByHoster($modelHotel->id);
        if(isset($dataFilter['cities'])){
            $queryPlace->whereIn('city_places', $dataFilter['cities']);
        }else{
            $queryPlace->whereCity($dataFilter['city']);    
        }

        // !empty($data_filter['typecuisine']) ? $data->whereRaw("type_cuisine regexp '$typecuisine'") : '';

        if(!empty($dataFilter['typeplace'])) $queryPlace->where('type_places_id', $dataFilter['typeplace']);

        if(!empty($dataFilter['categoriplace'])) $queryPlace->where('categori_places_id', $dataFilter['categoriplace']);
       
        if($dataFilter['search']){
            $queryPlace->whereHas('translatePlace', function($q) use ($dataFilter){
                $q->where('title', 'like',  ['%' . $dataFilter['search'] . '%']);
            });
        }

        if(isset($dataFilter['cities'])){

            $near_cities = $dataFilter['cities'] ?? [];
            $ordered_names = implode(",", array_map(function($city) {
                $city = str_replace("'", "\\'", $city); //Escapa apóstrofos
                return "'{$city}'";            
            }, $near_cities));

            $queryPlace->orderByRaw("FIELD(city_places, {$ordered_names})");
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

    public function getPlacesBySearch($modelHotel, $search, $totalLength, $typePlace, $categoryPlace) {
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
                ->when($typePlace, function ($query) use ($typePlace) {
                    return $query->where('type_places_id', $typePlace);
                })
                ->when($categoryPlace, function ($query) use ($categoryPlace) {
                    return $query->where('categori_places_id', $categoryPlace);
                })
                ->orderByFeatured($hotelId)
                ->limit($totalLength)->get();
                
            $places = $places->map(function($item){
                $image = $item->images()->first();
                return [
                    'id' => $item->id,
                    'type' => 'place',
                    'type_places_id' => $item->type_places_id,
                    'categori_places_id' => $item->categori_places_id,
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


    public function getRatingCountsPlaces ($request, $modelHotel) {
        try {
            
            $counts = [];
            $params = [
                'city' => $request->city,
                'typeplace' => $request->typeplace,
                'categoriplace' => $request->categoriplace,
                'search'=>null,
                'points'=> [],
                'featured'=>false
            ];
            for ($i=1; $i < 6 ; $i++) { 
                $params['points'] = [];
                $params['points'] = [$i];
                $queryPlace = $this->filter($params, $modelHotel);
                $counts[$i] = $queryPlace->count();
            }
            
            return $counts;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findById ($request) {
        try {
            return Places::findOrFail($request->id);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getDataReviews($id){
        $data = [
            "ammountTotal" => 0,
            "ammount" => [0,0,0,0,0,0],  
            "percentaje" => [0,0,0,0,0,0],
            "reviews" => []
        ];
        $reviews = [];
        
        try {
            ini_set('memory_limit', '1024M');
            $place = Places::find($id);
            $fileName = str_replace('.csv', '', $place->name_file);
            // return $place->name_file;
            if (Storage::disk('public')->exists('reviews_places/'.$fileName.'_reviews.json')) {
                $jsonData = Storage::disk('public')->get('reviews_places/'.$fileName.'_reviews.json');
                $allReviews = json_decode($jsonData);
            
                // Filtrar las reseñas donde 'url_id' es igual a $place->url
                $filteredReviews = [];
                foreach ($allReviews as $review) {
                    if (isset($review->url_id) && $review->url_id == $place->url) {
                        $filteredReviews[] = $review;
                        intval($review->general_rating) >= 1 && intval($review->general_rating) < 2 ? $data['ammount'][1]++ : '';
                        intval($review->general_rating) >= 2 && intval($review->general_rating) < 3 ? $data['ammount'][2]++ : '';
                        intval($review->general_rating) >= 3 && intval($review->general_rating) < 4 ? $data['ammount'][3]++ : '';
                        intval($review->general_rating) >= 4 && intval($review->general_rating) < 5 ? $data['ammount'][4]++ : '';
                        intval($review->general_rating) == 5 ? $data['ammount'][5]++ : '';
                    }
                }
                $reviews = $filteredReviews;
                // Calcular el puntaje de las reseñas
                $ammount_reviews = count($filteredReviews);
                $data['percentaje'][1] = round(floatval($data['ammount'][1] / $ammount_reviews), 2);
                $data['percentaje'][2] = round(floatval($data['ammount'][2] / $ammount_reviews), 2);
                $data['percentaje'][3] = round(floatval($data['ammount'][3] / $ammount_reviews), 2);
                $data['percentaje'][4] = round(floatval($data['ammount'][4] / $ammount_reviews), 2);
                $data['percentaje'][5] = round(floatval($data['ammount'][5] / $ammount_reviews), 2);
            }
            $data['ammountTotal'] = $ammount_reviews;
            $data['reviews'] = $reviews;
            return $data;
        } catch (\Exception $e) {
            // Aquí manejas la excepción
            return response()->json([
                'error' => 'Ocurrió un error al obtener las reseñas: ' . $e->getMessage()
            ], 500); // Puedes cambiar el código de estado HTTP según corresponda
        }

    }

    public function getReviewsByRating($request){
        try {
            $place = Places::find($request->id);
            
            $reviews = [];
            $fileName = str_replace('.csv', '', $place->name_file);
            // return $place->name_file;
            if (Storage::disk('public')->exists('reviews_places/'.$fileName.'_reviews.json')) {
                $jsonData = Storage::disk('public')->get('reviews_places/'.$fileName.'_reviews.json');
                $allReviews = json_decode($jsonData);
            
                // Filtrar las reseñas donde 'url_id' es igual a $place->url
                $filteredReviews = [];
                foreach ($allReviews as $review) {
                    $rating = intval($review->general_rating);
                    $search = intval($request->search);
                    if(
                        $search < 6 && $rating >= $search && $rating >= $search && $rating < ($search+1) &&
                        isset($review->url_id) && $review->url_id == $place->url
                    ){
                        $filteredReviews[] = $review;
                    }
                    if ($search >= 6 && isset($review->url_id) && $review->url_id == $place->url) {
                        $filteredReviews[] = $review;
                    }
                }
                $reviews = $filteredReviews;
                
            }

            return $reviews;
        } catch (\Exception $e) {
            // Aquí manejas la excepción
            return response()->json([
                'error' => 'Ocurrió un error al obtener las reseñas por rating: ' . $e->getMessage()
            ], 500); // Puedes cambiar el código de estado HTTP según corresponda
        }
    }
}