<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatSettingResource;
use App\Services\Hoster\Chat\ChatSettingsServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class ChatSettingsController extends Controller
{
    public $service;

    function __construct(
        ChatSettingsServices $_ChatSettingsServices
    )
    {
        $this->service = $_ChatSettingsServices;
    }

    public function getAll(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $model = new ChatSettingResource($model,['email_notify_new_message_to','email_notify_pending_chat_to','email_notify_not_answered_chat_to']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function updateNotificationsEmail(Request $request){
        try {
            return $request;
            // $hotel = $request->attributes->get('hotel');
            // $model = $this->service->getAll($hotel->id);
            // if(!$model){
            //     $data = [
            //         'message' => __('response.bad_request_long')
            //     ];
            //     return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            // }
            // $model = new ChatSettingResource($model,['email_notify_new_message_to','email_notify_pending_chat_to','email_notify_not_answered_chat_to']);
            // return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

}
