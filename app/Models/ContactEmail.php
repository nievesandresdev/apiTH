<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactEmail extends Model{
    use HasFactory;

    protected $fillable = ['stay_id', 'guest_id', 'message'];

    public function stay(){
        return $this->belongsTo(Stay::class);
    }

    public function guest(){
        return $this->belongsTo(Guest::class);
    }
}

