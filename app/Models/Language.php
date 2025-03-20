<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'active'
    ];
    

    public function chats()
    {
        return $this->belongsToMany(ChatSetting::class);
    }
}
