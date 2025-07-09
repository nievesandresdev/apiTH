<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DossierData extends Model
{
    use HasFactory;

    protected $fillable = [
        'dossier_id',
        'type',
        'numeral',
        'rooms',
        'averagePrice',
        'occupancyRate',
        'reputationIncrease',
        'pricePerNightIncrease',
        'occupancyRateIncrease',
        'recurrenceIndex',
        'pricePerRoomPerMonth',
        'implementationPrice',
        'investmentInHoster',
        'openMonths',
        'benefit',
    ];

    protected static function booted()
    {
        static::creating(function ($dossierData) {

            if (empty($dossierData->tab_number)) {
                $dossierData->tab_number = self::getNextTabNumber();
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
    private static function getNextTabNumber()
    {
        return self::max('tab_number') + 1;
    }

    //accesors sin decimales
    public function getOccupancyRateAttribute($value)
    {
        return (int) round($value);
    }

}
