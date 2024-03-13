<?php

namespace App\Services;

use App\Models\QuerySetting;
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
                $default = queriesTextDefault();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function notifications ($hotelId) {
        try {
            $default = QuerySetting::select('notify_to_hoster')
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