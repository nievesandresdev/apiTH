<?php

namespace App\Services;

use App\Mail\Guest\MsgStay;
use App\Models\Guest;
use App\Models\hotel;
use App\Models\StayNotificationSetting;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use App\Services\MailService;
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

    public function saveOrUpdate($data)
    {
        try {
            $email = $data->email;
            $name = $data->name;
            $phone = $data->phone;
            $lang = $data->language ?? 'es';

            $guest = Guest::where('email',$email)->first();
            
            $acronym = $this->generateInitialsName($name ?? $email);
            if(!$guest){
                $guest = Guest::create([
                    'name' =>$name,
                    'email' => $email,
                    'lang_web' => $lang,
                    'acronym' => $acronym,
                    'phone' => $phone ?? null,
                ]);
            }else{
                $guest->name = $name;
                $guest->lang_web = $lang;
                $guest->phone = $phone ?? $guest->phone;
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

    public function findLastStayAndAccess($id,$hotel){

        try {
            $guest = Guest::find($id);
            $last_stay = $guest->stays()
                        ->where('hotel_id',$hotel->id)
                        ->orderBy('check_out','DESC')->first();

            if($last_stay){
                $checkoutDate = $last_stay ? Carbon::parse($last_stay->check_out) : null;
                // Verifica si han pasado más de 10 días desde el checkout


                if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                    //si no han pasado retorna la estancia
                    $this->stayAccessService->save($last_stay->id,$guest->id);

                    return $last_stay;
                }
            }
            return null;
        } catch (\Exception $e) {
            return $e;
        }
    }


    public function findAndValidLastStay($guestId,$hotel){

        try {
            if(!$guestId) return;
            $guest = Guest::find($guestId);
            $last_stay = $guest->stays()
                        ->where('hotel_id',$hotel->id)
                        ->orderBy('check_out','DESC')->first();
            if($last_stay){
                $checkoutDate = $last_stay ? Carbon::parse($last_stay->check_out) : null;
                // Verifica si han pasado más de 10 días desde el checkout
                if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                    //si no han pasado retorna la estancia
                    return $last_stay;
                }
            }
            return null;
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

    public function updateById($data){
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

            $guest->name = $name;
            $guest->email = $data->email ?? $guest->email;
            $guest->phone = $data->phone ?? $guest->phone;
            $guest->lang_web = $data->lang_web ?? $guest->lang_web;
            $guest->acronym = $acronym;
            $guest->save();
            return $guest;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateById');
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
        Log::info('$colors '.json_encode($colors));
        Log::info('$colorsExistsArray '.json_encode($colorsExistsArray));

        // Filtrar colores para encontrar aquellos que no están en $colorsExistsArray
        $availableColors = array_diff($colors, $colorsExistsArray);
        Log::info('$availableColors '.json_encode($availableColors));

        // Verificar si hay colores disponibles
        if (!empty($availableColors)) {
            Log::info('if');
            Log::info('if '.$availableColors[array_rand($availableColors)]);
            // Seleccionar un color al azar de los colores disponibles
            return $availableColors[array_rand($availableColors)];
        } else {
            Log::info('else');
            Log::info('else '.$colors[array_rand($colors)]);
            // Todos los colores están en uso, seleccionar uno al azar de la lista total
            return $colors[array_rand($colors)];
        }
    }


}
