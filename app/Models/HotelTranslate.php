<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelTranslate extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'zone',
        'name',
        'language',
        'description',
    ];

}
