<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    use HasRoles;

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

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($user) {
            $availableColors = ['C5DC69', 'FB5607', 'FF006E', '8338EC', '3A86FF', '8AC926', 'B12E2E', '1982C4', 'FF595E'];
            $user->color = $availableColors[array_rand($availableColors)];
        });
    }

    public function profile()
    {
      return $this->hasOne(Profile::class);
    }

    public function hotel()
    {
        return $this->belongsToMany(Hotel::class)->withPivot(['manager','permissions']);
    }

}
