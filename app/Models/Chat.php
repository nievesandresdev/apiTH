<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'pending',
        'chatable_id',
        'chatable_type',
    ];

    public function chatable()
    {
        return $this->morphTo();
    }
    
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    //attributes
    public function getPendingAttribute($value)
    {
        return boolval($value);
    }

}
