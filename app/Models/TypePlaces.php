<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypePlaces extends Model
{
    use HasFactory;
    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [

        'name',
        'active',
        'show',
    ];

    /**
     * Método que obtiene el los detalles de las actividades guardadas
     *
     * @author  
     * @return object 
     */
    public function places()
    {
      return $this->hasMany(Places::class);
    }
    public function categoriPlaces()
    {
      return $this->hasMany(CategoriPlaces::class);
    }
}
