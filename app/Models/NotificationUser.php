<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    use HasFactory;

    protected $table = 'notification_user';
    protected $fillable = [
        'user_id', 
        'notification_id',
        'status', 
        'vote', 
    ];
    
}
