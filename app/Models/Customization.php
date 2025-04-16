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
        'chain_id',
        //
        'son_id'
    ];

    protected $casts = [
        'colors' => 'array',
    ];

    public function chain()
    {
        return $this->belongsTo(Chain::class);
    }


    public function valueDefault () {
        $customizationDefault = [
            "colors" => [
                [
                    "cod_hex" => '#333333',
                    "cod_rbg" => 'rgb(51, 51, 51)',
                    "contrast" => '1',
                ],
                [
                    "cod_hex" => '#FFD700',
                    "cod_rbg" => 'rgb(250, 215, 0)',
                    "contrast" => '0',
                ]
            ],
            "logo" => null,
            "name" => null,
            "type_header" => '0',
            "tonality_header" => '0',
        ];
        return $customizationDefault;
    }
    
    
}
