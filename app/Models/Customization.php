<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customization extends Model
{
    use HasFactory;

    protected $fillable = [
        'colors',
        'logo',
        'name',
        'type_header',
        'tonality_header',
        'chain_id'
    ];

    protected $casts = [
        'colors' => 'array',
    ];

    
    
}
