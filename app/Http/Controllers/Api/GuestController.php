<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Http\Resources\StayResource;
use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Utils\Enums\EnumResponse;
use App\Services\GuestService;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Google\Client as GoogleClient;

class GuestController extends Controller
{
    public $service;

    function __construct(
        GuestService $_GuestService
    )
    {
        $this->service = $_GuestService;
    }

    public function findById ($id) {
        try {
            $model = $this->service->findById($id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findById');
        }
    }

    public function saveOrUpdate (Request $request) {
        try {
            $model = $this->service->saveOrUpdate($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveOrUpdate');
        }
    }

    public function updateLanguage (Request $request) {
        try {
            $model = $this->service->updateLanguage($request);
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveOrUpdate');
        }
    }

    public function findLastStay($id,Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->findLastStayAndAccess($id,$hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findLastStay');
        }
    }


    public function findAndValidLastStay(Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $hotelId = $hotel->id ?? null;
            $chainId = $request->chainId;
            $guestId = $request->guestId;

            $model = $this->service->findAndValidLastStay($guestId, $hotelId, $chainId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findLastStay');
        }
    }

    public function sendMailTo(Request $request){
        $stayId = $request->stayId;
        $guestId = $request->guestId;
        $guestEmail = $request->guestEmail;
        $hotelId = $request->attributes->get('hotel')->id;

        $data = ['message' => __('response.bad_request_long')];
        if(!$stayId || !$guestId || !$guestEmail) return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);

        $sent = $this->service->sendEmail($stayId,$guestId,$guestEmail,$hotelId);
        return bodyResponseRequest(EnumResponse::ACCEPTED, $sent);
    }

    public function findByEmail (Request $request) {
        try {

            $model = $this->service->findByEmail($request->email);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = new GuestResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findByEmail');
        }
    }

    public function updatePasswordGuest(Request $request)
    {
        try {
            $response = $this->service->updateDataGuest($request);


            if (!$response['valid_password']) {
                $data = [
                    'message' => __('response.invalid_password')
                ];
                return bodyResponseRequest(EnumResponse::BAD_REQUEST, $data);
            }

            // Si la contraseña es correcta, devuelve el recurso actualizado
            $data = $response['data'];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, $e->getMessage(), $e->getMessage());
        }
    }


    public function updateDataGuest(Request $request) {
        $guest = Guest::find($request->id);

        if (!$guest) {
            $data = [
                'message' => __('response.bad_request_long')
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }

        if ($request->hasFile('avatar')) {
            if ($guest->avatar) {
                // Elimina el avatar antiguo si existe
                Storage::delete($guest->avatar);
            }

            // Guarda el nuevo avatar usando el helper
            $guest->avatar = saveImage($request->file('avatar'), 'guest-avatar', null, null, false, null);
        }

        $model = $this->service->updateDataGuest($guest, $request);

        if (!$model) {
            $data = [
                'message' => __('response.bad_request_long')
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }

        $data = new GuestResource($model);
        return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
    }

    public function createAccessInStay(Request $request) {
        try {
            $guestId = $request->guestId;
            $stayId = $request->stayId;
            $chainId = $request->chainId;

            $model = $this->service->createAccessInStay($guestId, $stayId, $chainId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            $data = [
                'guest' => new GuestResource($model['guest']),
                'stay' => new StayResource($model['stay']),
            ];
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.createAccessInStay');
        }
    }

    


}
