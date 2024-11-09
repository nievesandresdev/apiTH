<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'ends_at',
        'trial_ends_at',
        'user_id',
        'hotel_id',
    ];
}
