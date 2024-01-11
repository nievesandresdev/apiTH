<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StayAccess extends Model
{
    use HasFactory;
    protected $fillable = [
        'stay_id',
        'guest_id',
        'device',
    ];
    
    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
