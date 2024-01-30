<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Http\Resources\StayResource;
use App\Models\Guest;
use App\Models\Stay;
use App\Services\GuestService;
use App\Services\StayAccessService;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\StayService;
class StayController extends Controller
{
    public $service;
    public $guestService; 
    public $stayAccessService; 
    function __construct(
        StayService $_StayService,
        GuestService $_GuestService,
        StayAccessService $_stayAccessService
    )
    {
        $this->service = $_StayService;
        $this->guestService =  $_GuestService;
        $this->stayAccessService = $_stayAccessService;
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
            $model = $this->service->createAndInviteGuest($hotel,$request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.createAndInviteGuest');
        }
    }

    public function existingStayThenMatchAndInvite(Request $request){
        
        try {
            $currentStay = $request->currentStay;  
            $currentGuest = $request->currentGuest;  
            $invitedEmail = $request->invitedEmail;  
            $hotel = $request->attributes->get('hotel');
            // Intenta encontrar un huésped con el correo proporcionado, o lo crea si no existe
            $invited = Guest::firstOrCreate(['email' => $invitedEmail], []);
            $newCurrentStay = $this->service->existingStayThenMatch($currentStay, $currentGuest, $invitedEmail,$hotel);
            //agregar acceso del invitado
            $this->stayAccessService->save($newCurrentStay,$invited->id);
            //envia invitacion
            $this->guestService->inviteToStayByEmail($invited,$newCurrentStay->id,$hotel);
            if(!$newCurrentStay){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new StayResource($newCurrentStay);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingStayThenMatchAndInvite');
        }
    }

    public function getGuestsAndSortByCurrentguestId($stayId, $guestId)
    {
        try {
            $data = ['message' => __('response.bad_request_long')];
            if(!$stayId && !$guestId) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            $guests = $this->service->getGuests($stayId);

            // Separa la colección en dos: uno con el guestId y otro con los restantes.
            list($selectedGuest, $otherGuests) = $guests->partition(function ($guest) use ($guestId) {
                return $guest->id == $guestId;
            });

            // Combina el invitado seleccionado con el frente de los otros invitados.
            $sortedGuests = $selectedGuest->concat($otherGuests)->values();

            $data = GuestResource::collection($sortedGuests);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGuestsAndSortByCurrentguestId');
        }
    }

    public function updateStayAndGuests(Request $request){
        try {
            $updateStay = $this->service->updateStayData($request);
            $data = ['message' => __('response.bad_request_long')];
            if(!$updateStay) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            foreach($request->listGuest as $g){
                $data = (object) $g;
                $updateGuest = $this->guestService->updateById($data);
                $data = ['message' => __('response.bad_request_long')];
                if(!$updateGuest) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, true);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateStayAndGuests');
        }

    }

    
    public function deleteGuestOfStay($stayId, $guestId){
        try{
            $delete = $this->service->deleteGuestOfStay($stayId, $guestId);
            $data = ['message' => __('response.bad_request_long')];
            if(!$delete) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            return bodyResponseRequest(EnumResponse::ACCEPTED, true);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.deleteGuestOfStay');
        }
    }
    

}
