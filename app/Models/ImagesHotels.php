<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagesHotels extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'name',
        'url',
        'position'
    ];

    public function hotel()
    {
        return $this->belongsTo(hotel::class);
    }

}
