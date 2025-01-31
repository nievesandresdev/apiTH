<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierData extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'rooms',
        'average_price',
        'occupancy_rate',
        'reputation_increase',
        'price_per_night_increase',
        'occupancy_rate_increase',
        'price_per_room_per_month',
        'implementation_price',
        'investment_in_hoster',
        'benefit',
    ];

    protected static function booted()
    {
        static::creating(function ($dossierData) {

            if (empty($dossierData->tab_number)) {
                $dossierData->tab_number = self::getNextTabNumber($dossierData->dossier_id);
            }
        });
    }



    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    /**
     * Obtener el siguiente tab_number para un dossier_id.
     */
    private static function getNextTabNumber($dossierId)
    {
        return self::where('dossier_id', $dossierId)->max('tab_number') + 1;
    }
}
