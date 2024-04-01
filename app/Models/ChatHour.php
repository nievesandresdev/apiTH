<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatHour extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'day',
        'horary',
        'active',
    ];

    protected $casts = [
        'horary' => 'array',
    ];

    public function getActiveAttribute($value)
    {
        return boolval($value);
    }
}
