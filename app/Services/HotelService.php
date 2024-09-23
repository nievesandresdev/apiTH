<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Hotel;
use App\Models\User;
use App\Models\ImagesHotels;
use App\Models\HotelSubdomain;

use App\Http\Resources\HotelResource;
use App\Models\ChatHour;

use App\Services\Chatgpt\TranslateService;

use App\Jobs\TranslateModelJob;

class HotelService {

    protected $translateService;

    function __construct(
        TranslateService $_TranslateService
    )
    {
        $this->translateService = $_TranslateService;
    }

    public function getAll ($request, $modelHotel) {

        $user = User::find(2389);
        // $user = \Auth::user();
        // Log::info('hotel withoutCurrent '. $request->withoutCurrent);
        if (filter_var($request->withoutCurrent, FILTER_VALIDATE_BOOLEAN)) {
            // Log::info('entro withoutCurrent '. $modelHotel->id);
            $hotelsCollection = $user->hotel()->where('del', 0)->where('hotels.id','!=', $modelHotel->id)->get();
        }else{
            // Log::info('no entro withoutCurrent '. $modelHotel->id);
            $hotelsCollection = $user->hotel()->where('del', 0)->get();
        }

        return $hotelsCollection;
    }

    public function findByParams ($request) {
        try {
            $subdomain = $request->subdomain ?? null;

            // $query = Hotel::where(function($query) use($subdomain){
            //     if ($subdomain) {
            //         $query->where('subdomain', $subdomain);
            //     }
            // });

            $query = Hotel::whereHas('subdomains', function($query) use($subdomain){
                if ($subdomain) {
                    $query->where('name', $subdomain);
                }
            });

            if (!$subdomain) {
                return null;
            }

            $model = $query->first();

            // $data = new HotelResource($model);

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findById ($id) {
        try {

            $model = Hotel::find($id);

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getChatHours ($hotelId,$all = false) {
        try {
            $defaultChatHours = defaultChatHours();

            if ($all) {
                $query = ChatHour::where('hotel_id',$hotelId);
            }else{
                $query = ChatHour::where('hotel_id',$hotelId)->where('active',1);
            }

            if (!$query->exists()) {
                return $defaultChatHours;
            }else{
                $chatHours = $query->get();
                return $chatHours;
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateProfile ($request, $hotelModel) {
        $hotelModel->name = $request->name;
        $hotelModel->type = $request->type;
        $hotelModel->category = $request->category;
        $hotelModel->email = $request->email;
        $hotelModel->phone = $request->phone;
        $hotelModel->phone_optional = $request->phone_optional;
        $hotelModel->address = $request->address;
        $hotelModel->latitude = $request->metting_point_latitude;
        $hotelModel->longitude = $request->metting_point_longitude;
        $hotelModel->checkin = $request->checkin;
        $hotelModel->checkin_until = $request->checkin_until;
        $hotelModel->checkout = $request->checkout;
        $hotelModel->checkout_until = $request->checkout_until;
        $hotelModel->description = $request->description;
        $hotelModel->instagram_url = $request->urlInstagram;
        $hotelModel->pinterest_url = $request->urlPinterest;
        $hotelModel->facebook_url = $request->urlFacebook;
        $hotelModel->x_url = $request->urlX;
        $hotelModel->with_wifi = $request->with_wifi;
        $hotelModel->show_profile = $request->show_profile;

        $hotelModel->save();
        return $hotelModel;
    }

    public function asyncImages ($request, $hotelModel) {
        $imgs = collect($request->images_hotel??[]);
        $news_imgs = $imgs->filter(function($item){
            return !isset($item['id']) || empty($item['id']);
        });
        foreach ($news_imgs as $img) {
            ImagesHotels::create([
                'hotel_id' => $hotelModel->id,
                'name' => $img['name'],
                'url' => $img['url'],
                'type' => $img['type'],
            ]);
        }

        if(is_array($request->delete_imgs) && count($request->delete_imgs)){
            foreach ($request->delete_imgs as $img_id) {
                ImagesHotels::where("hotel_id", $hotelModel->id)->where("id", $img_id)->delete();
            }
        }
    }

    public function updateTranslation ($model, $translation) {
        $translation = collect($translation ?? []);

        foreach ($translation as $lg => $value) {
            $value = $value->description ?? null;
            if ($lg == 'es') {
                $model->description = $value;
                $model->save();
            }
            $model->translations()->updateOrCreate(
                [
                    'language' => $lg,
                    'hotel_id' => $model->id
                ],
                [
                    'description' => $value,
                    'name' => $model->name,
                    'zone' => $model->zone,
                    'type' => $model->type
                ]
            );
        }
    }

    public function processTranslateProfile ($request, $hotelModel) {
        $description = $request->description;
        // Log::info('processTranslateProfile $description'. !$description);
        if ($description && $description != $hotelModel->description) {
            $dirTemplateTranslate = 'translation/webapp/hotel_input/description';
            $inputsTranslate = ['description' => $description];
            TranslateModelJob::dispatch($dirTemplateTranslate, $inputsTranslate, $this, $hotelModel);
        }else{
            $hotelModel->translations()->update(['description' => null]);
        }

    }

    public function updateVisivilityFacilities ($hotelModel) {
        $hotelModel = $hotelModel->update(['show_facilities' => !$hotelModel->show_facilities]);
        return $hotelModel;
    }

    public function updateVisivilityExperiences ($hotelModel) {
        $hotelModel = $hotelModel->update(['show_experiences' => !$hotelModel->show_experiences]);
        return $hotelModel;
    }

    public function updateVisivilityPlaces ($hotelModel) {
        $hotelModel = $hotelModel->update(['show_places' => !$hotelModel->show_places]);
        return $hotelModel;
    }

    public function updateSenderMailMask ($hotelModel, $email) {
        $hotelModel = $hotelModel->update(['sender_mail_mask' => $email]);
        return $hotelModel;
    }

    public function updateVisivilityCategory ($request, $hotelModel) {
        if ($hotelModel->hiddenCategories()->where('categori_places_id', $request->categori_places_id)->exists()) {
            $hotelModel->hiddenCategories()->detach($request->categori_places_id);
        } else {
            $hotelModel->hiddenCategories()->attach($request->categori_places_id);
        }
    }

    public function updateVisivilityTypePlace ($request, $hotelModel) {
        if ($hotelModel->hiddenTypePlaces()->where('type_places_id', $request->type_places_id)->exists()) {
            $hotelModel->hiddenTypePlaces()->detach($request->type_places_id);
        } else {
            $hotelModel->hiddenTypePlaces()->attach($request->type_places_id);
        }
    }

    public function verifySubdomainExistPerHotel ($subdomain, $hotelModel) {
        if (!$hotelModel || $hotelModel->subdomain == $subdomain) {
            return  false;
        }
        $exist = HotelSubdomain::where(['name' => $subdomain])->whereNot('hotel_id', $hotelModel->id)->exists();
        return $exist;
    }
    public function verifySubdomainExist ($subdomain, $hotelModel) {
        if ($hotelModel->subdomain == $subdomain) {
            return  true;
        }
        $exist = HotelSubdomain::where(['name' => $subdomain])->exists();
        return $exist;
    }
    public function updateSubdomain ($subdomain, $hotelModel) {
        if ($subdomain == $hotelModel->subdomain) {
            return;
        }
        
        HotelSubdomain::where([
            'hotel_id' => $hotelModel->id,
            'active' => true
        ])->update(['active'=> false]);

        $hotelSubdomain = HotelSubdomain::firstOrCreate([
            'name' => $subdomain,
        ],[
            'name' => $subdomain,
            'hotel_id' => $hotelModel->id,
            'active' => true
        ]);
        $hotelSubdomain->active = true;
        $hotelSubdomain->save();

        $hotelModel->subdomain = $subdomain;

        $hotelModel->save();
    }
    public function updateCustomization ($request, $hotelModel) {
        [
            'language_default_webapp' => $languageDefaultWebapp,
            'img_selected_logo' => $imgSelectedLogo,
            'img_selected_bg' => $imgSelectedBg,
            'img_selected_fav' => $imgSelectedFav
        ] = $request->all();
        $hotelModel->language_default_webapp = $languageDefaultWebapp;
        if (!isset($imgSelectedBg['default'])) {
            $hotelModel->image = $imgSelectedBg['url'] ?? null;
        }
        if (!isset($imgSelectedLogo['default'])) {
            $hotelModel->logo = $imgSelectedLogo['url'] ?? null;
        }
        if (!isset($imgSelectedFav['default'])) {
            $hotelModel->favicon = $imgSelectedFav['url'] ?? null;
        }

        $hotelModel->save();
    }

    public function createSubdomainInCloud ($subdomain, $environment) {

        $email = env('EMAIL_CLOUDFLARE');
        $api_key = env('API_KEY_CLOUDFLARE');
        $zone_id = env('ZONE_ID_CLOUDFLARE');
        $ip_address = env('IP_ADDRESS');
        
        // Construye el nombre completo del subdominio
        $full_domain = $subdomain . ($environment == "pro" ? '' : '.' . $environment) . '.thehoster.io';

        // Inicializa cURL
        $ch = curl_init("https://api.cloudflare.com/client/v4/zones/{$zone_id}/dns_records");

        // Prepara el payload JSON
        $payload = json_encode([
            'type'    => 'A',
            'name'    => $full_domain,
            'content' => $ip_address,
            'ttl'     => 1,
            'proxied' => false,
        ]);

        // Configura las opciones de cURL
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Auth-Email: ' . $email,
            'X-Auth-Key: ' . $api_key,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Ejecuta la petición y guarda la respuesta
        $response = curl_exec($ch);
        curl_close($ch);

        // Verifica si la respuesta es exitosa
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success']) {
            return "success";
        } else {
            return "Error al crear el subdominio: " . $response;
        }
    }

}
