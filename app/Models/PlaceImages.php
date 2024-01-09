<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'places_id',
        'name',
        'url',
        'position',
        'api',
        'image',
        'url_id',
    ];

    public function places()
    {
        return $this->belongsTo(Places::class);
    }
}
