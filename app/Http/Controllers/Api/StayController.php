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
use Illuminate\Support\Facades\Log;
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

    public function existsAndValidate (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->existsAndValidate($request->stayId,$hotel);

            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existsAndValidate');
        }
    }

    public function createAndInviteGuest (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $model = $this->service->createAndInviteGuest($hotel, $chainSubdomain, $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            Log::error('Error creating and inviting guest: ' . $e->getMessage());
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), [], self::class . '.createAndInviteGuest');
        }
    }

    public function existingStayThenMatchAndInvite(Request $request){

        try {
            $currentStay = $request->currentStay;
            $invitedEmail = $request->invitedEmail;
            $hotel = $request->attributes->get('hotel');
            // Intenta encontrar un huésped con el correo proporcionado, o lo crea si no existe
            $dataGuest = new \stdClass();
            $dataGuest->name = null;
            $dataGuest->email = $invitedEmail;
            $dataGuest->language = $hotel->language_default_webapp;
            $invited = $this->guestService->saveOrUpdate($dataGuest);
            $newCurrentStay = $this->service->existingStayThenMatch($currentStay, $invitedEmail, $hotel);
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

    public function existingThenMatchOrSave(Request $request){

        try {
            $stayId = $request->stayId;
            $guestEmail = $request->guestEmail;
            $hotel = $request->attributes->get('hotel');
            // Intenta encontrar un huésped con el correo proporcionado, o lo crea si no existe
            $newCurrentStay = $this->service->existingStayThenMatch($stayId, $guestEmail, $hotel);
            if(!$newCurrentStay){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new StayResource($newCurrentStay);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingThenMatchOrSave');
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

    public function getGuestsAndSortByAccess($stayId)
    {
        try {
            $data = ['message' => __('response.bad_request_long')];
            if(!$stayId) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            $guests = $this->service->getGuestsAndSortByAccess($stayId);

            $data = GuestResource::collection($guests);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGuestsAndSortByAccess');
        }
    }

    public function updateStayAndGuests(Request $request){
        try {
            $updateStay = $this->service->updateStayData($request);
            $data = ['message' => __('response.bad_request_long')];
            if(!$updateStay) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            // //actualizar huespedes
            // foreach($request->listGuest as $g){
            //     $data = (object) $g;
            //     $updateGuest = $this->guestService->updateById($data);
            //     $data = ['message' => __('response.bad_request_long')];
            //     if(!$updateGuest) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            // }
            // //eliminar huespedes
            // $deleteList = $request->deleteList;
            // foreach ($deleteList as $g) {
            //     $this->service->deleteGuestOfStay($request->stayId,$g);
            // }
            $response = new StayResource($updateStay);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $response);
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

    public function testMail()
    {
        try {
            $this->service->testMail();
            return bodyResponseRequest(EnumResponse::ACCEPTED, true);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.testMail');
        }
    }

    public function findbyId($stayId)
    {
        try {
            $stay = $this->service->findbyId($stayId);
            $data = ['message' => __('response.bad_request_long')];
            if(!$stay) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            $stay = new StayResource($stay);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $stay);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findbyId');
        }
    }

}
