<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'code',
        'sessions_current_period',
        'last_session',

        //OTHERS
        'del',      
        'google_url',       

        // STRIPE
        'stripe_id',
        'pm_type',
        'trial_duration',
        'trial_ends_at',
        'trial_starts_at',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'trial_ends_at',
        'trial_starts_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'trial_starts_at' => 'datetime',
    ];

    public function profile()
    {
      return $this->hasOne(Profile::class);
    }

}
