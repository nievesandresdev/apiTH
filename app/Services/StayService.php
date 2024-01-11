<?php

namespace App\Services;

use App\Mail\Guest\MsgStay;
use App\Models\Guest;
use App\Models\hotel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Stay;
use App\Models\StayNotificationSetting;
use App\Services\GuestService;
use Illuminate\Support\Facades\Mail;

class StayService {
    
    public $guestService;

    function __construct(
        GuestService $_GuestService
    )
    {
        $this->guestService = $_GuestService;
    }

    public function findAndValidAccess($stay_id,$hotel)
    {
        try {
            $stay = Stay::where('id',$stay_id)->where('hotel_id',$hotel->id)->first();
            $checkoutDate = $stay ? Carbon::parse($stay->check_out) : null;
            // Verifica si han pasado más de 10 días desde el checkout

            if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                //si no han pasado retorna la estancia
                return $stay;
            }
            return null;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function createAndInviteGuest($hotel,$request)
    {
        try {
            DB::beginTransaction();
            $guestId = $request->guestId;
            
            $guest = Guest::find($guestId);
            
            $langs = [
                'en' => 'Inglés',
                'es' => 'Español',
                'fr' => 'Francés'
            ];
            
            $stay = Stay::create([
                'hotel_id' =>$hotel->id,
                'number_guests' => $request->numberGuests,
                'language' => $langs[$request->language],
                'check_in' => $request->checkDate['start'],
                'check_out' => $request->checkDate['end']
            ]);
            
            $guest->stays()->syncWithoutDetaching([$stay->id]);
            
            
            //enviar mensaje al creador de la estancia
            $user = $hotel->user()->first();
            $user_id = $user->id;
            $settings =  StayNotificationSetting::where('user_id',$user_id)->first();
            if(!$settings){
                $settingsArray = settingsNotyStayDefault();
                $settings = (object)$settingsArray;
            }
            
            $data = [
                'stay_id' => $stay->id,
                'guest_id' => $guest->id,
                'stay_lang' => $guest->lang_web,
                'msg_text' => $settings->guestcreate_msg_email[$guest->lang_web],
                'guest_name' => $guest->name,
                'hotel_name' => $hotel->name,
                'hotel_id' => $hotel->id,
            ];
            
            if($settings->guestcreate_check_email){
                $msg = prepareMessage($data,$hotel);
                // Mail::to($guest->email)->send(new MsgStay($msg,$hotel));    
            }
            
            //adjutar huespedes y enviar correos
            $list_guest = $request->listGuest;
            
            // foreach($list_guest as $g){
                
            //     if($g['email']){
            //         $data = collect([
            //             "name" => null,
            //             "email" => $g['email'],
            //             "language" => $request->language
            //         ]);
                    
            //         $guest = $this->guestService->saveOrUpdate($data);
                    
            //         $guest->stays()->syncWithoutDetaching([$stay->id]);
            //         if($settings->guestinvite_check_email){
            //             $data['guest_id'] = $guest->id;
            //             $data['guest_name'] = $guest->name;
            //             $data['msg_text'] = $settings->guestinvite_msg_email[$guest->lang_web];
            //             $msg = prepareMessage($data,$hotel,'&subject=invited');
            //             // Mail::to($guest->email)->send(new MsgStay($msg,$hotel));    
            //         }
            //     }
            // }
            
            // sendEventPusher('private-create-stay.' . $hotel->id, 'App\Events\CreateStayEvent', null);
            DB::commit();
            return $stay;
            
        } catch (\Exception $e) {
            return $e;
        }
    }

    

    
}