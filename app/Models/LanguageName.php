<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageName extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'language',
        'language_names_id',
        'language_names_type'
    ];


    public function language_names () {
        return $this->morphTo();
    }
    
}
