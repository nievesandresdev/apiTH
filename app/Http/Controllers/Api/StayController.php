<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StayResource;
use App\Models\Guest;
use App\Models\Stay;
use App\Services\GuestService;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\StayService;
class StayController extends Controller
{
    public $service;
    public $guestService; 
    function __construct(
        StayService $_StayService,
        GuestService $_GuestService
    )
    {
        $this->service = $_StayService;
        $this->guestService =  $_GuestService;
    }

    public function findAndValidAccess (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->findAndValidAccess($request->stayId,$hotel,$request->guestId);
            
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findAndValidAccess');
        }
    }

    public function createAndInviteGuest (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            return $model = $this->service->createAndInviteGuest($hotel,$request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.store');
        }
    }

    public function existingStayThenMatchAndInvite(Request $request){
        
        try {
            return $data = new StayResource(Stay::find(76));
            $currentStay = $request->currentStay;  
            $currentGuest = $request->currentGuest;  
            $invitedEmail = $request->invitedEmail;  
            $hotel = $request->attributes->get('hotel');
            $newCurrentStay = $this->service->existingStayThenMatch($currentStay, $currentGuest, $invitedEmail,$hotel);
            //envia invitacion
            // Intenta encontrar un huésped con el correo proporcionado, o lo crea si no existe
            $guest = Guest::firstOrCreate(['email' => $invitedEmail], []);
            $this->guestService->inviteToStayByEmail($guest,$newCurrentStay->id,$hotel);
            if(!$newCurrentStay){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new StayResource($newCurrentStay);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingStayThenMatchOrInvite');
        }
    }

}
