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
use App\Models\CategoriPlaces;
use App\Models\TypePlaces;

use App\Http\Resources\HotelBasicDataResource;
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

        $user = \Auth::user();
        // Log::info('hotel withoutCurrent '. $request->withoutCurrent);
        if (filter_var($request->withoutCurrent, FILTER_VALIDATE_BOOLEAN)) {
            // Log::info('entro withoutCurrent '. $modelHotel->id);
            //$hotelsCollection = $user->hotel()->where('del', 0)->where('hotels.id','!=', $modelHotel->id)->get();
            if ($user->parent_id) {
                $hotelsCollection = User::find($user->parent_id)
                    ->hotel() // Relación de hoteles del usuario padre
                    ->where('del', 0) // Condición para excluir hoteles eliminados
                    ->where('hotels.id', '!=', $modelHotel->id) // Excluir el hotel actual
                    ->get();
            } else {
                $hotelsCollection = $user->hotel()
                    ->where('del', 0)
                    ->where('hotels.id', '!=', $modelHotel->id)
                    ->get();
            }

        }else{
            // Log::info('no entro withoutCurrent '. $modelHotel->id);
            $hotelsCollection = $user->hotel()->where('del', 0)->get();
        }

        return $hotelsCollection;
    }

    public function getHotelsByUser() {
        $user = \Auth::user();
        $hotelsCollection = $user->hotel()->where('del', 0)->get();
        return $hotelsCollection;
    }

    public function updateDefaultHotel($request) {
        $user = \Auth::user();

        // Revisamos si el usuario ya tiene un hotel predeterminado
        $currentDefaultHotel = $user->hotel()->wherePivot('is_default', 1)->first();

        // Si tiene un hotel predeterminado, lo actualizamos a 0
        if ($currentDefaultHotel) {
            $user->hotel()->updateExistingPivot($currentDefaultHotel->id, ['is_default' => 0]);
        }

        // Actualizamos el nuevo hotel a predeterminado
        $user->hotel()->updateExistingPivot($request->hotel_id, ['is_default' => 1]);

        // Recuperamos el hotel que ha sido marcado como predeterminado
        $newDefaultHotel = $user->hotel()->where('hotels.id', $request->hotel_id)->first();

        return $newDefaultHotel;
    }

    public function getRewardsByHotel($modelHotel)
    {
        $modelHotel->loadMissing(['referrals', 'referent']);


        return [
            'name' => $modelHotel->name,
            'referrals' => $modelHotel->referrals->first(),
            'referent'  => $modelHotel->referent->first(),
        ];
    }


    public function findByParams ($request) {
        try {
            $subdomain = $request->subdomain ?? null;
            $id = $request->id ?? null;

            // $query = Hotel::where(function($query) use($subdomain){
            //     if ($subdomain) {
            //         $query->where('subdomain', $subdomain);
            //     }
            // });
            if ($subdomain) {
                $query = Hotel::where('subdomain', $subdomain);
            }

            if ($id) {
                $query = Hotel::where('id', $id);
            }


            // $query = Hotel::whereHas('subdomains', function($query) use($subdomain){
            //     if ($subdomain) {
            //         $query->where('name', $subdomain);
            //     }
            // });

            /* if (!$subdomain) {
                return null;
            }
 */
            $model = $query->first();

            // $data = new HotelResource($model);

            return $model;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findById($id) {
        try {

            $model = Hotel::find($id);

            return new HotelBasicDataResource($model);

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
        $hotelModel->buttons_home = json_encode($request->buttons);

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

    public function updateShowButtons($request,$hotelModel)
    {
        $buttonsData = $request->buttons;
        $imageData = $request->image ?? null;

        if ($buttonsData) {
            $hotelModel->buttons_home = json_encode($buttonsData);
        }

        if ($imageData) {
            $hotelModel->image = $imageData;
        }


        $hotelModel->save();

        return $hotelModel;
    }

    public function translateAll () {

        $lgsAll = getAllLanguages()->toArray();


        $query = Hotel::query();

        $hotelCollection = $query->limit(1)->get();

        foreach ($hotelCollection as $hotelModel) {
            $translations = collect($hotelModel->translations);
            

            $lgsWithTranslations = $translations->pluck('language')->toArray();
            $lgsWithoutTranslations = array_values(array_diff($lgsAll, $lgsWithTranslations));
            $dirTemplateTranslate = 'translation/webapp/hotel_input/description';
            $description = $translations->first()->description;

            $inputsTranslate = ['description' => $description];
            TranslateModelJob::dispatchSync($dirTemplateTranslate, $inputsTranslate, $this, $hotelModel, $lgsWithoutTranslations);

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
        if (!$description) {
            $hotelModel->translations()->update(['description' => null]);
            return;
        }
        if ($description != $hotelModel->description) {
            $dirTemplateTranslate = 'translation/webapp/hotel_input/description';
            $inputsTranslate = ['description' => $description];
            TranslateModelJob::dispatch($dirTemplateTranslate, $inputsTranslate, $this, $hotelModel);
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

    public function updateVisivilityServices ($request, $hotelModel) {
        $nameService = $request->service;
        $input = "show_$nameService";
        $hotelModel = $hotelModel->update([$input => !$hotelModel[$input]]);
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

            $categoriplace = CategoriPlaces::find($request->categori_places_id);
    
            $typeplace = $categoriplace->TypePlaces;
    
            $categoriesActives = $typeplace->categoriPlaces()->where(['show' => 1, 'active' => 1])->pluck('id');

            $categoriesHiddenHotel = $hotelModel->hiddenCategories;

            if (count($categoriesActives) == count($categoriesHiddenHotel)) {
                $hotelModel->hiddenTypePlaces()->detach($typeplace->id);
            }

        } else {
            $hotelModel->hiddenCategories()->attach($request->categori_places_id);
        }

    }
    
    public function updateVisivilityTypePlace ($request, $hotelModel) {
        $categoriplaces = CategoriPlaces::where(['show' => 1, 'active' => 1, 'type_places_id' => $request->type_places_id])->get()->pluck('id');
        $typeplaces = TypePlaces::where(['show' => 1, 'active' => 1])->get()->pluck('id');
        // $typeplacesHiddenHotel = $hotelModel->hiddenTypePlaces;
        // return $typeplacesHiddenHotel;
        if ($hotelModel->hiddenTypePlaces()->where('type_places_id', $request->type_places_id)->exists()) {
            // return 'e';
            $hotelModel->hiddenTypePlaces()->detach($request->type_places_id);
            $hotelModel->hiddenCategories()->detach($categoriplaces);
            
            $hotelModel->show_places = true;
            $hotelModel->save();
            
        } else {
            // return 't';
            $hotelModel->hiddenTypePlaces()->attach($request->type_places_id);
            $hotelModel->hiddenCategories()->syncWithoutDetaching($categoriplaces);
            $typeplacesHiddenHotel = $hotelModel->hiddenTypePlaces;
            
            $typeplacesHiddenHotel = $hotelModel->hiddenTypePlaces;
            if (count($typeplaces) == count($typeplacesHiddenHotel)) {
                $hotelModel->show_places = false;
                $hotelModel->save();
            }

        }
    }

    public function verifySubdomainExistPerHotel ($subdomain, $hotelModel) {
        if (!$hotelModel || $hotelModel->subdomain == $subdomain) {
            return  false;
        }
        $exist = hotel::where(['subdomain' => $subdomain])->whereNot('hotels.id', $hotelModel->id)->exists();
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

    public function updateSlug ($slug, $hotelModel) {

        $hotelModel->update([
            'subdomain' => $slug,
            'slug' => $slug,
        ]);
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
        try {
            //code...
        } catch (\Throwable $th) {
            //throw $th;
        }

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

    public function handleShowReferrals ($hotelModel) {
       $hotelModel->update(['show_referrals' => !$hotelModel->show_referrals]);

        return $hotelModel;
    }


    public function getMainData ($request) {
        try {
            $subdomain = $request->subdomain ?? null;
            Log::error('subdomain '.$subdomain);

            return Hotel::with([
                'chatSettings:id,hotel_id,show_guest'
            ])
            ->select(
                'hotels.id','hotels.name','hotels.type','hotels.zone','hotels.instagram_url','hotels.facebook_url','hotels.pinterest_url',
                'hotels.show_profile','hotels.subdomain','hotels.logo','hotels.favicon','hotels.show_experiences','hotels.instagram_url',
                'hotels.language_default_webapp','hotels.x_url','hotels.show_facilities','hotels.show_places','hotels.show_transport','hotels.show_confort','hotels.buttons_home',
                'hotels.show_referrals','hotels.show_checkin_stay','hotels.offer_benefits','hotels.latitude','hotels.longitude',
                'hotels.city_id','hotels.checkin','hotels.checkout','hotels.image','hotels.code'
            )
            // chatSettings
            ->where('subdomain', $subdomain)
            ->first();
        } catch (\Exception $e) {
            return $e;
        }
    }

}
