<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;
use App\Models\Legal\LegalGeneral;
use App\Models\Legal\PolicyLegals;

class Hotel extends Model
{
    use HasFactory;
    // use Billable;
    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'name_origin',
        'type',
        'address',
        'zone',
        'image',
        'category',
        'phone',
        'email',
        'latitude',
        'longitude',
        'checkin',
        'checkout',
        'description',
        'instagram_url',
        'facebook_url',
        'pinterest_url',
        'subscription_active',
        'slug',
        'name_short',
        'logo',
        'user_ratings_total',
        'url_google',
        'website_google',
        'google_maps_place_id',
        'historical_runs',
        'scraper_run',
        'last_date_historical',
        'show_experiences',
        'show_places',
        'phone_optional',
        'with_wifi',
        'checkin_until',
        'checkout_until',
        'x_url',
        'show_facilities',
        'sender_mail_mask',
        // customization
        'subdomain',
        'language_default_webapp',
        'sender_for_sending_sms',
        'sender_for_sending_email',
        'code',
        'chain_id',
        'show_referrals',
        'offer_benefits',
    ];

    /* public function user()
    {
        return $this->belongsToMany(User::class);
    } */

    public function user()
    {
        return $this->belongsToMany(User::class)->withPivot('manager');
    }

    public function subdomains()
    {
        return $this->hasMany(HotelSubdomain::class);
    }

    public function chain()
    {
        return $this->belongsTo(Chain::class);
    }

    public function facilities()
    {
        return $this->hasMany(FacilityHoster::class);
    }

    public function images()
    {
        return $this->hasMany(ImagesHotels::class);
    }

    public function translate()
    {
        return $this->hasOne(HotelTranslate::class)->where('language', localeCurrent());
    }

    public function translations()
    {
        return $this->hasMany(HotelTranslate::class);
    }

    public function otas()
    {
        return $this->hasMany(HotelOta::class);
    }

    public function chatSettings() {
        return $this->hasOne(ChatSetting::class);
    }

    public function generalLegal()
    {
        return $this->hasOne(LegalGeneral::class);
    }

    public function policies()
    {
        return $this->hasMany(PolicyLegals::class)->where('del',0);
    }

    public function chatMessages()
    {
        return $this->morphMany(ChatMessage::class, 'messageable');
    }

    public function hiddenCategories()
    {
        return $this->belongsToMany(CategoriPlaces::class, 'hotel_category_places_hides', 'hotel_id', 'categori_places_id');
    }

    public function hiddenTypePlaces()
    {
        return $this->belongsToMany(TypePlaces::class, 'hotel_type_places_hides', 'hotel_id', 'type_places_id');
    }

    public function stays()
    {
        return $this->hasMany(Stay::class);
    }

    public function gallery()
    {
        return $this->hasMany(ImageGallery::class, 'image_id');
    }


    public function scopeActive($query)
    {
        return $query->where('del', 0);
    }




    // AUXILIARIES

    public function toArray()
    {
        $fakeChatSettings = new \stdClass();
        $fakeChatSettings->show_guest = true;
        $fakeChatSettings->hotel_id = $this->id;
        $array = parent::toArray(); // Obtener todas las propiedades y relaciones

        // Modificar o agregar propiedades específicas
        $array['chat_settings'] = $this->chatSettings ?? (object)[
            'show_guest' => $fakeChatSettings->show_guest,
            'hotel_id' => $fakeChatSettings->hotel_id
        ];

        return $array;
    }

    public function subscription () {
        $hotel = $this;
        $user = $hotel->user[0];
        $subscription = null;
        if (!empty($hotel->subscription_active)) {
            $subscription = $user->subscription($hotel->subscription_active);
        }
        // $subscriptions = $user->subscriptions;
        // $subscription = $subscriptions->where('hotel_id', $hotel->id)->first();
        return $subscription;    }

    public function price_current () {
        $subscription = $this->subscription();
        return $subscription;
        // $plan = $this->stripe->plans->retrieve($request->price_id);
    }

    public function getButtonsHomeAttribute($value)
    {
        $defaultButtonsHome = [
            'show_wifi' => false,
            'show_call' => false,
            'show_legal_text' => false,
            'show_all' => false,
        ];

        return $value ? json_decode($value, true) : $defaultButtonsHome;
    }

    public function getImageAttribute($value)
    {
        return $value ? $value : '/storage/gallery/general-1.jpg';
    }


}
