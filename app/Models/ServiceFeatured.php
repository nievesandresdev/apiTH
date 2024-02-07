<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceFeatured extends Model
{
    use HasFactory;
    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $table = 'service_featured';

    protected $fillable = [
        'user_id',
        'product_id',
        'hotel_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
    public function product()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
