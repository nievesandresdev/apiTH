<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityHosterLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'facility_hoster_id',
        'language',
        'schedule',
    ];

}
