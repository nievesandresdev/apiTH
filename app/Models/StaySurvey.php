<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaySurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'stay_id',
        'guest_id',
        'score',
        'description',
        'steps',
    ];
}
