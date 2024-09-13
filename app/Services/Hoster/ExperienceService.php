<?php

namespace App\Services\Hoster;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

use App\Models\Products;
use App\Models\Recomendation;
use App\Models\ServiceHiddens;
use App\Models\ToggleProduct;
use App\Models\User;
use App\Models\ServiceFeatured;


use App\Http\Resources\FacilityResource;

class ExperienceService {

    function __construct()
    {

    }

    public function getNumbersByFilters ($request, $modelHotel, $dataFilter, $cityModel) {
        
        $countByFilterDuration = [
            '1' => ['name' =>'Hasta una hora', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'duration'=>['1']], $cityModel)->count() ],
            '2' => ['name' =>'Entre 1 y 3 horas', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'duration'=>['2']], $cityModel)->count() ],
            '3' => ['name' =>'Medio día', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'duration'=>['3']], $cityModel)->count() ],
            '4' => ['name' =>'Día completo', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'duration'=>['4']], $cityModel)->count()]
        ];

        $countByFilterScore = [
            '1' => ['name' =>'1 estrella', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'score'=>['1']], $cityModel)->count() ],
            '2' => ['name' =>'2 estrellas', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'score'=>['2']], $cityModel)->count() ],
            '3' => ['name' =>'3 estrellas', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'score'=>['3']], $cityModel)->count() ],
            '4' => ['name' =>'4 estrellas', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'score'=>['4']], $cityModel)->count() ],
            '5' => ['name' =>'5 estrellas', 'count' => $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'score'=>['5']], $cityModel)->count()]
        ];

        $freeCancelation = $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'free_cancelation'=> true], $cityModel)->count();

        $city = $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'all_cities'=> false], $cityModel)->count();

        $allCities = $this->queryGetAll($request, $modelHotel, [...$dataFilter, 'all_cities'=> true], $cityModel)->count();
                        
        return [
            'duration' => $countByFilterDuration,
            'score' => $countByFilterScore,
            'freeCancelation' => $freeCancelation,
            'city' => $city,
            'allCities' => $allCities,
        ];
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

    public function filter ($query, $dataFilter, $hotelModel, $cityModel) {
        
        $user = $hotelModel->user[0];

        if($dataFilter['all_cities']){
        }else{
            $query->whereHas('translation', function($query) use($dataFilter){
                $query->where('city_experince', $dataFilter['city']);
            });
        }
        
        // $query->whereHas('translation', function($query) use($dataFilter){   
        //     $query->whereNotNull('metting_point_longitude')
        //     ->whereNotNull('metting_point_latitude');
        // });

        if (!empty($dataFilter['search'])) {
            $query->whereHas('translation', function($query) use($dataFilter){
                $query->where('title','like', ['%'.$dataFilter['search'].'%'])
                    ->orWhere('description','like', ['%'.$dataFilter['search'].'%']);
            });
        }

        if (!empty($dataFilter['price_min'])) {
            $query->where('from_price', '>=', floatval($dataFilter['price_min']));
        }
        if (!empty($dataFilter['price_max'])) {
            $query->where('from_price', '<=', floatval($dataFilter['price_max']));
        } 

        if (!empty($dataFilter['free_cancelation'])) {
            $query->whereHas('translation', function($query) use($dataFilter){
                $query->where(['cancellation_policy' => 'STANDARD']);
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

        if (count($dataFilter['score']) > 0) {
            foreach ($dataFilter['score'] as $key => $item) {
                $durations =  [['i'=>0,'f'=>1.99],['i'=>2,'f'=>2.99],['i'=>3,'f'=>3.99],['i'=>4,'f'=>4.99],['i'=>5,'f'=>5]];
                $d = intval($item) - 1;
                $interval = $durations[$d];
                if ($key == 0){
                    $query->whereRaw("JSON_EXTRACT(reviews, '$.combined_average_rating') BETWEEN ? AND ?", [$interval['i'], $interval['f']]);
                }else{
                    $query->orWhereRaw("JSON_EXTRACT(reviews, '$.combined_average_rating') BETWEEN ? AND ?", [$interval['i'], $interval['f']]);
                }
                if ($interval['i'] == 0) {
                    $query->orWhereNull('reviews');
                }
            }
        }

        $query->whereDoesntHave('productHidden', function($query)use($hotelModel){
            $query->where(['hotel_id' => $hotelModel->id, 'is_deleted' => 1]);
        });

        if($dataFilter['visibility'] == 'hidden'){
            $query->whereAddExpInHoster($hotelModel->id);
        }
        if($dataFilter['visibility'] == 'visible'){
            $query->whereVisibleByHoster($hotelModel->id);
        }
        if($dataFilter['visibility'] == 'recommendated'){
            $query->whereFeaturedHotel($hotelModel->id);
            // ->whereVisibleByHoster($hotelModel->id);
        }
        if(!$dataFilter['visibility']){
            $query->withVisibilityForProduct($hotelModel->id);
        }

        $query->orderByPosition($hotelModel->id);
        $query->orderByFeatured($hotelModel->id);
        $query->orderByWeighing($hotelModel->id);
        $query->orderBy('distance','ASC');
        
        return $query;
    }

    public function featuredByHoster ($featuredBool, $modelHotel, $modelProduct) {
        $modelProductFeatured = ServiceFeatured::where('product_id',$modelProduct->id)
                                        ->where('hotel_id',$modelHotel->id)
                                        ->first();
        if(!$featuredBool && $modelProductFeatured){
            $modelProductFeatured->delete();
        }
        if($featuredBool && !$modelProductFeatured){
            $modelProductFeatured = new ServiceFeatured();
            $modelProductFeatured->product_id = $modelProduct->id;
            $modelProductFeatured->hotel_id = $modelHotel->id;
            $modelProductFeatured->user_id = $modelHotel->user[0]->id;
            $modelProductFeatured->save();
        }
    }

    public function assignFirstPosition ($hotelModel, $productModel) {
        $toggleProductModelFirstOld = ToggleProduct::where([
            'hotel_id' => $hotelModel->id,
            'position' => 0,
        ])->first();
        return $toggleProductModelFirstOld;
        if ($toggleProductModelFirstOld) {
            $toggleProductModelFirstOld->update(['position' => 0.5]);
        }
        $modelToggleProductFirstNew = ToggleProduct::updateOrCreate([
            'hotel_id' => $hotelModel->id,
            'Products_id' => $productModel->id,
        ], [
            'position' => 0,
        ]);
        return $modelToggleProductFirstNew;
    }

    public function deletePositionCurrent ($hotelModel, $productModel) {
        $modelToggleProduct = ToggleProduct::updateOrCreate([
            'hotel_id' => $hotelModel->id,
            'products_id' => $productModel->id,
        ], [
            'position' => null,
        ]);
    }

    public function updateVisibility ($request, $hotelModel, $productModel) {
        $productId = $productModel->id;
        $isVisible = $request->visivility;
        $userId = $hotelModel->user[0]->id;
        if(!$isVisible){
            $toggleProductModel = ToggleProduct::where([
                'hotel_id' => $hotelModel->id,
                'products_id' => $productId,
            ])->first();
            if ($toggleProductModel) {
                $toggleProductModel->delete();
            }
            $productHiddenModel = ServiceHiddens::updateOrCreate([
                'hotel_id' => $hotelModel->id,
                'activities_id' => $productId,
            ], [
                'hotel_id' => $hotelModel->id,
                'activities_id' => $productId,
                'user_id' => $userId,
                'is_deleted' => $request->is_deleted ?? false,
            ]);
        }else{
            ServiceHiddens::where([
                'hotel_id' => $hotelModel->id,
                'activities_id' => $productId,
            ])->delete();
            $modelToggleProduct = ToggleProduct::updateOrCreate([
                'hotel_id' => $hotelModel->id,
                'products_id' => $productId,
            ], [
                'hotel_id' => $hotelModel->id,
                'products_id' => $productId,
                'position' => 0,
                'order' => 1,
            ]);
        }
    }

    public function syncPosition ($request, $cityModel, $hotelModel, $update_position_old = true) {
        $hotelId = $hotelModel->id;
        $productsQuery = Products::activeToShow()
            ->select(
                'products.id',
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
            )->addBinding([$cityModel->long, $cityModel->lag], 'select')
            ->whereVisibleByHoster($hotelModel->id)
            ->orderByPosition($hotelModel->id)
            ->orderByFeatured($hotelModel->id)
            ->orderByWeighing($hotelModel->id)
            ->orderBy('distance','ASC')
            ->whereCity($cityModel->name);

        $position = 0;

        $productsQuery->chunk(100, function ($products) use (&$position, $hotelModel, $update_position_old) {
            foreach ($products as $product) {
                // $toggleProductModel = ToggleProduct::where([
                //     'hotel_id' => $hotelModel->id,
                //     'products_id' => $product->id,
                // ])->first();
                // if ($toggleProductModel) {
                //     $toggleProductModel->position = $position;
                //     if ($update_position_old) {
                //         $toggleProductModel->position_old = $position;
                //     }
                //     $toggleProductModel->save();
                // }
                //
                $product->toggleableHotels()->where('hotel_id', $hotelModel->id)->update(['position' => $position]);
                $position++;
            }
        });
        return true;
    }

    public function resetPosition ($request, $cityModel, $hotelModel) {
        $hotelId = $hotelModel->id;
        $productsQuery = Products::activeToShow()
            ->whereVisibleByHoster($hotelModel->id)
            ->whereCity($cityModel->name);

        $productsQuery->chunk(100, function ($products) use (&$position, $hotelModel) {
            foreach ($products as $product) {
                $toggleProductModel = ToggleProduct::where([
                    'hotel_id' => $hotelModel->id,
                    'products_id' => $product->id,
                ])->first();
                if ($toggleProductModel) {
                    $toggleProductModel->update(['position' => null]);
                }
            }
        });

    }

    public function updatePositionBulk ($position, $hotelModel) {
        $toggleProductsIdsOrded = collect($position??[]);
        foreach ($toggleProductsIdsOrded as $position => $id) {
            ToggleProduct::where(['id' => $id])->update(['position' => $position]);
        }
    }
    public function updatePosition ($hotelModel, $productModel) {
        $toggleProductModel = ToggleProduct::where(['products_id' => $productModel->id, 'hotel_id' => $hotelModel->id])->first();
        if ($toggleProductModel && $toggleProductModel->position_old) {
            var_dump('entro');
            $toggleProductModel->update(['position' => $toggleProductModel->position_old, 'position_old' => $toggleProductModel->position_old]);
        }
    }
    public function getPositionFirtNonRecommendated ($hotelModel, $cityModel) {
        $newPosition = null;
        $productsQuery = Products::activeToShow()
            ->select(
                'products.id',
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
            )->addBinding([$cityModel->long, $cityModel->lag], 'select')
            ->whereVisibleByHoster($hotelModel->id)
            ->orderByPosition($hotelModel->id)
            ->orderByFeatured($hotelModel->id)
            ->orderByWeighing($hotelModel->id)
            ->orderBy('distance','ASC')
            ->whereCity($cityModel->name);

        $productsQuery->chunk(100, function ($products) use (&$newPosition, $hotelModel) {
            foreach ($products as $productModal) {
                $productFeatured = $productModal->productFeatured()->where('hotel_id', $hotelModel->id)->first();
                
                if (!$productFeatured) {
                    $toggleProduct = ToggleProduct::where(['hotel_id' => $hotelModel->id, 'products_id' => $productModal->id])->first();
                    $newPosition = $toggleProduct->position;
                    // var_dump($newPosition);
                    return false;
                }
            }
        });

        return $newPosition;
        
    }
    public function getPositionOld ($order, $hotelModel, $cityModel) {

        $order = (float) $order;
        $productsQuery = Products::activeToShow()
            ->select(
                'products.id',
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
            )->addBinding([$cityModel->long, $cityModel->lag], 'select')
            ->whereVisibleByHoster($hotelModel->id)
            ->orderByPosition($hotelModel->id)
            ->orderByFeatured($hotelModel->id)
            ->orderByWeighing($hotelModel->id)
            // ->orderBy('distance','ASC')
            ->whereCity($cityModel->name);

        $shouldContinue = true;

        $newOrder = null;

        $productsQuery->chunk(100, function ($products) use (&$newOrder, $order, $hotelModel) {
            foreach ($products as $productModal) {
                $toggleProduct = ToggleProduct::where(['hotel_id' => $hotelModel->id, 'products_id' => $productModal->id])->first();
                $orderProductCurrent = (float) $toggleProduct->order;
                if ($orderProductCurrent < $order) {
                    return false;
                }
                $newOrder = $orderProductCurrent;
            }
        });

        return $newOrder;
        
    }

    public function findRecommendation ($hotelModel, $productModel) {
        $recomendationModel = Recomendation::where('recommendable_id',$productModel->id)
        ->where('recommendable_type','App\Models\Products')
        ->where('hotel_id',$hotelModel->id)
        ->first();
        return $recomendationModel;
    }

    public function updateRecommendation ($message, $recomendationModel, $hotelModel, $productModel) {
        if($recomendationModel){
            $recomendationModel->message = $message;
            $recomendationModel->save();
        }else{
            $recomendationModel = Recomendation::create([
                'message' => $message,
                'recommendable_id' => $productModel->id,
                'hotel_id' => $hotelModel->id,
                'recommendable_type' => 'App\Models\Products',
                'order' => 1,
            ]);
        }
        return $recomendationModel;
    }

    public function updateTranslation ($model, $translation) {
        try{
            if (!$translation) return;
            $translation = collect($translation);
            $translation = $translation->mapWithKeys(function($value, $lg){
                $message_translated = !isset($value->recommendation) || !$value->recommendation || ($value->recommendation == 'null') ? null : $value->recommendation;
                return [$lg => $message_translated];
            });

            $model->update(['translate' => json_encode($translation, true)]);

        } catch (\Exception $e) {
            $message = $e->getMessage();
            Log::error('updateTranslation:'.' '. $message);
            return $e;
        }

    }


}