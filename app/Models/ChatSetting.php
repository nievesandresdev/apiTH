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
        'email_notify_new_message_to',
        'email_notify_pending_chat_to',
        'email_notify_not_answered_chat_to',
    ];

    protected $casts = [
        'not_available_msg' => 'array',
        'first_available_msg' => 'array',
        'second_available_msg' => 'array',
        'three_available_msg' => 'array',
        'email_notify_new_message_to' => 'array',
        'email_notify_pending_chat_to' => 'array',
        'email_notify_not_answered_chat_to' => 'array',
    ];
    //relations 
    public function languages()
    {
        return $this->belongsToMany(Language::class)->withPivot('id','son_id');
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
