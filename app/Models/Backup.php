<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{

    public $table = 'backups_db';

    protected $fillable = [
        'file_name', 'file_path', 'disk','type'
    ];
}
