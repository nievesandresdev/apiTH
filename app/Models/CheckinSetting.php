<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinSetting extends Model
{
    use HasFactory;

    public $fillable = [
        'succes_message',
        'first_step',
        'second_step',
        'show_prestay_query',
        'hotel_id',
    ];

    protected $casts = [
        'succes_message' => 'array',
        'first_step' => 'array',
        'second_step' => 'array',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    //attr
    public function getShowPrestayQueryAttribute($value)
    {
        return boolval($value);
    }

}