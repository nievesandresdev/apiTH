<?php

namespace App\Services\Hoster\Chat;

use App\Jobs\TranslateGenericMultipleJob;
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
            Log::info('getAll '.$hotelId);
            $default = ChatSetting::where('hotel_id',$hotelId)->first();
            if(!$default){
                $default = defaultChatSettings();
            }else{
                $default->load('languages');
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
            if(in_array('languages', $keysToSave)){
                $save->languages()->sync($newdata->languages_id);
            }

            $this->processTranslateTexts($newdata, $save);
            return $save;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function processTranslateTexts ($request, $model){

        try {
            $first_available_msg = $request->first_available_msg['es'] ?? $model->first_available_msg['es'];
            $not_available_msg = $request->not_available_msg['es'] ?? $model->not_available_msg['es'];
            $second_available_msg = $request->second_available_msg['es'] ?? $model->second_available_msg['es'];
            $three_available_msg = $request->three_available_msg['es'] ?? $model->three_available_msg['es'];

            $arrToTranslate = [
                'first_available_msg' => $first_available_msg,
                'not_available_msg' => $not_available_msg,
                'second_available_msg' => $second_available_msg,
                'three_available_msg' => $three_available_msg
            ];
            
            TranslateGenericMultipleJob::dispatch($arrToTranslate, $this, $model, [], false);
        } catch (\Exception $e) {
            Log::error('error processTranslateTextsCHAT: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateTranslation($model, $translation) {
        try{
            // Asegurarse de que $translation sea un arreglo
            $translationFormat = json_decode(json_encode($translation), true);
        
            foreach ($translationFormat as $key => &$categories) {
                foreach ($categories as $lang => &$details) {
                    // Asegurarse de que 'text' existe antes de intentar accederlo
                    if (isset($details['text'])) {
                        $details = $details['text'];
                    }
                }
            }
            
            $model->first_available_msg = isset($translationFormat['first_available_msg']) ? $translationFormat['first_available_msg'] : $model->first_available_msg;
            $model->not_available_msg = isset($translationFormat['not_available_msg']) ? $translationFormat['not_available_msg'] : $model->not_available_msg;
            $model->second_available_msg = isset($translationFormat['second_available_msg']) ? $translationFormat['second_available_msg'] : $model->second_available_msg;
            $model->three_available_msg = isset($translationFormat['three_available_msg']) ? $translationFormat['three_available_msg'] : $model->three_available_msg;
            //
            $model->save();
        } catch (\Exception $e) {
            Log::error('error processTranslateTextsCHat: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
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
