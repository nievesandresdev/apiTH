<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToggleProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'products_id',
        'hotel_id',
        'order',
        'position',
        'position_old',
    ];



    // SCOPED

    public function scopeOrderByDistance($query)
    {
        $query->orderByRaw('distance IS NULL, distance ASC');
    }

}
