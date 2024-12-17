<?php

namespace App\Services;

use App\Jobs\Stay\SendEmailGuest;
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
use App\Services\Hoster\UtilsHosterServices;
use App\Services\MailService;

class StayService {
    public $mailService;
    public $guestService;
    public $stayAccessService;
    public $utilsHosterServices;
    public $querySettingsServices;
    public $utilityService;

    function __construct(
        MailService $_MailService,
        GuestService $_GuestService,
        StayAccessService $_StayAccessService,
        UtilsHosterServices $_utilsHosterServices,
        QuerySettingsServices $_QuerySettingsServices,
        UtilityService $_UtilityService
    )
    {
        $this->mailService = $_MailService;
        $this->guestService = $_GuestService;
        $this->stayAccessService = $_StayAccessService;
        $this->utilsHosterServices = $_utilsHosterServices;
        $this->querySettingsServices = $_QuerySettingsServices;
        $this->utilityService = $_UtilityService;
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

        // $hotel = Hotel::find(187);
        // $settings =  StayNotificationSetting::where('hotel_id',$hotel->id)->first();
        //     if(!$settings){
        //         $settingsArray = settingsNotyStayDefault();
        //         $settings = (object)$settingsArray;
        //     }
        // $data = [
        //     'stay_id' => 1,
        //     'guest_id' => 1,
        //     'stay_lang' => 'es',
        //     'msg_text' => $settings->create_msg_email['es'],
        //     'guest_name' => 'Juan',
        //     'hotel_name' => $hotel->name,
        // ];
        // $msg = prepareMessage($data,$hotel,'&subject=invited');
        // $link = prepareLink($data,$hotel);
        // //dd($msg,$link);
        // $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link,false,'',true), 'francisco20990@gmail.com');
        // dd('mail enviado');

    }

