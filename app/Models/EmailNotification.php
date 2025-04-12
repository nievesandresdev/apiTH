<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'stay_id',
        'hotel_id',
        'post_checkin',
        'sent_at'
    ];

    protected $casts = [
        'post_checkin' => 'boolean',
        'sent_at' => 'datetime'
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

}
