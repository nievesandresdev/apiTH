<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'image_id',
        'concept',
        'url',
        'select',
        'name',
        'type',
        'url_origin',
        'son_id'
    ];
}
