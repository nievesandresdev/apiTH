<?php

namespace App\Services\Hoster;

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

    public function queryGetAll ($request, $hotelModel, $dataFilter, $cityModel) {

        $user = $hotelModel->user[0];
        $user_id = $user->id;
        
        $query = Products::activeToShow()
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
                            point(?, ?)
                        )
                        FROM activities a
                        WHERE a.products_id = products.id
                        ORDER BY a.id ASC
                        LIMIT 1
                    ) AS distance"),
                )->addBinding([$cityModel->long, $cityModel->lag], 'select');
    
        if($dataFilter['one_exp_id']){
            $query = $query->where('products.id', $dataFilter['one_exp_id']);
        }else{
            $query = $this->filter($query, $dataFilter, $hotelModel, $cityModel);
        }
        return $query;
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

    public function filter ($query, $dataFilter, $hotelModel, $cityModel) {
        $user = $hotelModel->user[0];

        if($dataFilter['all_cities']){

        }else{
            $query->whereHas('translation', function($query) use($dataFilter){

                $query->where('city_experince', $dataFilter['city']);
            });
        }
        $query->whereHas('translation', function($query) use($dataFilter){   
            $query->whereNotNull('metting_point_longitude')
            ->whereNotNull('metting_point_latitude');
        });

        if (!empty($dataFilter['search'])) {
            $query->whereHas('translation', function($query) use($dataFilter){
                $query->where('title','like', ['%'.$dataFilter['search'].'%'])
                    ->orWhere('description','like', ['%'.$dataFilter['search'].'%']);
            });
        }

        // 1 hora [0, 1]
        // 1 y 3 horas [1.1, 3.99]
        // Medio dia [4, 7.9]
        // 1 dia [8]
        if (count($dataFilter['duration']) > 0) {
            $query->whereHas('translation', function($query) use($dataFilter){
                foreach ($dataFilter['duration'] as $key => $item) {
                    $durations =  [['i'=>0,'f'=>60],['i'=>61,'f'=>180],['i'=>181,'f'=>480],['i'=>481,'f'=>100000]];
                    $d = intval($item) - 1;
                    $interval = $durations[$d];
                    if ($key == 0){
                        $query->whereBetween('duration', [$interval['i'], $interval['f']]);
                        if ($interval['i'] == 0) {
                            $query->orWhereNull('duration');
                        }
                    }else{
                        $query->orWhereBetween('duration', [$interval['i'], $interval['f']]);
                        if ($interval['i'] == 0) {
                            $query->orWhereNull('duration');
                        }
                    }
                }
            });
        }

        if($dataFilter['visibility'] == 'hidden'){
            $query->whereAddExpInHoster($hotelModel->id);
        }
        if($dataFilter['visibility'] == 'visible'){
            $query->whereVisibleByHoster($hotelModel->id);
        }
        if($dataFilter['visibility'] == 'recommendated'){
            $query->whereFeaturedHotel($hotelModel->id)
            ->whereVisibleByHoster($hotelModel->id);
        }
        if(!$dataFilter['visibility']){
            $query->withVisibilityForProduct($hotelModel->id);
        }
        $query->orderByFeatured($hotelModel->ID);
        $query->orderByWeighing($hotelModel->id);
        $query->orderBy('distance','ASC');
        
        return $query;
    }

}