<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GuestResource;
use App\Http\Resources\StayResource;
use App\Models\Guest;
use Illuminate\Http\Request;

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

}
