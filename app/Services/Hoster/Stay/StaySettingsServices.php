<?php
namespace App\Services\Hoster\Stay;

use App\Models\StayNotificationSetting;

class StaySettingsServices {

    function __construct()
    {

    }

    public function getAll ($hotelId) {
        try {
            
            $settings =  StayNotificationSetting::where('hotel_id',$hotelId)->first();
            if(!$settings){
                $settings = settingsNotyStayDefault();
            }
            return $settings;
        } catch (\Exception $e) {
            return $e;
        }
    }


    
}
