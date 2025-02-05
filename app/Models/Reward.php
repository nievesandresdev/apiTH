<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount',
        'code',
        'description',
        'type_discount',
        'type_rewards',
        'hotel_id',
        'url',
        'enabled_url',
        'used',
    ];

    protected $casts = [
        'enabled_url' => 'boolean',
        'used' => 'boolean',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reward) {
            if (auth()->check()) {
                $reward->user_id = auth()->id();
            }
        });
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
