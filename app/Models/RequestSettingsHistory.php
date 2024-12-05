<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestSettingsHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id',
        'request_setting_id',
        'request_to',
        'in_stay_activate'
    ];
    
    /**
     * Relación con el hotel.
     */
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    /**
     * Relación con request_setting.
     */
    public function requestSetting()
    {
        return $this->belongsTo(RequestSetting::class, 'request_setting_id');
    }
}
