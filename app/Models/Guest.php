<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class Guest extends Authenticatable
{
    use HasApiTokens, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'phone',
        'birthdate',
        'lang_web',
        'acronym',
        'color',
        'googleId',
        'avatar',
        'password',
        'facebookId',
        'complete_checkin_data',
        'checkin_email',
        'off_email',
        'son_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guard_name = 'session-guest';

    protected $casts = [
        'off_email' => 'boolean',
    ];

    public function stays()
    {
        return $this->belongsToMany(Stay::class)->withPivot('chain_id');
    }

    /**
     * Accesor para obtener el tipo de avatar.graph.facebook
     */
    public function getAvatarTypeAttribute()
    {
        // Check if the avatar URL is from Google
        if (strpos($this->avatar, 'googleusercontent') !== false) {
            return 'GOOGLE';
        }

        // Check if the avatar URL is from Facebook
        if (strpos($this->avatar, 'facebook.com') !== false || strpos($this->avatar, 'fbcdn.net') !== false) {
            return 'FACEBOOK';
        }

        // Check if it's an image stored on the server
        if (strpos($this->avatar, '/storage/') !== false) {
            return 'STORAGE';
        }

        // If none of the above, return false or any default value you prefer
        return false;
    }

    public function getcompleteCheckinDataAttribute($value)
    {
        return boolval($value);
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
