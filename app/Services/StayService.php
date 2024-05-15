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
use App\Models\StayAccess;
use App\Models\StayNotificationSetting;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Mail;

use App\Services\GuestService;
use App\Services\MailService;

class StayService {
    public $mailService;
    public $guestService;
    public $stayAccessService;

    function __construct(
        MailService $_MailService,
        GuestService $_GuestService,
        StayAccessService $_StayAccessService
    )
    {
        $this->mailService = $_MailService;
        $this->guestService = $_GuestService;
        $this->stayAccessService = $_StayAccessService;
    }

    public function findAndValidAccess($stay_id,$hotel,$guestId)
    {
        try {
            $stay = Stay::where('id',$stay_id)->where('hotel_id',$hotel->id)->first();
            $checkoutDate = $stay ? Carbon::parse($stay->check_out) : null;
            // Verifica si han pasado más de 10 días desde el checkout

            if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                //si no han pasado retorna la estancia y guarda el acceso en caso de no existir
                $this->stayAccessService->save($stay->id,$guestId);
                return $stay;
            }
            return null;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function existsAndValidate($stay_id,$hotel)
    {
        try {
            $stay = Stay::where('id',$stay_id)->where('hotel_id',$hotel->id)->first();
            $checkoutDate = $stay ? Carbon::parse($stay->check_out) : null;
            // Verifica si han pasado más de 10 días desde el checkout
            if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                return $stay;
            }
            return null;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function testMail() {

        $hotel = Hotel::find(187);
        $settings =  StayNotificationSetting::where('hotel_id',$hotel->id)->first();
            if(!$settings){
                $settingsArray = settingsNotyStayDefault();
                $settings = (object)$settingsArray;
            }
        $data = [
            'stay_id' => 1,
            'guest_id' => 1,
            'stay_lang' => 'es',
            'msg_text' => $settings->create_msg_email['es'],
            'guest_name' => 'Juan',
            'hotel_name' => $hotel->name,
        ];
        $msg = prepareMessage($data,$hotel,'&subject=invited');
        $link = prepareLink($data,$hotel);
        //dd($msg,$link);
        $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link,false,'',true), 'francisco20990@gmail.com');
        dd('mail enviado');

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
            //guardar acceso
            $this->stayAccessService->save($stay->id,$guestId);

            //enviar mensaje al creador de la estancia
            $settings =  StayNotificationSetting::where('hotel_id',$hotel->id)->first();
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
                $msg = prepareMessage($data,$hotel,'&subject=invited');
                $link = prepareLink($data,$hotel,'&subject=invited');
                // Maiil::to($guest->email)->send(new MsgStay($msg,$hotel));
                $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link), $guest->email);
            }
            DB::commit();
            //adjutar huespedes y enviar correos
            $list_guest = $request->listGuest ?? [];
            foreach($list_guest as $g){
                DB::beginTransaction();
                if($g['email']){
                    $dataGuest = new \stdClass();
                    $dataGuest->name = null;
                    $dataGuest->email = $g['email'];
                    $dataGuest->language = $request->language;
                    $guest = $this->guestService->saveOrUpdate($dataGuest);

                    if($settings->guestinvite_check_email){
                        $stay = $this->existingStayThenMatch($stay->id,$g['email'],$hotel);
                        $data['stay_id'] = $stay->id;
                        $data['guest_id'] = $guest->id;
                        $data['guest_name'] = $guest->name;
                        $data['msg_text'] = $settings->create_msg_email[$guest->lang_web];
                        $msg = prepareMessage($data,$hotel,'&subject=invited');
                        $link = prepareLink($data,$hotel,'&subject=invited');
                        // Maiil::to($guest->email)->send(new MsgStay($msg,$hotel));
                        $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link,true,$guest->name,true), $guest->email);
                    }
                    $guest->stays()->syncWithoutDetaching([$stay->id]);
                    $this->stayAccessService->save($stay->id,$guestId);
                }
                DB::commit();
            }
            //actualizar accesos
            $currentStayAccesses = StayAccess::where('stay_id', $stay->id)
                    ->distinct('guest_id')
                    ->count(['guest_id']);
            $stay = Stay::find($stay->id);
            if($currentStayAccesses > intval($stay->number_guests)){
                $stay->number_guests = $currentStayAccesses;
                $stay->save();
            }

            sendEventPusher('private-create-stay.' . $hotel->id, 'App\Events\CreateStayEvent', null);
            return $stay;

        } catch (\Exception $e) {
            DB::rollback();
            return $e;
        }
    }

    public function existingStayThenMatch($currentStayId,$invitedEmail,$hotel){
        Log::info("existingStayThenMatch");
        if (!$currentStayId || !$invitedEmail || !$hotel) return;
        try {
            $invited = Guest::where('email',$invitedEmail)->first();
            Log::info("invited:".$invited);
            $invitedStay = $this->guestService->findAndValidLastStay($invited->id,$hotel);
            $currentStayData = Stay::find($currentStayId);

            $invitedStayCheckout =  $invitedStay ? $invitedStay->check_out : null;
            $currentStayCheckout =  $currentStayData ? $currentStayData->check_out : null;
            $invitedStayValid = $this->validateCheckoutOfStay($invitedStayCheckout);
            $currentStayValid = $this->validateCheckoutOfStay($currentStayCheckout);
            if(!$invitedStayValid)  $invitedStay = null;
            if(!$currentStayValid)  $currentStayData = null;
            // Log::info("currentStayId:".$currentStayId);
            // Log::info("invitedStay:".$invitedStay);
            if($invitedStay && (intval($invitedStay->id) !== intval($currentStayId))){
                Log::info("matcheo de estancia");
                DB::beginTransaction();
                //suma de accesos entre las dos estancias
                $currentStayAccesses = StayAccess::where('stay_id', $currentStayData->id)
                    ->distinct('guest_id')
                    ->count(['guest_id']);
                $invitedStayAccesses = StayAccess::where('stay_id', $invitedStay->id)
                    ->distinct('guest_id')
                    ->count(['guest_id']);
                $accessesSum = $currentStayAccesses + $invitedStayAccesses;
                //tomo el numero maximo de huespedes añadido entre las dos estancias para actulizar la actual
                //si los accesos son mayores al numero de huespedes guardado se iguala a n de accesos y se actualiza
                $currentNumberGuest = $currentStayData->number_guests ?? 1;
                $invitedNumberGuest = $invitedStay->number_guests ?? 1;
                $maxNumberGuests = max($currentNumberGuest, $invitedNumberGuest);
                if($maxNumberGuests < $accessesSum){
                    $maxNumberGuests = $accessesSum;
                }
                $invitedStay->number_guests = $maxNumberGuests;
                $invitedStay->save();
                //relacionar huespedes actuales a la estancia del invitado
                $currentStayData->guests()->update(['stay_id'=> $invitedStay->id]);
                //relacionar accesos actuales a la estancia del invitado
                $currentStayData->accesses()->update(['stay_id'=> $invitedStay->id]);
                //eliminar estancia
                $currentStayData->delete();
                //retorna la estancia del invitado como nueva estancia para la sesion actual
                DB::commit();
                return $invitedStay;
            }else{
                Log::info("creacion de acccesos para estancia");
                //agregar acceso del invitado
                if($currentStayData){
                    $this->stayAccessService->save($currentStayData->id,$invited->id);
                    //agregar relacion a estancia
                    $invited->stays()->syncWithoutDetaching([$currentStayData->id]);
                }
            }
            Log::info("currentStayData:".$currentStayData);
            return $currentStayData;
        } catch (\Exception $e) {
            DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingStayThenMatch');
        }
    }

    public function getGuests($stayId){

        try{
            $stay = Stay::find($stayId);
            $guests = $stay->guests()->get();
            return $guests;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGuests');
        }
    }

    public function updateStayData($request){

        try{
            $stay = Stay::find($request->stayId);
            $stay->room = $request->room;
            $stay->number_guests = $request->numberGuests;
            $stay->check_in = $request->checkDate['start'];
            $stay->check_out = $request->checkDate['end'];
            $stay->save();

            return $stay;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateStayData');
        }

    }

    public function deleteGuestOfStay($stayId,$guestId){
        try{
            $stay = Stay::find($stayId);
            if($guestId && $stay){
                DB::beginTransaction();
                //eliminar relacion a estancia
                $stay->guests()->detach($guestId);
                //elimniar accesos
                StayAccess::where('stay_id',$stayId)->where('guest_id',$guestId)->delete();
                //disminuir n huespdes
                // $stay->number_guests = (intval($stay->number_guests)-1);
                // $stay->save();
                DB::commit();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            DB::rollback();
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteGuestOfStay');
        }
    }

    public function validateCheckoutOfStay($check_out){
        try {
            if(!$check_out) return false;
            $checkoutDate = Carbon::parse($check_out);
            // Verifica si han pasado más de 10 días desde el checkout
            if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                //si no han pasado retorna la estancia y guarda el acceso en caso de no existir
                return true;
            }
            return false;

        } catch (\Exception $e) {
            return $e;
        }
    }

}
