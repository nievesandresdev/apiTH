<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardStay extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'hotel_id',
        'stay_id',
        'guest_id',
        'reward_id',
    ];

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

}
