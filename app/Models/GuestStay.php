<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class GuestStay extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $table = 'guest_stay';
    
    protected $fillable = [
        'stay_id',
        'guest_id',
        'chain_id'
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }
}   
