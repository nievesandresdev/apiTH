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
        'city_id',
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
        'show_confort',
        'show_transport',
        'show_places',
        'show_profile',
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
        //
        'show_checkin_stay',
        'parent_id',
        //
        'chat_service_enabled',
        'checkin_service_enabled',
        'reviews_service_enabled',
        //
        'contact_whatsapp_number',
        'contact_email',
        'show_contact',
    ];

    //bool offer_benefits
    protected $casts = [
        'offer_benefits' => 'boolean',
        'buttons_home' => 'boolean',
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

    //hasMany rewards
    public function referrals()
    {
        return $this->hasMany(Reward::class)->where('type_rewards', 'referrals');
    }

    public function referent()
    {
        return $this->hasMany(Reward::class)->where('type_rewards', 'referent');
    }


    public function hotelCommunications()
    {
        return $this->hasMany(HotelCommunication::class);
    }

    public function hotelCommunicationsWithDefault()
    {
        if ($this->hotelCommunications->isEmpty()) {
            return getDefaultHotelCommunications();
        }

        return $this->hotelCommunications;
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

    public function checkinSettings() {
        return $this->hasOne(CheckinSetting::class);
    }

    public function chatHours()
    {
        return $this->hasMany(ChatHour::class);
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

    public function querySettings() {
        return $this->hasOne(QuerySetting::class);
    }

    public function requestSettings() {
        return $this->hasOne(RequestSetting::class);
    }

    public function wifiNetworks()
    {
        return $this->hasMany(HotelWifiNetworks::class);
    }

    public function languageNames()
    {
        return $this->hasMany(HotelTranslate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('del', 0);
    }

    public function buttons()
    {
        return $this->hasMany(HotelButton::class)->orderBy('order');
    }

    public function activeButtons()
    {
        return $this->hasMany(HotelButton::class)->where('is_visible', true)->orderBy('order');
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

 /*    public function getButtonsHomeAttribute($value)
    {
        $defaultButtonsHome = [
            'show_wifi' => false,
            'show_call' => false,
            'show_legal_text' => false,
            'show_all' => false,
        ];

        if ($value === null || $value === 'null' || $value === '') {
            return $defaultButtonsHome;
        }

        return json_decode($value, true) ?: $defaultButtonsHome;
    } */

    public function getImageAttribute($value)
    {
        return $value ? $value : '/storage/gallery/general-1.jpg';
    }

    public function getShowCheckinStayAttribute($value)
    {
        return boolval($value);
    }

    public function getChatServiceEnabledAttribute($value)
    {
        return boolval($value);
    }

    public function getCheckinServiceEnabledAttribute($value)
    {
        return boolval($value);
    }

    public function getReviewsServiceEnabledAttribute($value)
    {
        return boolval($value);
    }

    public function getShowContactAttribute($value)
    {
        return boolval($value);
    }

    /* public function getButtonsAttribute()
    {
        $allButtons = $this->buttons()->get();
        $visibleButtons = $allButtons->where('is_visible', true)->sortBy('order')->values();
        $hiddenButtons = $allButtons->where('is_visible', false)->values();

        return [
            'visible' => $visibleButtons,
            'hidden' => $hiddenButtons
        ];
    } */









}
