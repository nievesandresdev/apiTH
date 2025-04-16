<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    use HasFactory;

    public $fillable = ['subdomain','type','parent_hotel_id'];

    public function hotel()
    {
        return $this->hasOne(Hotel::class);
    }

    public function hotels()
    {
        return $this->hasMany(Hotel::class);
    }
    public function subdomainActive()
    {
        return $this->hasOne(ChainSubdomain::class)->where('active', 1);
    }

    public function customization()
    {
        return $this->hasOne(Customization::class);
    }

}
