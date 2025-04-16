<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageFacilty extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'type',
        'facility_hoster_id',
        // para sincronización el registro padre y el registro hijo
        'son_id'
    ];

    public function facilityHoster()
    {
        return $this->belongsTo(FacilityHoster::class, 'facility_hoster_id', 'id');
    }

}
