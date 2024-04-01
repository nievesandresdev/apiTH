<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelOta extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'enum_ota',
        'review',
        'hotel_id',
        'detail_run',
        'first_run_date',
        'last_run_date',
        'first_run_reviews',
        'last_run_review',
        'total_current_reviews',
        'date_last_read_reviews',
        'date_last_read_ota'
    ];

    protected $casts = [
        'review' => 'array',
        'detail_run' => 'array',
    ];
}
