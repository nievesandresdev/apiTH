<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recomendation extends Model
{
    use HasFactory;

    protected $table = 'recomendations';
    protected $fillable = [
        'user_id',
        'recommendable_id',
        'recommendable_type',
        'message',
        'language',
        'order',
        'hotel_id',
    ];

    public function recommendable() : MorphTo{
        return $this->morphTo();
    }

    // public function place()
    // {
    //     return $this->belongsTo(Places::class, 'recommendable_id');
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function product()
    {
        return $this->belongsTo(Products::class, 'recommendable_id');
    }
}
