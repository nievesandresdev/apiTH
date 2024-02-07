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

    public function messageable()
    {
        return $this->morphTo();
    }

    //attributes
    public function getAutomaticAttribute($value)
    {
        return boolval($value);
    }


}
