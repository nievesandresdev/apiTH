<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'msg_title',
        'msg_text',
        'otas_enabled',
        'request_to',
        'in_stay_activate',
        'in_stay_msg_title',
        'in_stay_msg_text',
        'in_stay_otas_enabled',
    ];

    protected $casts = [
        'msg_title' => 'array',
        'msg_text' => 'array',
        'otas_enabled' => 'array',
        'in_stay_msg_title' => 'array',
        'in_stay_msg_text' => 'array',
        'in_stay_otas_enabled' => 'array',
        'request_to' => 'array'
    ];

    protected $attributes = [
        'msg_title' => '[]',
        'msg_text' => '[]',
        'in_stay_msg_title' => '[]',
        'in_stay_msg_text' => '[]',
        'in_stay_otas_enabled' => '[]',
        'request_to' => '[]'
    ];
    
    public function getInStayActivateAttribute($value)
    {
        return boolval($value);
    }

}
