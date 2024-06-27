<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatSettingResource;
use App\Services\Hoster\Queries\QuerySettingsHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class QuerySettingsHosterController extends Controller
{
    public $service;

    function __construct(
        QuerySettingsHosterServices $_QuerySettingsHosterServices
    )
    {
        $this->service = $_QuerySettingsHosterServices;
    }

    public function updateNotificationsEmail(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, ['email_notify_new_feedback_to','email_notify_pending_feedback_to'], $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateNotificationsEmail');
        }
    }

}
