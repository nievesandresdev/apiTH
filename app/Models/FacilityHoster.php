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
        'visible',
        'hotel_id',
        'schedules',
        'always_open',
        'ad_tag',
        'order',

        // para sincronización el registro padre y el registro hijo
        'son_id',
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
        return $this->hasOne('App\Models\FacilityHosterLanguage', 'facility_hoster_id')->where('language', localeCurrent());
    }
    public function translations()
    {
        //
        return $this->hasMany('App\Models\FacilityHosterLanguage', 'facility_hoster_id');
    }

    public function scopeWhereVisible ($query) {
        $query->where(['select' => 1, 'status' => 1, 'visible' => 1]);
    }

}
