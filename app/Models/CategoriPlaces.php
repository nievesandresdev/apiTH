<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriPlaces extends Model
{
    use HasFactory;
    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'type_places_id',
        'name',
        'active',
        'show',
        'icon',
        'translate'
    ];

    protected $casts = [
        'translate' => 'array',
    ];  

    /**
     * Método que obtiene el los detalles de las actividades guardadas
     *
     * @author  
     * @return object 
     */
    public function TypePlaces()
    {
        return $this->belongsTo(TypePlaces::class);
    }
    public function Places()
    {
      return $this->hasMany(Places::class);
    }
  
}