    public function createAndInviteGuest($hotel, $chainSubdomain, $request)
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
                'check_out' => $request->checkDate['end'],
                'guest_id' => $guest->id,
            ]);
            // $guest->stays()->syncWithoutDetaching([$stay->id]);
            $guest->stays()->syncWithoutDetaching([
                $stay->id => ['chain_id' => $hotel->chain_id]
            ]);
            
            //guardar acceso
            $this->stayAccessService->save($stay->id,$guestId);

            //enviar mensaje al creador de la estancia
            $settings =  StayNotificationSetting::where('hotel_id',$hotel->id)->first();
            if(!$settings){
                $settingsArray = settingsNotyStayDefault();
                $settings = (object)$settingsArray;
            }


            // $data = [
            //     'stay_id' => $stay->id,
            //     'guest_id' => $guest->id,
            //     'stay_lang' => $guest->lang_web,
            //     'msg_text' => $settings->guestcreate_msg_email[$guest->lang_web],
            //     'guest_name' => $guest->name,
            //     'hotel_name' => $hotel->name,
            //     'hotel_id' => $hotel->id,
            // ];
            if($settings->guestcreate_check_email){
                // $msg = prepareMessage($data,$hotel,'&subject=invited');
                // $link = prepareLink($data,$hotel,'&subject=invited');
                // Maiil::to($guest->email)->send(new MsgStay($msg,$hotel));
                // $this->guestWelcomeEmail('welcome', $chainSubdomain, $hotel, $guest, $stay);
                SendEmailGuest::dispatch('welcome', $chainSubdomain, $hotel, $guest, $stay);
            }

            $colorsExists = $stay->guests()->select('color')->pluck('color');
            $color = $this->guestService->updateColorGuestForStay($colorsExists);
            if($color){
                // Log::info('se agregar el color al huesped '.$color);
                $guest->color = $color;
                $guest->save();
            }
            Log::info('stay 1'.json_encode($stay));
            DB::commit();
            //por ahora ya no se invitan huespedes
            //adjutar huespedes y enviar correos
            // $list_guest = $request->listGuest ?? [];
            // foreach($list_guest as $g){
            //     DB::beginTransaction();
            //     if($g['email']){
            //         $dataGuest = new \stdClass();
            //         $dataGuest->name = null;
            //         $dataGuest->email = $g['email'];
            //         $dataGuest->language = $request->language;
            //         $guest = $this->guestService->saveOrUpdate($dataGuest);

            //         if($settings->guestinvite_check_email){
            //             $stay = $this->existingStayThenMatch($stay->id,$g['email'],$hotel);
            //             $data['stay_id'] = $stay->id;
            //             $data['guest_id'] = $guest->id;
            //             $data['guest_name'] = $guest->name;
            //             $data['msg_text'] = $settings->create_msg_email[$guest->lang_web];
            //             $msg = prepareMessage($data,$hotel,'&subject=invited');
            //             $link = prepareLink($data,$hotel,'&subject=invited');
            //             // Maiil::to($guest->email)->send(new MsgStay($msg,$hotel));
            //             $this->mailService->sendEmail(new MsgStay($msg,$hotel,$link,true,$guest->name,true), $guest->email);
            //         }
            //         $guest->stays()->syncWithoutDetaching([$stay->id]);
            //         $this->stayAccessService->save($stay->id,$guestId);
            //         $colorsExists = $stay->guests()->select('color')->pluck('color');
            //         Log::info('agregado colors '.json_encode($colorsExists));
            //         $color = $this->guestService->updateColorGuestForStay($colorsExists);
            //         if($color){
            //             Log::info('se agregar el color al huesped agregado '.$color);
            //             $guest->color = $color;
            //             $guest->save();
            //         }
            //     }
            //     DB::commit();
            // }
            //actualizar accesos
            // $currentStayAccesses = StayAccess::where('stay_id', $stay->id)
            //         ->distinct('guest_id')
            //         ->count(['guest_id']);
            // $stay = Stay::find($stay->id);
            // if($currentStayAccesses > intval($stay->number_guests)){
            //     $stay->number_guests = $currentStayAccesses;
            //     $stay->save();
            // }
            Log::info('stay 2'.json_encode($stay));
            // $stay->refresh();
            // Log::info('stay 3'.json_encode($stay));
            // sendEventPusher('private-create-stay.' . $hotel->id, 'App\Events\CreateStayEvent', null);
            sendEventPusher('private-update-stay-list-hotel.' . $hotel->id, 'App\Events\UpdateStayListEvent', ['showLoadPage' => false]);
            return $stay;

        } catch (\Exception $e) {
            Log::error('Error service createAndInviteGuest: ' . $e->getMessage());
            DB::rollback();
            return $e;
        }
    }

    // public function existingStayThenMatch($currentStayId,$invitedEmail,$hotel){
    //     Log::info("existingStayThenMatch");
    //     if (!$currentStayId || !$invitedEmail || !$hotel) return;
    //     try {
    //         $invited = Guest::where('email',$invitedEmail)->first();
    //         Log::info("invited:".$invited);
    //         $invitedStay = $this->guestService->findAndValidLastStay($invited->id,$hotel);
    //         $currentStayData = Stay::find($currentStayId);

    //         $invitedStayCheckout =  $invitedStay ? $invitedStay->check_out : null;
    //         $currentStayCheckout =  $currentStayData ? $currentStayData->check_out : null;
    //         $invitedStayValid = $this->validateCheckoutOfStay($invitedStayCheckout);
    //         $currentStayValid = $this->validateCheckoutOfStay($currentStayCheckout);
    //         if(!$invitedStayValid)  $invitedStay = null;
    //         if(!$currentStayValid)  $currentStayData = null;
    //         // Log::info("currentStayId:".$currentStayId);
    //         // Log::info("invitedStay:".$invitedStay);
    //         if($invitedStay && (intval($invitedStay->id) !== intval($currentStayId))){
    //             Log::info("matcheo de estancia");
    //             DB::beginTransaction();
    //             //suma de accesos entre las dos estancias
    //             $currentStayAccesses = StayAccess::where('stay_id', $currentStayData->id)
    //                 ->distinct('guest_id')
    //                 ->count(['guest_id']);
    //             $invitedStayAccesses = StayAccess::where('stay_id', $invitedStay->id)
    //                 ->distinct('guest_id')
    //                 ->count(['guest_id']);
    //             $accessesSum = $currentStayAccesses + $invitedStayAccesses;
    //             //tomo el numero maximo de huespedes añadido entre las dos estancias para actulizar la actual
    //             //si los accesos son mayores al numero de huespedes guardado se iguala a n de accesos y se actualiza
    //             $currentNumberGuest = $currentStayData->number_guests ?? 1;
    //             $invitedNumberGuest = $invitedStay->number_guests ?? 1;
    //             $maxNumberGuests = max($currentNumberGuest, $invitedNumberGuest);
    //             if($maxNumberGuests < $accessesSum){
    //                 $maxNumberGuests = $accessesSum;
    //             }
    //             $invitedStay->number_guests = $maxNumberGuests;
    //             $invitedStay->save();
    //             //relacionar huespedes actuales a la estancia del invitado
    //             $currentStayData->guests()->update(['stay_id'=> $invitedStay->id]);
    //             //relacionar accesos actuales a la estancia del invitado
    //             $currentStayData->accesses()->update(['stay_id'=> $invitedStay->id]);
    //             //relacionar queries actuales a la estancia del invitado
    //             $currentStayData->queries()->update(['stay_id'=> $invitedStay->id]);
    //             //relacionar chats actuales a la estancia del invitado
    //             $currentStayData->chats()->update(['stay_id'=> $invitedStay->id]);
    //             //relacionar notas actuales a la estancia del invitado
    //             $currentStayData->notes()->update(['stay_id'=> $invitedStay->id]);
    //             //relacionar notas de huespedes de la estancia actual a la estancia del invitado
    //             $currentStayData->guestNotes()->update(['stay_id'=> $invitedStay->id]);
    //             //eliminar estancia
    //             $currentStayData->delete();
    //             //retorna la estancia del invitado como nueva estancia para la sesion actual
    //             DB::commit();
    //             return $invitedStay;
    //         }else{
    //             Log::info("creacion de acccesos para estancia");
    //             //agregar acceso del invitado
    //             if($currentStayData){
    //                 $this->stayAccessService->save($currentStayData->id,$invited->id);
    //                 //agregar relacion a estancia
    //                 $invited->stays()->syncWithoutDetaching([$currentStayData->id]);
    //                 $colorsExists = $currentStayData->guests()->select('color')->pluck('color');
    //                 $color = $this->guestService->updateColorGuestForStay($colorsExists);
    //                 if($color){
    //                     Log::info('se agregar el color al huesped invitado'.$color);
    //                     $invited->color = $color;
    //                     $invited->save();
    //                 }
    //             }
    //         }
    //         Log::info("currentStayData:".$currentStayData);
    //         return $currentStayData;
    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingStayThenMatch');
    //     }
    // }

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
            $stay->room = $request->room ?? $stay->room;
            $stay->number_guests = $request->numberGuests ?? $stay->number_guests;
            $stay->middle_reservation = $request->middle_reservation ?? $stay->middle_reservation;
            $stay->check_in = $request->checkDate['start'] ?? $stay->check_in;
            $stay->check_out = $request->checkDate['end'] ?? $stay->check_out;
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

    public function findbyId($stayId)
    {
        try {
            $stay = Stay::find($stayId);
            return $stay;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getCurrentPeriod($hotel, $stay) {
        try {
            
            $dayCheckin = $stay->check_in;
            $dayCheckout = $stay->check_out;
            $hourCheckin = $hotel->checkin ?? '16:00';

            // Crear objeto Carbon para check-in
            $checkinDateTimeString = $dayCheckin . ' ' . $hourCheckin;
            $checkinDateTime = Carbon::createFromFormat('Y-m-d H:i', $checkinDateTimeString);

            // período in-stay
            // $inStayStart = (clone $checkinDateTime)->addDay()->setTime(5, 0);
            $hideStart = Carbon::createFromFormat('Y-m-d', $dayCheckout);

             // período post-stay
            $postStayStart = Carbon::createFromFormat('Y-m-d H:i', $dayCheckout . ' 05:00');
            $postStayEnd = (clone $hideStart)->addDays(10);

            //fecha actual
            $now = Carbon::now();
            if ($now->lessThan($checkinDateTime)) {
                return 'pre-stay';
            }
            // if ($now->greaterThanOrEqualTo($inStayStart) && $now->lessThan($hideStart)) {
            if ($now->greaterThan($checkinDateTime) && $now->lessThan($hideStart)) {
                return 'in-stay';
            }
            if ($now->greaterThanOrEqualTo($postStayStart) && $now->lessThanOrEqualTo($postStayEnd)) {
                return 'post-stay';
            }
             // Nueva condición para verificar si han pasado más de 10 días después del checkout
            if ($now->greaterThan($postStayEnd)) {
                return 'invalid-stay';
            }
            return null;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function calculateHoursAfterCheckin($hotel, $stay)
    {
        try {
            // Obtener la fecha de check-in (día)
            $dayCheckin = $stay->check_in;
            // Obtener la hora de check-in del hotel, o por defecto '16:00'
            $hourCheckin = $hotel->checkin ?? '16:00';

            // Crear objeto Carbon para la fecha y hora completa de check-in
            $checkinDateTimeString = $dayCheckin . ' ' . $hourCheckin;
            $checkinDateTime = Carbon::createFromFormat('Y-m-d H:i', $checkinDateTimeString);

            // Si el check-in es en el futuro, devolver 0
            if (Carbon::now()->lessThan($checkinDateTime)) {
                return 0;
            }

            // Calculamos las horas transcurridas desde el check-in hasta ahora
            $hoursPassed = $checkinDateTime->diffInHours(Carbon::now());

            return $hoursPassed;

        } catch (\Exception $e) {
            // En caso de error, retornamos la excepción (o podrías manejarla a conveniencia)
            return $e;
        }
    }

    public function guestWelcomeEmail($type, $chainSubdomain, $hotel, $guest, $stay = null)
    {
        try {
            $checkData = [];
            $queryData = [];
            //stay section
            if($type == 'welcome'){
                if($stay->check_in && $stay->check_out){
                    $formatCheckin = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_in);
                    $formatCheckout = $this->utilsHosterServices->formatDateToDayWeekDateAndMonth($stay->check_out);
                }
                $webappEditStay = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'editar-estancia/'.$stay->id);
                //

                $checkData = [
                    'title' => "Datos de tu estancia en {$hotel->name}",
                    'formatCheckin' => $formatCheckin,
                    'formatCheckout' => $formatCheckout,
                    'editStayUrl' => $webappEditStay
                ];
            }

        //     //query section
            if($type == 'welcome'){
                $currentPeriod = $this->getCurrentPeriod($hotel, $stay);    
                $querySettings = $this->querySettingsServices->getAll($hotel->id);
                $hoursAfterCheckin = $this->calculateHoursAfterCheckin($hotel, $stay);
                $showQuerySection = true;
                if(
                    $currentPeriod == 'pre-stay' && !$querySettings->pre_stay_activate || 
                    $currentPeriod == 'in-stay' && $hoursAfterCheckin < 24 ||
                    $currentPeriod == 'post-stay'
                ){
                    $showQuerySection = false;
                }    
                //        
                $webappLinkInbox = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'inbox');
                $webappLinkInboxGoodFeel = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'inbox',"e={$stay->id}&g={$guest->id}&fill=VERYGOOD");

                $queryData = [
                    'showQuerySection' => $showQuerySection,
                    'currentPeriod' => $currentPeriod,
                    'webappLinkInbox' => $webappLinkInbox,
                    'webappLinkInboxGoodFeel' => $webappLinkInboxGoodFeel,
                    
                ];
            }

            $urlWebapp = buildUrlWebApp($chainSubdomain, $hotel->subdomain);

            //
            $webappChatLink = buildUrlWebApp($chainSubdomain, $hotel->subdomain,'chat');
            //

            $crosselling = $this->utilityService->getCrossellingHotelForMail($hotel, $chainSubdomain);
            //
            // $urlQr = generateQr($hotel->subdomain, $urlWebapp);
            $urlQr = "https://thehosterappbucket.s3.eu-south-2.amazonaws.com/test/qrcodes/qr_nobuhotelsevillatex.png";

            $dataEmail = [
                'checkData' => $checkData,
                'queryData' => $queryData,
                'places' => $crosselling['places'],
                'experiences' => $crosselling['experiences'],
                'facilities' => $crosselling['facilities'],
                'webappChatLink' => $webappChatLink,
                'urlQr' => $urlQr,
            ];
            Log::info('dataEmail '.json_encode($dataEmail));
            Log::info('hotelid '.json_encode($hotel->id));
            Log::info('guest '.json_encode($guest));
            $this->mailService->sendEmail(new MsgStay($type, $hotel, $guest, $dataEmail), "andresdreamerf@gmail.com");

        } catch (\Exception $e) {
            Log::error('Error service guestWelcomeEmail: ' . $e->getMessage());
            DB::rollback();
            return $e;
        }
    }
}
