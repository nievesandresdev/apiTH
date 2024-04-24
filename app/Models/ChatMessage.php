<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'text',
        'status',
        'by',
        'messageable_id',
        'messageable_type',
        'automatic'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function messageable()
    {
        return $this->morphTo();
    }

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    //attributes
    public function getAutomaticAttribute($value)
    {
        return boolval($value);
    }


}
