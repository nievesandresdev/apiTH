<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chain extends Model
{
    use HasFactory;

    public $fillable = ['subdomain','type'];

    public function customization()
    {
        return $this->hasOne(Customization::class);
    }



}
