<?php
namespace App\Services;

use App\Jobs\TranslateGenericMultipleJob;
use App\Models\CheckinSetting;
use App\Models\Hotel;
use App\Utils\Enums\EnumsStay\CheckinSettingsDefaultEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckinServices {

    public function getAllSettings ($hotelId) {
        try {
            $default = CheckinSetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = CheckinSettingsDefaultEnum::defaultFieldsForm();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }

}
