<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'type',
        'welcome_email',
        'pre_checkin_email',
        'post_checkin_email',
        'checkout_email',
        'pre_checkout_email',
        'new_chat_email',
        'referent_email',
    ];

    protected $casts = [
        'welcome_email' => 'boolean',
        'pre_checkin_email' => 'boolean',
        'post_checkin_email' => 'boolean',
        'checkout_email' => 'boolean',
        'pre_checkout_email' => 'boolean',
        'new_chat_email' => 'boolean',
        'referent_email' => 'boolean',
    ];


    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    //scopeType
    public function scopeType($query, $type)
    {
        if($type){
            return $query->where('type', $type);
        }

        return $query;
    }
}
