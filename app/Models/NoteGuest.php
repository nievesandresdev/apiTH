<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteGuest extends Model
{
    protected $fillable = [
        'stay_id',
        'guest_id',
        'content',
        'edited'
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }
}
