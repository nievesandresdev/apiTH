<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityHoster extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'schedule',
        'status',
        'user_id',
        'facility_id',
        'select',
        'hotel_id',
    ];

    public function facilities()
    {
        return $this->hasOne(Facility::class, 'id', 'facility_id');
    }

    public function images()
    {
        return $this->hasMany(ImageFacilty::class, 'facility_hoster_id');
    }

    public function translate()
    {
        return $this->hasOne('App\Models\FacilityHosterLanguage')->where('language', localeCurrent());
    }
    public function translations()
    {
        return $this->hasMany('App\Models\FacilityHosterLanguage');
    }

}
