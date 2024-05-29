<?php

namespace App\Services;

use App\Models\RequestSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RequestSettingService {

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

    public function getPostStayRequestData($settings, $hotel){
        
        try {
            $localLang = localeCurrent();

            //titulo
            $title = $settings->msg_title[$localLang];
            $title = str_replace('[nombre del hotel]', $hotel->name, $title);

            //mensaje
            $text = $settings->msg_text[$localLang];
            $parts = explode("<p><strong>[Link a las OTAs]</strong></p><p><br></p>", $text);

            $text1 = $parts[0] ?? null;
            $text2 = $parts[1] ?? null;

            return [
                "title" => $title,
                "text1" => $text1,
                "text2" => $text2,
                "otas_enabled" => $settings->otas_enabled,
                "request_to" => $settings->request_to
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

}