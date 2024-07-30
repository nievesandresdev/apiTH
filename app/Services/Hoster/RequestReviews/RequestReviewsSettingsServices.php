<?php

namespace App\Services\Hoster\RequestReviews;

use App\Jobs\TranslateGenericMultipleJob;
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
            $this->processTranslateTexts($newdata, $save);
            return $save;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function processTranslateTexts ($request, $model){
        
        $msg_title = $request->msg_title['es'] ?? null;
        $msg_text = $request->msg_text['es'] ?? null;
        $arrToTranslate = ['msg_title' => $msg_title,'msg_text' => $msg_text];
        
        TranslateGenericMultipleJob::dispatch($arrToTranslate, $this, $model);
    }

    public function updateTranslation($model, $translation) {
        Log::info('execute updateTranslation'. json_encode($translation));
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
        
        $model->msg_title = isset($translationFormat['msg_title']) ? $translationFormat['msg_title'] : $model->msg_title;
        $model->msg_text = isset($translationFormat['msg_text']) ? $translationFormat['msg_text'] : $model->msg_text;
        //
        $model->save();
        Log::info('nueva traduccion guardada');
    }
}
