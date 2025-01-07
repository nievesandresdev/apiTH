<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'permissions', //json
        'notifications', //json
        'periodicity_chat',
        'periodicity_stay',
        'status',
    ];

    //boot create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($work_position) {
            $work_position->hotel_id = request()->attributes->get('hotel')->id;
        });
    }

    //scope active where status 1
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    //scope by hotel
    public function scopeByHotel($query)
    {
        return $query->where('hotel_id', request()->attributes->get('hotel')->id);
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class, 'work_position_id');
    }


}
