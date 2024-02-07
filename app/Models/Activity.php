<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'metting_point_latitude',
        'end_point_latitude',
        'metting_point_longitude',
        'end_point_longitude',
        'metting_point_reference',
        'end_point_reference',
        'include_experince',
        'not_include_experince',
        'rules',
        'cancellation_policy',
        'hours_reservation',
        'language_experince',
        'city_experince',
        'duration',
        'products_id',
        'language',

    ];

}
