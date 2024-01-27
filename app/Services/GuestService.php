<?php

namespace App\Services;

use App\Mail\Guest\MsgStay;
use App\Models\Guest;
use App\Models\StayNotificationSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class GuestService {
    public $stayAccessService;

    function __construct(
        StayAccessService $__StayAccessService
    )
    {
        $this->stayAccessService = $__StayAccessService;
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
            $lang = $data->language;

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

    public function findLastStay($id,$hotel){
        
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
                    $this->stayAccessService->save($last_stay,$guest->id);
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
        $user = $hotel->user()->first();
        $user_id = $user->id;
        $settings =  StayNotificationSetting::where('user_id',$user_id)->first();
        if(!$settings){
            $settingsArray = settingsNotyStayDefault();
            $settings = (object)$settingsArray;
        }
        if($settings->guestcreate_check_email){
            $data = [
                'stay_id' => $stayId,
                'guest_id' => $guest->id,
                'stay_lang' => $guest->lang_web,
                'msg_text' => $settings->guestcreate_msg_email[$guest->lang_web],
                'guest_name' => $guest->name,
                'hotel_name' => $hotel->name,
                'hotel_id' => $hotel->id,
            ];
            $msg = prepareMessage($data,$hotel);
            Mail::to($guest->email)->send(new MsgStay($msg,$hotel));    
        }
    }
}