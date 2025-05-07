<?php

namespace App\Services;

use App\Mail\Guest\ContactToHoster;
use App\Mail\Guest\MsgStay;
use App\Mail\Guest\ResetPasswordGuest;
use App\Models\Chat;
use App\Models\ContactEmail;
use App\Models\Guest;
use App\Models\hotel;
use App\Models\NoteGuest;
use App\Models\Query;
use App\Models\Stay;
use App\Models\StayAccess;
use App\Models\StayNotificationSetting;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Services\MailService;
use App\Utils\Enums\EnumsLanguages;
use App\Utils\Enums\GuestEnum;

class GuestService {

    public $stayAccessService;
    public $mailService;
    public $colors;

    function __construct(
        StayAccessService $_StayAccessService,
        MailService $_MailService
    )
    {
        $this->stayAccessService = $_StayAccessService;
        $this->mailService = $_MailService;
    }

    public function findById($id)
    {
        try {
            return $guest = Guest::find($id);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function saveOrUpdate($data, $makeNullname = false)
    {
        try {

            $email = $data->email;
            $name = $data->name ?? null;
            $phone = $data->phone ?? null;
            $lang = $data->language ?? 'es';
            $avatar = $data->avatar ?? null;
            $googleId = $data->googleId ?? null;
            $facebookId = $data->facebookId ?? null;
            $completeCheckinData = $data->complete_checkin_data ?? false;
            $guest = Guest::where('email',$email)->first();
            $acronym = $this->generateInitialsName($name ?? $email);
            //guardar name vacio
            if($makeNullname) $name = null;
            if(!$guest){
                $guest = Guest::create([
                    'name' =>$name,
                    'email' => $email,
                    'lang_web' => $lang,
                    'acronym' => $acronym,
                    'phone' => $phone ?? null,
                    'avatar' => $avatar,
                    'googleId' => $googleId,
                    'facebookId' => $facebookId
                ]);

            }else{
                $guest->name = $name;
                $guest->lang_web = $lang;
                $guest->phone = $phone ?? $guest->phone;
                $guest->avatar = $avatar ?? $guest->avatar;
                $guest->googleId = $googleId ?? $guest->googleId;
                $guest->facebookId = $facebookId ?? $guest->facebookId;
                $guest->complete_checkin_data = $completeCheckinData;

                if($acronym){
                    $guest->acronym = $acronym;
                }
                $guest->save();
            }
            return $guest;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findByEmail($email)
    {
        try {
            return  Guest::where('email',$email)->first();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updatePasswordGuest($data)
    {
        try {
            //return $data->id;
            $guest = Guest::find($data->id);

            // Si el campo password es null, permite establecer la nueva contraseña
            if (is_null($guest->password) || Hash::check($data->currentPassword, $guest->password)) {
                // Actualiza la nueva contraseña
                $guest->password = Hash::make($data->newPassword);
                $guest->save();

                return [
                    'valid_password' => true,
                    'data' => $guest
                ];
            }

            // Si la contraseña actual no es válida
            return [
                'valid_password' => false,
                'message' => __('response.invalid_password')
            ];

        } catch (\Exception $e) {
            return $e;  // Puedes registrar el error o manejarlo según tu configuración
        }
    }

    public function updateDataGuest($guest, $request, $completeCheckin = false, $updateFields = []) {
        try {
            Log::info('test updateDataGuest'. json_encode($request->all(), JSON_PRETTY_PRINT));
            Log::info('updateFields'.json_encode(is_array($updateFields)));
            //name e email no pueden ser borrados
            $guest->name = $request->name ?? $guest->name;
            $guest->email = $request->email ?? $guest->email;
            
            in_array('lastname', $updateFields, true) || count($updateFields) == 0 ? $guest->lastname = $request->lastname : '';
            in_array('secondLastname', $updateFields, true) || count($updateFields) == 0 ? $guest->second_lastname = $request->secondLastname : '';
            in_array('gender', $updateFields, true) || count($updateFields) == 0 ? $guest->sex = $request->gender : '';

            if($request->birthdate && (in_array('birthdate', $updateFields, true) || count($updateFields) == 0 )){ 
                $birthdate = Carbon::createFromFormat('d/m/Y', $request->birthdate)
                ->format('Y-m-d');
                $guest->birthdate = $birthdate;
            }
            in_array('responsibleAdult', $updateFields, true) || count($updateFields) == 0 ? $guest->responsible_adult = $request->responsibleAdult : '';
            in_array('kinshipRelationship', $updateFields, true) || count($updateFields) == 0 ? $guest->kinship_relationship = $request->kinshipRelationship : '';
            in_array('nationality', $updateFields, true) || count($updateFields) == 0 ? $guest->nationality = $request->nationality : '';
            in_array('docType', $updateFields, true) || count($updateFields) == 0 ? $guest->doc_type = $request->docType : '';
            
            //
            in_array('docSupportNumber', $updateFields, true) || count($updateFields) == 0 ? $guest->doc_support_number = $request->docSupportNumber : '';
            in_array('docNumber', $updateFields, true) || count($updateFields) == 0 ? $guest->doc_num = $request->docNumber : '';
            in_array('countryResidence', $updateFields, true) || count($updateFields) == 0 ? $guest->country_address = $request->countryResidence : '';
            in_array('postalCode', $updateFields, true) || count($updateFields) == 0 ? $guest->postal_code = $request->postalCode : '';
            in_array('municipality', $updateFields, true) || count($updateFields) == 0 ? $guest->municipality = $request->municipality : '';
            in_array('addressResidence', $updateFields, true) || count($updateFields) == 0 ? $guest->address = $request->addressResidence : '';
            in_array('checkinEmail', $updateFields, true) || count($updateFields) == 0 ? $guest->checkin_email = $request->checkinEmail : '';
    
            if((in_array('phone', $updateFields, true) || count($updateFields) == 0)){
                $guest->phone = strlen($request->phone) > 4 ? $request->phone : null;
            }

            if($completeCheckin){
                $guest->complete_checkin_data = true;
            }
            //
            $guest->save();
            return $guest;

        } catch (\Exception $e) {
            logger()->error("Error en updateDataGuest: " . $e->getMessage());
            return null;
        }
    }

    public function deleteCheckinData($guest) {
        try {
            $guest->second_lastname = null;
            $guest->phone = null;
            $guest->sex = null;
            //
            $guest->responsible_adult = null;
            $guest->kinship_relationship = null;
            //
            $guest->nationality = null;
            $guest->doc_type = null;
            $guest->doc_support_number = null;
            $guest->doc_num = null;
            $guest->country_address = null;
            $guest->postal_code = null;
            $guest->municipality = null;
            $guest->address = null;
            $guest->complete_checkin_data = false;
            //
            $guest->save();
            return $guest;

        } catch (\Exception $e) {
            logger()->error("Error en deleteCheckinData: " . $e->getMessage());
            return null;
        }
    }


    public function updateLanguage ($data)
    {
        try {
            $guest_id = $data->guest_id;
            $language = $data->language;
            $guest = Guest::find($guest_id);
            $guest->lang_web = $language;
            $guest->save();

            return $guest;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findAndValidLastStay($guestEmail, $chainId, $hotelId = null, $lang = null){

        try {
            $limitDate = Carbon::now()->subDays(11)->toDateString(); // Formato 'YYYY-MM-DD'
            $guest = Guest::where('email', $guestEmail)->first();

            if (!$guest || !$guestEmail) {
                return null; // O maneja el caso donde el huésped no existe
            }

            // Iniciar la consulta
            $query = $guest->stays()
                        ->wherePivot('chain_id', $chainId) // Filtrar por chain_id
                        ->where('check_out', '>', $limitDate);

            // Aplicar filtro por hotel_id si está presente
            if ($hotelId) {
                $query->where('hotel_id', $hotelId);
            }

            // Obtener la última estancia que cumple con los criterios
            $last_stay = $query->first();

            if ($last_stay) {
                $this->stayAccessService->save($last_stay->id, $guest->id);
                return [
                    "stay" => $last_stay,
                    "guest" => $guest
                ];
            }
            return [
                "guest" => $guest
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function inviteToStayByEmail($guest,$stayId,$hotel){
        $settings =  StayNotificationSetting::where('hotel_id',$hotel->id)->first();
        if(!$settings){
            $settingsArray = settingsNotyStayDefault();
            $settings = (object)$settingsArray;
        }
        // Log::info("inviteToStayByEmail settings".json_encode($settings));
        // Log::info("lang_web ".$guest->lang_web);
        if($settings->guestinvite_check_email){
            // Log::info("inviteToStayByEmail entro en envio");
            $data = [
                'stay_id' => $stayId,
                'guest_id' => $guest->id,
                'stay_lang' => $guest->lang_web,
                'msg_text' => $settings->guestinvite_msg_email[$guest->lang_web],
                'guest_name' => $guest->name,
                'hotel_name' => $hotel->name,
                'hotel_id' => $hotel->id,
            ];
            $msg = prepareMessage($data,$hotel);
            $link = prepareLink($data,$hotel);
            // Log::info("inviteToStayByEmail prepareMessage".$msg);
            // Log::info("inviteToStayByEmail hotel".json_encode($hotel));
            // Maiil::to($guest->email)->send(new MsgStay($msg,$hotel));
            $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link,true,$guest->name), $guest->email);
        }
    }

    public function updateById($data, $renew = false){
        if(!$data->id) return;

        try{
            $guest = Guest::find($data->id);

            $name = $guest->name;
            if($data->name){
                $name = $data->name;
            }

            $email = $guest->email;
            if($data->email){
                $email = $data->email;
            }

            $acronym = $this->generateInitialsName($name ?? $email);

            $currentPhone = !$renew ? $guest->phone : null;
            $currentLang = !$renew ? $guest->lang_web : 'es';
            $currentLastname = !$renew ? $guest->lastname : null;
            $currentAvatar = !$renew ? $guest->avatar : null;


            $guest->name = $name;
            $guest->email = $data->email ?? $guest->email;
            $guest->phone = $data->phone ?? $currentPhone;
            $guest->lang_web = $data->lang_web ?? $currentLang;
            $guest->lastname = $data->lastname ?? $currentLastname;
            $guest->avatar = $data->avatar ?? $currentAvatar;


            $guest->acronym = $acronym;

            // Log::info('pass '.$data->password);
            if (isset($data->password) && !empty($data->password)) {
                $guest->password = bcrypt($data->password);
                // Log::info('update pass'. $guest->password);
            }

            $guest->save();
            return $guest;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateById');
        }
    }

    public function confirmPassword($data){
        try{
            $guest = $this->findByEmail($data->email);
            Log::info('$guest find '.json_encode($guest));
            Log::info('compare '.Hash::check($data->password, $guest->password));
            if ($guest && Hash::check($data->password, $guest->password)) {
                return $guest;
            }
            return null;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.confirmPassword');
        }
    }

    public function sendEmail($stayId,$guestId,$guestEmail,$hotelId,$concept = null){

        try{
            $hotel = hotel::find($hotelId);
            $settings =  StayNotificationSetting::where('hotel_id',$hotel->id)->first();
            if(!$settings){
                $settingsArray = settingsNotyStayDefault();
                $settings = (object)$settingsArray;
            }
            $guest = Guest::find($guestId);
            $msg_text = $settings->guestinvite_msg_email[$guest->lang_web];
            $data = [
                'stay_id' => $stayId,
                'guest_id' => $guest->id,
                'stay_lang' => $guest->lang_web,
                'msg_text' => $msg_text,
                'guest_name' => $guest->name,
                'hotel_name' => $hotel->name,
                'hotel_id' => $hotel->id,
            ];

            $msg = prepareMessage($data,$hotel);
            $link = prepareLink($data,$hotel);
            // Maiil::to($guestEmail)->send(new MsgStay($msg,$hotel));
            $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link,true,$guest->name), $guestEmail);
            //
            return  true;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.sendEmail');
        }
    }

    public function generateInitialsName($name)
    {
        try{
            if(!$name) return null;
            // Elimina espacios adicionales
            $name = preg_replace('/\s+/', ' ', trim($name));

            // Divide el nombre en partes
            $parts = explode(' ', trim($name));
            $initials = null;

            // Verifica si el nombre tiene más de una parte
            if (count($parts) > 1) {
                // Si tiene nombre y apellido, toma la primera letra de cada uno
                $initials = mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
            } else {
                // Si solo tiene un nombre, toma las primeras dos letras
                $initials = mb_strtoupper(mb_substr($name, 0, 2));
            }

            return $initials;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.generateInitialsName');
        }
    }

    public function updateColorGuestForStay($colorsExists) {
        // Obtener los colores definidos
        $colors = GuestEnum::COLORS;

        // Asegurarse de que $colorsExists es un array
        $colorsExistsArray = $colorsExists->toArray();

        // Log para ver qué contiene $colorsExistsArray
        // Log::info('$colors '.json_encode($colors));
        // Log::info('$colorsExistsArray '.json_encode($colorsExistsArray));

        // Filtrar colores para encontrar aquellos que no están en $colorsExistsArray
        $availableColors = array_diff($colors, $colorsExistsArray);
        // Log::info('$availableColors '.json_encode($availableColors));

        // Verificar si hay colores disponibles
        if (!empty($availableColors)) {
            // Seleccionar un color al azar de los colores disponibles
            return $availableColors[array_rand($availableColors)];
        } else {
            Log::info('else');
            Log::info('else '.$colors[array_rand($colors)]);
            // Todos los colores están en uso, seleccionar uno al azar de la lista total
            return $colors[array_rand($colors)];
        }
    }

    public function sendResetLinkEmail($email, $hotel, $chain){

        try {
            $hotelData = $hotel;
            $guest = $this->findByEmail($email);

            // Generar token de restablecimiento
            $token = Str::random(60);
             // Guardar token en la base de datos
            DB::table('password_resets')->insert([
                'email' => $guest->email,
                'token' => $token,
                'model_type' => get_class($guest),
                'model_id' => $guest->id,
                'created_at' => now()
            ]);

            // Enviar correo con el enlace de restablecimiento
            // Mail::to($email)->send(new ResetPasswordGuest($url.$token));
            $lastStay = $this->findAndValidLastStay($guest->email, $chain->id, $hotel ? $hotel->id : null);
            if($lastStay && !$hotel){
                $hotelData = $lastStay['stay']->hotel;
            }

            Log::info('hotel '.json_encode($hotelData));
            Log::info('guest '.json_encode($guest));
            Log::info('chain '.json_encode($chain));

            $url = buildUrlWebApp($chain->subdomain, $hotelData->subdomain,'',"email={$email}&acform=reset&token=");
            Log::info('url '.json_encode($url.$token));
            Mail::to($guest->email)->send(new ResetPasswordGuest($hotelData, $url.$token, $guest));
            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function resetPassword($token, $newPassword){

        try {

            $reset = DB::table('password_resets')->where([
                ['token', $token]
            ])->first();
            if(!$reset) return null;

            $guest = $this->findByEmail($reset->email);
            if(!$guest) return null;
            $dataGuest = new \stdClass();
            $dataGuest->id = $guest->id;
            $dataGuest->name = $guest->name;
            $dataGuest->email = $guest->email;
            $dataGuest->password = $newPassword;
            $result = $this->updateById($dataGuest);
            return $result;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function createAccessInStay($guestId, $stayId, $chainId)
    {
        try {
            DB::beginTransaction();
            $guest = Guest::find($guestId);
            if(!$guest) return;

            $guest->stays()->syncWithoutDetaching([
                $stayId => ['chain_id' => $chainId]
            ]);

            //guardar acceso
            $this->stayAccessService->save($stayId,$guestId);

            //actualizar conteo de huespedes
            $stay = Stay::find($stayId);
            $currentCountGuestsInStay = $stay->guests()->count();
            if($currentCountGuestsInStay > intval($stay->number_guests)){
                $stay->number_guests = $currentCountGuestsInStay;
                $stay->save();
            }
            DB::commit();

            sendEventPusher('private-update-stay-list-hotel.' . $stay->hotel_id, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);

            return [
                'stay' => $stay,
                'guest' => $guest,
            ];

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function deleteGuestOfStay($guestId, $stayId, $hotelId, $chainId)
    {
        try {
            DB::beginTransaction();
            try {
                Log::info('-----deleteGuestOfStay ');

                $guest = Guest::find($guestId);
                Log::info('guest '.json_encode($guest->name));
                $stay = Stay::find($stayId);
                $chatExists = Chat::where('stay_id', $stayId)->where('guest_id', $guestId)->first();
                Log::info('chatExists '.json_encode($chatExists));
                $queryAnsweredExists = Query::where('stay_id', $stayId)
                            ->where('guest_id', $guestId)
                            ->where('answered', 1)
                            ->exists();
                Log::info('queryAnsweredExists '.json_encode($queryAnsweredExists));
                if(intval($stay->number_guests) > 1){
                    $stay->number_guests = intval($stay->number_guests) - 1;
                    $stay->save();
                }

                if($chatExists || $queryAnsweredExists || $guest->complete_checkin_data){
                    Log::info('proceso para huesped con actividad ');
                    // Crear una nueva estancia solo para el huésped
                    $newStay = new Stay();
                    $newStay->hotel_id = $hotelId;
                    $newStay->number_guests = 1;
                    $newStay->language = 'Español';
                    $newStay->check_in = $stay->check_in;
                    $newStay->check_out = $stay->check_out;
                    $newStay->guest_id = $guestId;
                    $newStay->save();

                    // Actualizar StayAccess para que apunte a la nueva estancia
                    StayAccess::where([
                        'stay_id' => $stayId,
                        'guest_id' => $guestId
                    ])->update(['stay_id' => $newStay->id]);

                    // Actualizar la relación guest->stays para que apunte a la nueva estancia
                    // Primero, adjuntar la nueva estancia con los datos del pivot si es necesario
                    $guest->stays()->syncWithoutDetaching([
                        $newStay->id => ['chain_id' => $chainId]
                    ]);

                    // Luego, desasociar la estancia antigua
                    $guest->stays()->detach($stayId);

                    // Actualizar Queries para que apunten a la nueva estancia
                    Query::where('stay_id', $stayId)
                    ->where('guest_id', $guestId)
                    ->update(['stay_id' => $newStay->id]);

                    if($chatExists){
                        Chat::where('stay_id', $stayId)->where('guest_id', $guestId)->update(['stay_id' => $newStay->id]);
                    }
                    //actualizar notas
                    NoteGuest::where('stay_id', $stayId)->where('guest_id', $guestId)->update(['stay_id' => $newStay->id]);

                } else {
                    Log::info('proceso para huesped SIN actividad ');
                    // Eliminar relación
                    $guest->stays()->detach($stayId);
                    // Eliminar acceso
                    $access = StayAccess::where([
                        'stay_id' => $stayId,
                        'guest_id' => $guestId
                    ])->first();
                    if ($access) {
                        $access->delete();
                    }

                    // Eliminar Queries asociadas a la estancia y huésped
                    Query::where('stay_id', $stayId)
                    ->where('guest_id', $guestId)
                    ->delete();

                    // Eliminar Notas asociadas a la estancia y huésped
                    NoteGuest::where('stay_id', $stayId)
                    ->where('guest_id', $guestId)
                    ->delete();
                }

                DB::commit();
                //actualizar lista en el saas
                sendEventPusher('private-logout-webapp-guest.' . $guestId, 'App\Events\LogoutWebappGuest', [
                    'guestId' => $guestId
                ]);
                sendEventPusher('private-update-stay-list-hotel.' . $hotelId, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);
                return true;
            } catch (\Exception $e) {
                DB::rollBack();
                // Manejo de errores según tu lógica
                throw $e;
            }

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function sendContactEmail($data, $guest, $stay, $hotelContactEmail){
        try {
            $contactEmail = ContactEmail::create([
                'stay_id' => $data->stayId,
                'guest_id' => $data->guestId,
                'message' => $data->message
            ]);

            $data = [
                'guestName' => $guest->name.' '.$guest->lastname,
                'guestEmail' => $guest->email,
                'guestLanguageAbbr' => $guest->lang_web,
                'guestLanguageName' => EnumsLanguages::NAME[$guest->lang_web],
                'stayCheckin' => Carbon::parse($stay->check_in)->format('d/m/Y'),
                'stayCheckout' => Carbon::parse($stay->check_out)->format('d/m/Y'),
                'message' => $data->message
            ];
            
            Mail::to($hotelContactEmail)->send(new ContactToHoster($data));
            return $contactEmail;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getContactEmailsByStayId($stayId, $guestId){
        return ContactEmail::where('stay_id', $stayId)->where('guest_id', $guestId)->get();
    }
}
