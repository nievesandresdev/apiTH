<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'lang_web',
        'acronym',
        'color'
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
        return $this->belongsToMany(Stay::class);
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
}
