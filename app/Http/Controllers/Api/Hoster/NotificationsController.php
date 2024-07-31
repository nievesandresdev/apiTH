<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\NotificationsServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;

class NotificationsController extends Controller
{
    public $service;

    function __construct(
        NotificationsServices $_NotificationsServices
    )
    {
        $this->service = $_NotificationsServices;
    }

    public function getNotificationsByUser($UserId){
        try {
            $model = $this->service->getNotificationsByUser($UserId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $this->service->maskAsReadToUser($UserId);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getNotificationsByUser');
        }
    }

    public function vote(Request $request){
        try {
             
            $request->validate([
                'userId' => 'integer',
                'noticationId' => 'integer',
                'face' => 'string',
            ]);
            
            $userId = $request->userId;
            $noticationId = $request->noticationId;
            $face = $request->face;

            $model = $this->service->vote($userId,  $noticationId, $face);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.vote');
        }
    }

}
