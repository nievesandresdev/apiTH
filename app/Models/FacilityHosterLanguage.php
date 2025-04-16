<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacilityHosterLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'facility_hoster_id',
        'language',
        'schedule',
        'ad_tag',
        // para sincronización el registro padre y el registro hijo
        'son_id',
    ];

    public function facilityHoster()
    {
        return $this->belongsTo(FacilityHoster::class);
    }

}
