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
}
