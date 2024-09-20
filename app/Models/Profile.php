<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'firstname',
        'lastname',
        'gender',
        'phone',
        'gestor',
        'razon',
        'nif',
        'identify',
        'city',
        'cp',
        'address',
        'province',
        'type',
        'platform_steps',
        'goal_achieved',
        'image',
        'logo',
        'name_hoster',
        'work_position_id'
    ];

    protected $guard_name ='api';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workPosition()
    {
        return $this->belongsTo(WorkPosition::class);
    }


}
