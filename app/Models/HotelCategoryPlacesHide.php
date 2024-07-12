<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelCategoryPlacesHide extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'categori_places_id',
    ];
}
