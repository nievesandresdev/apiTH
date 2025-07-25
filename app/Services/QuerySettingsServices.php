<?php

namespace App\Services;

use App\Models\QuerySetting;
use App\Utils\Enums\EnumsQueries\QuerySettingsEnums;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuerySettingsServices {

    function __construct()
    {

    }

    public function getAll ($hotelId) {
        try {
            $default = QuerySetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = QuerySettingsEnums::queriesTextDefault();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function notifications ($hotelId) {
        try {
            $default = QuerySetting::select('email_notify_new_feedback_to','email_notify_pending_feedback_to')
                        ->where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = queryNotifyDefault();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }
}