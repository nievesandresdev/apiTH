<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stay extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'room',
        'number_guests',
        'companion',
        'language',
        'check_in',
        'check_out',
        'hour_checkin',
        'hour_checkout'
    ];

    public function staySurvey()
    {
        return $this->hasMany(StaySurvey::class, 'stay_id');
    }

    public function hotel()
    {
        return $this->belongsTo(hotel::class);
    }

    public function chat()
    {
        return $this->morphOne(Chat::class, 'chatable');
    }

}
