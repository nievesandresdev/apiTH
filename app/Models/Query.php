<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Query extends Model
{
    use HasFactory;

    protected $fillable = [
        'period',
        'stay_id',
        'guest_id',
        'answered',
        'qualification',
        'comment',
        'attended',
        'visited',
        'response_lang',
        'responded_at',
        'disabled'
    ];
    
    protected $casts = [
        'comment' => 'array',
    ];

    //attrs
    public function getSeenAttribute($value)
    {
        return boolval($value);
    }
}
