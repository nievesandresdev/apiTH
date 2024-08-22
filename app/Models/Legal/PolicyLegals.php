<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Hotel;

class PolicyLegals extends Model
{
    use HasFactory;

    public $table = 'policie_legals';

    protected $fillable = [
        'hotel_id',
        'title',
        'description',
        'penalization',
        'penalization_details',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
