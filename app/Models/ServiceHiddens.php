<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceHiddens extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activities_id',
        'hotel_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function activitie()
    // {
    //     return $this->belongsTo('App\Models\Activities', 'activities_id','products_id')
    //             ->where('language', 'es');
    // }

    public function product()
    {
        return $this->belongsTo(Products::class, 'activities_id');
    }
}
