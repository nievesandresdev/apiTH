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

class GuestService {

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
            $lang = $data->language ?? 'es';

            $guest = Guest::where('email',$email)->first();
            if(!$guest){
                $guest = Guest::create([
                    'name' =>$name,
                    'email' => $email,
                    'lang_web' => $lang
                ]);
            }else{
                $guest->name = $name;
                $guest->lang_web = $lang;
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
        Log::info("inviteToStayByEmail settings".json_encode($settings));
        if($settings->guestinvite_check_email){
            Log::info("inviteToStayByEmail entro en envio");
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
            Log::info("inviteToStayByEmail prepareMessage".$msg);
            Log::info("inviteToStayByEmail hotel".json_encode($hotel));
            // Maiil::to($guest->email)->send(new MsgStay($msg,$hotel));  
            $this->mailService->sendEmail(new MsgStay($msg,$hotel), $guest->email);  
        }
    }

    public function updateById($data){
        if(!$data->id) return;

        try{
            $guest = Guest::find($data->id);
            $guest->name = $data->name ?? $guest->name;
            $guest->email = $data->email ?? $guest->email;
            $guest->phone = $data->phone ?? $guest->phone;
            $guest->lang_web = $data->lang_web ?? $guest->lang_web;
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
            // Maiil::to($guestEmail)->send(new MsgStay($msg,$hotel));
            $this->mailService->sendEmail(new MsgStay($msg,$hotel), $guestEmail);
            //
            return  true;   
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.sendEmail');
        }
    }
}
