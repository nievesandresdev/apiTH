<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class Guest extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'phone',
        'lang_web',
        'acronym',
        'color',
        'googleId',
        'avatar',
        'password'
    ];

    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($guest) {
    //         $availableColors = ['5E7A96','5E5E96','967E5E','966B5E','5E968F','5E966A','965E71','965E96'];
    //         $guest->color = $availableColors[array_rand($availableColors)];
    //     });
    // }

    public function stays()
    {
        return $this->belongsToMany(Stay::class)->withPivot('chain_id');
    }


    public function chatMessages()
    {
        return $this->morphMany(ChatMessage::class, 'messageable');
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function notes()
    {
        return $this->hasMany(NoteGuest::class);
    }

    public function stayAccesses()
    {
        return $this->hasMany(StayAccess::class);
    }

    public function queries()
    {
        return $this->hasMany(Query::class);
    }
}
