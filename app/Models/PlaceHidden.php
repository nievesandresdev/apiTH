<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaceHidden extends Model
{
    use HasFactory;
    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $table = 'place_hidden';

    protected $fillable = [
        'user_id',
        'place_id',
        'hotel_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
