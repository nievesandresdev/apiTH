<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'not_available_msg',
        'not_available_show',
        'first_available_msg',
        'first_available_show',
        'second_available_msg',
        'second_available_show',
        'three_available_msg',
        'three_available_show',
        'name',
        'show_guest',
    ];

    protected $casts = [
        'not_available_msg' => 'array',
        'first_available_msg' => 'array',
        'second_available_msg' => 'array',
        'three_available_msg' => 'array',
    ];
    //relations 
    public function languages()
    {
        return $this->belongsToMany(Language::class);
    }
    //attrs
    public function getShowGuestAttribute($value)
    {
        return boolval($value);
    }

    public function getNotAvailableShowAttribute($value)
    {
        return boolval($value);
    }

    public function getFirstAvailableShowAttribute($value)
    {
        return boolval($value);
    }

    public function getSecondAvailableShowAttribute($value)
    {
        return boolval($value);
    }

    public function getThreeAvailableShowAttribute($value)
    {
        return boolval($value);
    }
}
