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
        'request_to'
    ];

    protected $casts = [
        'msg_title' => 'array',
        'msg_text' => 'array',
        'otas_enabled' => 'array'
    ];
    

}
