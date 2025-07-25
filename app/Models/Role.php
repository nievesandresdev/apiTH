<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'guard_name', 'comment'];

    public function notifications()
    {
        return $this->belongsToMany(Notification::class, NotificationRole::class);
    }
}
