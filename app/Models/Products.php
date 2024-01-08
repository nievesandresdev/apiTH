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

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
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

    public function scopeWhereCity($query, $city = null, $model_activity_language = 'activitiesEs')
    {
        if (!$city) return;
        $query->whereHas($model_activity_language, function($query) use($city){
            $query->where('city_experince','like', ['%'.$city.'%']);
        });
    }

}
