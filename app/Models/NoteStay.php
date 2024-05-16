<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NoteStay extends Model
{
    protected $fillable = [
        'stay_id',
        'content',
        'edited'
    ];

    public function stay()
    {
        return $this->belongsTo(Stay::class);
    }
}
