<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'pending',
        'guest_id',
        'stay_id'
    ];

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function stay()
    {
        return $this->belongsTo(Guest::class);
    }

    //attributes
    public function getPendingAttribute($value)
    {
        return boolval($value);
    }

}
