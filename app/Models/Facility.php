<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image',
    ];

    use HasFactory;

    public function translate()
    {
        return $this->hasOne(FacilityHosterLanguage::class)->where('language', localeCurrent());
    }
}
