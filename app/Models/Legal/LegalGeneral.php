<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Hotel;

class LegalGeneral extends Model
{
    use HasFactory;

    //protected $table = 'legal_general';

    protected $fillable = [
        'hotel_id',
        'name',
        'address',
        'nif',
        'email',
        'protection',
        'email_protection',
    ];

    // Relación con el modelo Hotel
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
