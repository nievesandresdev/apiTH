<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stay extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'room',
        'number_guests',
        'companion',
        'language',
        'check_in',
        'check_out',
        'hour_checkin',
        'hour_checkout',
        'pending_queries_seen',
        'sessions',
        'trial',
        'guest_id',
        'son_id',
        'is_demo'
    ];

    protected $casts = [
        'sessions' => 'array',
        'is_demo' => 'boolean'
    ];

    public function staySurvey()
    {
        return $this->hasMany(StaySurvey::class, 'stay_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function accesses()
    {
        return $this->hasMany(StayAccess::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    public function guests()
    {
        return $this->belongsToMany(Guest::class);
    }

    public function queries()
    {
        return $this->hasMany(Query::class);
    }

    public function notes()
    {
        return $this->hasMany(NoteStay::class);
    }

    public function guestNotes()
    {
        return $this->hasMany(NoteGuest::class);
    }

    //attr
    public function getTrialAttribute($value)
    {
        return boolval($value);
    }

}
