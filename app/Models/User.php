<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Services\StripeServices;
use Laravel\Cashier\Billable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;
    use HasRoles;
    use Billable;

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
        'parent_id',

        //settings
        'permissions', //json
        'notifications', //json
        'status',
        'periodicity_chat',
        'periodicity_stay',
        'feedback_last_notified_at',
        'chat_last_notified_at',

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

    protected $guard_name ='web';

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

    public function scopeMyHotels($query,$hotelIds) {
        $query->whereHas('hotel', function ($query) use ($hotelIds) {
            $query->whereIn('hotel_id', $hotelIds);
        });
    }

    public function getFullNameAttribute()
    {
        return $this->name.' '.$this->profile->lastname;
    }



    public function profile()
    {
      return $this->hasOne(Profile::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id'); //para ver los hoteles del usuario principal
    }

    public function hotel()
    {
        return $this->belongsToMany(Hotel::class)->withPivot(['manager','permissions','is_default']);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getRole()
    {
        return $this->getRoles()->first();
    }

    public function getRoleName()
    {
        return $this->getRole()->name;
    }

    public function isHoster() {

        $role = $this->getRoleNames()->first();

        return in_array($role, ['Administrator', 'Operator']);
    }


    public function status_subscription ($hotel) {

        $user = $this->parent_id ? $this->parent : $this; //ahora se usa con el usuario principalq que es el parent
        //return $this->parent;
        // $user = auth()->user();
        //$hotel = currentHotel();
        // $subscription_hotel = $hotel->subscription();
        /*STUTUS;
        0-> SUSCRITO
        1-> NO SUSCRITO, FREE TRIAL ACTIVO
        2 -> NO SUSCRIPTO, FREE TRIAL EXPIRADO
        3 -> SUSCRIPCION EXPIRADA
        */

        // $stripe_ervices = new StripeServices();
        $data = [
            'on_trial' => false,
            'subscribed' => false,
            'on_grace_period' => false,
            'remaining_days' => 0,
            'trial_ends_at' => null,
            'expired_trial' => null,
            'status' => 0,
            'subscription'=> null,
            'product_name_current' => null,
        ];
        // $data['user'] = $user;
        if (!$user || !$user['trial_ends_at']) {
            return $data;
        }
        // $data['on_trial'] = $user->trial_ends_at && $user->trial_ends_at->isFuture();
        $data['on_trial'] = $user->onTrial();

        $suscription = $hotel->subscription();
        // if ($suscription){
        //     $stripe_ervices->validate_subscription($hotel);
        //     $hotel->refresh();
        // }

        //$data['subscribed'] = $user->subscribed($hotel['subscription_active']);
        $data['subscribed'] = $user->subscriptions()->where(['name' => $hotel->subscription_active, 'stripe_status' => 'active'])->exists();
        $data['on_grace_period'] = $data['subscribed'] ? $user->subscription($hotel['subscription_active'])->onGracePeriod() : false;
        $now = Carbon::now();
        $trial_starts_at = Carbon::parse($user->trial_starts_at);
        $trial_ends_at = Carbon::parse($user->trial_ends_at);
        $data['trial_ends_at'] = $trial_ends_at;
        $diff_days = $now->diffInDays($trial_ends_at, false);
        $data['remaining_days'] = $diff_days;
        $data['expired_trial'] = $trial_ends_at->lessThanOrEqualTo($now);
        // $data['expired_trial'] = ($data['remaining_days'] < 0) || ($trial_starts_at == $trial_ends_at);

        if ($suscription) {
            // $product_subscription = $stripe_ervices->get_products($suscription['stripe_price']);
            // if ($product_subscription) {
            //     $data['product_name_current'] = $product_subscription['name'];
            // }
        }

        // $data['subscription'] = $suscription;
        $date_ends_subscription = null;

        if (!empty($suscription['ends_at'])){
            $date_ends_subscription = $suscription['ends_at'];
            $date_ends_subscription = $date_ends_subscription ? Carbon::parse($date_ends_subscription) : null;
        }

        // STATUS 0

        if ($data['subscribed']) {
            $data['status'] = 0;
        }

        // STATUS 1
        if (!$data['subscribed'] && $user->onTrial()) {
            $data['status'] = 1;
        }

        // STATUS 2
        if (!$data['subscribed'] && $data['expired_trial']) {
            $data['status'] = 2;
        }

        // STATUS 3

        if ($suscription && $suscription->cancelled()) {
            $data['status'] = 3;
        }

        //$data['status'] = 2;

        return $data;
    }

    public function onTrial($name = 'default', $price = null)
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->onTrial()) {
            return false;
        }

        return ! $price || $subscription->hasPrice($price);
    }

}
