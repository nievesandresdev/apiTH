<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Chat\ChatHosterServices;
use App\Services\Hoster\Stay\StayHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class StayChatHosterController extends Controller
{
    public $service;
    public $stayService;

    function __construct(
        StayHosterServices $_StayHosterServices,
        ChatHosterServices $_ChatHosterServices
    )
    {
        $this->stayService = $_StayHosterServices;
        $this->service = $_ChatHosterServices;
    }

    public function getDataRoom(Request $request){
        try {
            $stayId = $request->stayId;
            $guestId = $request->guestId;

            $model = $this->service->getDataRoom($stayId, $guestId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getDataRoom');
        }
    }

    public function sendMsg(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $text = $request->text;
            $pendingStatus = $request->pendingStatus;
            
            $model = $this->service->sendMsg($guestId, $stayId, $text, $hotel, $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.sendMsg');
        }
    }

    public function togglePending(Request $request){
        try {
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $pendingBool = $request->pendingBool;
            $hotel = $request->attributes->get('hotel');

            $model = $this->service->togglePending($guestId, $stayId, $pendingBool, $hotel->id, $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.togglePending');
        }
    }

    public function getGuestListWNoti(Request $request){
        try {
            $stayId = $request->stayId;

            $model = $this->service->getGuestListWNoti($stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGuestListWNoti');
        }
    }

    public function pendingCountByHotel(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');

            $model = $this->service->pendingCountByHotel($hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.pendingCountByHotel');
        }
    }

    public function pendingCountByStay($stayId){
        try {
            $model = $this->service->pendingCountByStay($stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.pendingCountByStay');
        }
    }

    public function markGuesMsgstAsRead($stayId, $guestId){
        try {
            $model = $this->service->markGuesMsgstAsRead($stayId, $guestId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.markGuesMsgstAsRead');
        }
    }

    public function markHosterMsgstAsRead($stayId, $guestId){
        try {
            $model = $this->service->markHosterMsgstAsRead($stayId, $guestId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.markHosterMsgstAsRead');
        }
    }

}
