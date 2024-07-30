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

    public function getNotificationsByUser(Request $request){
        try {
            return $model = $this->service->getNotificationsByUser($request->userId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getNotificationsByUser');
        }
    }

}
