<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hoster\QuerySettingsHosterResource;
use App\Http\Resources\QuerySettingsResource;
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

    public function getPreStaySettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new QuerySettingsHosterResource($model,['pre_stay_activate', 'pre_stay_thanks', 'pre_stay_comment']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getPreStaySettings');
        }
    }
    
    public function updatePreStaySettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, ['pre_stay_activate','pre_stay_thanks','pre_stay_comment'], $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updatePreStaySettings');
        }
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
