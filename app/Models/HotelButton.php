<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelButton extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'icon',
        'is_visible',
        'order'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}