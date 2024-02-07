<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceTranslate extends Model
{
    use HasFactory;

    protected $casts = [
        'tokens' => 'array',
    ];

    protected $fillable = [
        'title',
        'description',
        'datos_interes',
        'language',
        'places_id',
        'tokens',
        'translate',
        'url_id',
        'type_cuisine',
        'diet_specifications',
        'label',
    ];

}
