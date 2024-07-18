<?php

namespace App\Services\Hoster\Chat;

use App\Models\{ChatSetting, ChatHour};
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

    public function updateSettings ($hotelId, $keysToSave, $newdata) {
        try {
            $default = $this->getAll($hotelId);

            $save = ChatSetting::updateOrCreate(['hotel_id' => $hotelId],
                [
                    'name' => in_array('name', $keysToSave) ? $newdata->name : $default->name,
                    'show_guest' => in_array('show_guest', $keysToSave) ? ($newdata->show_guest ? 1 : 0 ) : $default->show_guest,
                    'not_available_msg' => in_array('not_available_msg', $keysToSave) ? $newdata->not_available_msg : $default->not_available_msg,
                    'not_available_show' => in_array('not_available_show', $keysToSave) ? $newdata->not_available_show : $default->not_available_show,
                    'first_available_msg' => in_array('first_available_msg', $keysToSave) ? $newdata->first_available_msg : $default->first_available_msg,
                    'first_available_show' => in_array('first_available_show', $keysToSave) ? $newdata->first_available_show : $default->first_available_show,
                    'second_available_msg' => in_array('second_available_msg', $keysToSave) ? $newdata->second_available_msg : $default->second_available_msg,
                    'second_available_show' => in_array('second_available_show', $keysToSave) ? $newdata->second_available_show : $default->second_available_show,
                    'three_available_msg' => in_array('three_available_msg', $keysToSave) ? $newdata->three_available_msg : $default->three_available_msg,
                    'three_available_show' => in_array('three_available_show', $keysToSave) ? $newdata->three_available_show : $default->three_available_show,
                    'email_notify_new_message_to' => in_array('email_notify_new_message_to', $keysToSave) ? $newdata->email_notify_new_message_to : $default->email_notify_new_message_to,
                    'email_notify_pending_chat_to' => in_array('email_notify_pending_chat_to', $keysToSave) ? $newdata->email_notify_pending_chat_to : $default->email_notify_pending_chat_to,
                    'email_notify_not_answered_chat_to' => in_array('email_notify_not_answered_chat_to', $keysToSave) ? $newdata->email_notify_not_answered_chat_to : $default->email_notify_not_answered_chat_to,
                ]
            );
            $save->languages()->sync($newdata->languages_id);
            return $save;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function updateAvailability ($availability,$hotelId) {
        try {
            foreach($availability as $horary){
                $chat_hour = ChatHour::updateOrCreate(
                    ['hotel_id' =>  $hotelId,'day' =>  $horary['day']],
                    [
                        'active' => $horary['active'],
                        'horary' => $horary['horary'],
                    ]
                );
            }

            return $chat_hour;

        } catch (\Exception $e) {
            return $e;
        }
    }
}
