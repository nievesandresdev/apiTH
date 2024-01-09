<?php

namespace App\Models;

use Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Places extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_places',
        'type_places_id',
        'categori_places_id',
        'address',
        'order',
        'show',
        'language',
        'metting_point_latitude',
        'metting_point_longitude',
        'status',
        'featured_multi',
        'featured',
        'recommendation_admin',
        'selection_admin',
        'range_prices',
        'range_numeric_prices',
        'url_menu',
        'web_link',
        'phone_wheretoeat',
        'email_wheretoeat',
        'type_cuisine',
        'diet_specifications',
        'import',
        'ta_id',
        'name_file',
        'url',
        'punctuation',
        'num_reviews',
        'num_stars',
        'reviews_dist',
        'translate',
        'type',
    ];

    public function images()
    {
        return $this->hasMany(PlaceImages::class, 'places_id');
    }
    

    public function typePlaces()
    {
        return $this->belongsTo(TypePlaces::class);
    }
    public function categoriPlaces()
    {
        return $this->belongsTo(CategoriPlaces::class);
    }

    public function translatePlace()
    {
        return $this->hasOne('App\Models\PlaceTranslate')->where('language', localeCurrent());
    }

    public function toggleableHotels(){
        return $this->belongsToMany(hotel::class, 'toggle_places', 'places_id', 'hotel_id')->withPivot('order');
    }

    public function placeHidden(){
        return $this->hasOne(\App\Models\PlaceHidden::class, 'place_id');
    }

    public function placeFeatured(){
        return $this->hasOne(\App\Models\PlaceFeatured::class, 'place_id');
    }

    public function recomendations(){
        return $this->morphOne(\App\Models\Recomendation::class, 'recommendable');
    }

    // SCOPE

    public function scopeActiveToShow($query)
    {
        $query->where(['status' => 1, 'show' => 1]);
    }

    public function scopeWhereCity($query, $cityName)
    {
        if ($cityName){
            $query->where('city_places', 'like', ['%'.$cityName.'%']);
        }
    }

    public function scopeWhereVisibleByHoster($query, $hotelId = null){
        if ($hotelId) {
            $query->where(function($query)use($hotelId){
                $query->whereHas('toggleableHotels', function($query)use($hotelId){
                    $query->where('hotel_id', $hotelId);
                });
            })
            ->whereDoesntHave('placeHidden', function($query)use($hotelId){
                $query->where('hotel_id', $hotelId);
            });
        }
    }

    public function scopeOrderByFeatured($query, $hotelId)
    {
        if ($hotelId) {
            $query->withCount('recomendations')
                ->leftJoin('place_featured', function ($join) use ($hotelId) {
                    $join->on('places.id', '=', 'place_featured.place_id')
                    ->where('place_featured.hotel_id', '=', $hotelId);
                })
                ->leftJoin('recomendations', function ($join) use ($hotelId) {
                    $join->on('places.id', '=', 'recomendations.recommendable_id')
                        ->where('recomendations.hotel_id', '=', $hotelId)
                        ->where('recommendable_type', 'App\Models\Places')
                        ->where('recomendations.hotel_id', '=', $hotelId);
                })
                ->orderByRaw('CASE
                    WHEN recomendations.recommendable_id IS NOT NULL THEN 1
                    WHEN place_featured.place_id IS NOT NULL THEN 2
                    ELSE 3
                END');
        }
    }

    public function scopeWhereTypePlaceByName($query, $typePlaceName, $typeQuery = 1)
    {
        if ($typeQuery == 1 && !empty($typePlaceName)) {
            $query->whereHas('TypePlaces', function($query)use($typePlaceName){
                $query->where('name', 'like', ['%' . $typePlaceName . '%']);
            });
        }
        if ($typeQuery == 0 && !empty($typePlaceName)) {
            $query->whereDoesntHave('TypePlaces', function($query)use($typePlaceName){
                $query->where('name', 'like', ['%' . $typePlaceName . '%']);
            });
        }
    }

}
