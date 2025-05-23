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


    public function getRequestData($settings, $guestName, $period){
        // Log::info('msg_text: ' . json_encode($settings->msg_text));
        // Log::info('in_stay_msg_text: ' . json_encode($settings->in_stay_msg_text));
        try {
            $localLang = localeCurrent();
            
            //titulo
            $nameGuestText = "[nombreHuesped]";
            
            $title = $settings->msg_title[$localLang];
            $text = preg_replace('/>\s+</', '><', $settings->msg_text[$localLang]);
            if($period == 'in-stay'){
                $title = $settings->in_stay_msg_title[$localLang];
                $text = preg_replace('/>\s+</', '><', $settings->in_stay_msg_text[$localLang]);
            }

            $title = str_replace($nameGuestText, $guestName, $title);

            //mensaje
            $linkText = "[Link a las OTAs]";

            // Verificar si $linkText está contenido dentro de $text
            $buttonAnchor = false;
            if (strpos($text, $linkText) !== false) {
                $buttonAnchor = true;
            }
            $parts = explode("<p><strong>$linkText</strong></p><p><br></p>", $text);

            $text1 = $parts[0] ?? null;
            $text2 = $parts[1] ?? null;

            return [
                "title" => $title,
                "text1" => $text1,
                "text2" => $text2,
                "otas_enabled" => $period == 'in-stay' ? $settings->in_stay_otas_enabled : $settings->otas_enabled,
                "request_to" => $settings->request_to,
                "in_stay_activate" => $settings->in_stay_activate,
                "buttonAnchor" => $buttonAnchor
            ];
        } catch (\Exception $e) {
            return $e;
        }
    }

}
