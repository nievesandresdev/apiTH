<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatMessageResource;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\ChatService;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public $service;

    function __construct(
        ChatService $_ChatService
    )
    {
        $this->service = $_ChatService;
    }


    public function sendMsgToHoster(Request $request){

        try {
            $model = $this->service->sendMsgToHoster($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }

            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.sendMsgToHoster');
        }
    }

    public function markMsgsAsRead(Request $request){

        try {
            $model = $this->service->markMsgsAsRead($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }

            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.markMsgsAsRead');
        }
    }

    public function loadMessages(Request $request){
        try {
            $models = $this->service->loadMessages($request);
            if(!$models){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }

            $data = ChatMessageResource::collection($models);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.loadMessages');
        }
    }

    public function unreadMsgs(Request $request){
        try {

            $model = $this->service->unreadMsgs($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.loadMessages');
        }
    }

    public function getAvailavilityByHotel(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            return $model = $this->service->getAvailavilityByHotel(191);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAvailavilityByHotel');
        }
    }

    public function getAvailableLanguages(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAvailableLanguages($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAvailableLanguages');
        }
    }

    public function getAllSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAllSettings($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllSettings');
        }
    }

    public function getChatHoursByHotel (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getChatHoursByHotel($hotel->id);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getChatHoursByHotel');
        }
    }
}
