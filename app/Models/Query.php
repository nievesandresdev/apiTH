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
    ];
    

    //attrs
    public function getSeenAttribute($value)
    {
        return boolval($value);
    }
}
