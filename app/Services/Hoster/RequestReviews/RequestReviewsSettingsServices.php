<?php

namespace App\Services\Hoster\RequestReviews;

use App\Models\RequestSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class RequestReviewsSettingsServices {

    function __construct()
    {

    }

    public function getAll ($hotelId) {
        try {
            $default = RequestSetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = requestSettingsDefault();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateSettings ($hotelId, $keysToSave, $newdata) {
        try {
            $default = $this->getAll($hotelId);
            $save = RequestSetting::updateOrCreate(['hotel_id' => $hotelId],
                [
                    'msg_title' => in_array('msg_title', $keysToSave) ? $newdata->msg_title : $default->msg_title,
                    'msg_text' => in_array('msg_text', $keysToSave) ? $newdata->msg_text : $default->msg_text,
                    'otas_enabled' => in_array('otas_enabled', $keysToSave) ? $newdata->otas_enabled : $default->otas_enabled,
                    'request_to' => in_array('request_to', $keysToSave) ? $newdata->request_to : $default->request_to,
                ]
            );
            return $save;
        } catch (\Exception $e) {
            return $e;
        }
    }
}
