<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
        'active',
        'slug',
        'id',
        'natcode',
        'inspire_id',
        'name',
        'codnut1',
        'codnut2',
        'codnut3',
        'codigoine',
        'shape_area',
        'shape_length',
        'lat',
        'long',
        'near',
    ];

    protected $casts = [
        'near' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Products::class);
    }
    
    public function translate()
    {
        return $this->morphOne('App\Models\LanguageName', 'language_names')->where('language', localeCurrent());
    }


    public function scopeSearch($query, $search) 
    {
        if ($search) {
            // $query->whereHas('language_names', function($language) use($search){
            //     $language->where('language',  'es')->where('name','like',  ['%'.$search.'%']);
            // });
            $query->where('slug','like',  ['%'.$search.'%']);
        }
    }
    
}
