<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'status',
        'destacado',
        'des_multi',
        'order',
        'name_api',
        'slug',
        'api_short_id',
        'show',
        'recomend',
        'select',
        'from_price',
        'reviews',
        'city_id',
        'location',
        'url',
    ];

    protected $casts = [
        'location' => 'array',
        'reviews' => 'array',
    ];

    public function images()
    {
        return $this->hasMany(Images::class)->orderBy('images.position','ASC');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function translate()
    {
        return $this->hasOne(Activity::class)->where('language', localeCurrent());
    }
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function toggleableHotels(){
        return $this->belongsToMany(hotel::class, 'toggle_products', 'products_id', 'hotel_id')->withPivot('order');;
    }

    public function productFeatured(){
        return $this->hasOne(ServiceFeatured::class, 'product_id');
    }

    public function productHidden(){
        return $this->hasOne(ServiceHiddens::class, 'activities_id');
    }

    public function recomendations(){
        return $this->morphOne(\App\Models\Recomendation::class, 'recommendable')->orderBy('order','asc');
    }

    // SCOPE

    public function scopeActiveToShow($query) 
    {
        $query->where([
            'type' => 'Activities',
            'status' => 1,
            'show' => 1
        ]);
    }

    public function scopeWhereCity($query, $city = null)
    {
        if (!$city) return;

        $modelActivityLanguage = "activities".ucfirst(localeCurrent());

        $query->whereHas('translate', function($query) use($city){
            $query->where('city_experince','like', ['%'.$city.'%']);
        });
    }

    public function scopeWhereDiffLocaleCity($query, $city = null)
    {
        if (!$city) return;

        $modelActivityLanguage = "activities".ucfirst(localeCurrent());

        $query->whereHas('translate', function($query) use($city){
            $query->where('city_experince','!=', $city);
        });
    }

    public function scopeWhereCities($query, $cities = null)
    {
        if (!$cities) return;

        $modelActivityLanguage = "activities".ucfirst(localeCurrent());

        $query->whereHas('translate', function($query) use($cities){
            $query->whereIn('city_experince',$cities);
        });
    }

    public function scopeWhereVisibleByHoster($query, $hotelId = null){
        if ($hotelId) {
            $query->where(function($query)use($hotelId){
                $query->whereHas('toggleableHotels', function($query)use($hotelId){
                    $query->where(function($query)use($hotelId){
                        $query->where('hotel_id', $hotelId);
                    });
                });
            })
            ->whereDoesntHave('productHidden', function($query)use($hotelId){
                $query->where('hotel_id', $hotelId);
            });
        }
    }

    public function scopeOrderByFeatured($query, $hotelId)
    {
        if ($hotelId) {
            $query->withCount('recomendations')
                ->leftJoin('service_featured', function ($join) use ($hotelId) {
                    $join->on('products.id', '=', 'service_featured.product_id')
                    ->where('service_featured.hotel_id', '=', $hotelId);
                })
                ->leftJoin('recomendations', function ($join) use ($hotelId) {
                    $join->on('products.id', '=', 'recomendations.recommendable_id')
                        ->where('recomendations.hotel_id', '=', $hotelId)
                        ->where('recommendable_type', 'App\Models\Products')
                        ->where('recomendations.hotel_id', '=', $hotelId);
                })
                ->orderByRaw('CASE 
                    WHEN recomendations.recommendable_id IS NOT NULL THEN 1
                    WHEN service_featured.product_id IS NOT NULL THEN 2
                    ELSE 3
                END');
        }
    }

    public function scopeSearch ($query, $search)
    {
        if ($search) {
            $query->whereHas('translate', function($query)use($search){
                if ($search) {
                    $query->where('title','like',  ['%'.$search.'%'])
                    ->orWhere('description','like',  ['%'.$search.'%']);
                }
            });
        }
    }

    public function scopeOrderByWeighing($query, $hotelId)
    {
        if ($hotelId) {
            $query->join('toggle_products', 'products.id', '=', 'toggle_products.products_id')
                ->where('toggle_products.hotel_id', $hotelId)
                ->orderBy('toggle_products.order', 'desc');
        }
    }

    public function scopeOrderByASpecificCity($query, $cityName)
    {
        if ($cityName) {
            $query->leftJoin('activities', 'activities.products_id', '=', 'products.id', 'activities.language', '=', 'es')
            ->orderByRaw("CASE WHEN activities.city_experince = '$cityName' THEN 0 ELSE 1 END, activities.city_experince");
        }
    }

}
