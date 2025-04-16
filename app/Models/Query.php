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
        'disabled' => 'boolean'
    ];

    //attrs
    public function getAnsweredAttribute($value)
    {
        return boolval($value);
    }

    public function getSeenAttribute($value)
    {
        return boolval($value);
    }

    // relations
    public function histories()
    {
        return $this->hasMany(QueryHistory::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
