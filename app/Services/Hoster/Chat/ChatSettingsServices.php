<?php

namespace App\Services\Hoster\Chat;

use App\Models\ChatSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ChatSettingsServices {

    function __construct()
    {

    }

    public function getAll ($hotelId) {
        try {
            $default = ChatSetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = defaultChatSettings();
            }
            return $default;
        } catch (\Exception $e) {
            return $e;
        }
    }
}
