<?php

namespace App\Services\Hoster\RequestReviews;

use App\Jobs\TranslateGenericMultipleJob;
use App\Models\RequestSetting;
use App\Models\RequestSettingsHistory;
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
                    'in_stay_activate' => in_array('in_stay_activate', $keysToSave) ? $newdata->in_stay_activate : $default->in_stay_activate,
                    'in_stay_msg_title' => in_array('in_stay_msg_title', $keysToSave) ? $newdata->in_stay_msg_title : $default->in_stay_msg_title,
                    'in_stay_msg_text' => in_array('in_stay_msg_text', $keysToSave) ? $newdata->in_stay_msg_text : $default->in_stay_msg_text,
                    'in_stay_otas_enabled' => in_array('in_stay_otas_enabled', $keysToSave) ? $newdata->in_stay_otas_enabled : $default->in_stay_otas_enabled,
                    'request_to' => in_array('request_to', $keysToSave) ? $newdata->request_to : $default->request_to,
                ]
            );

            $this->createHistory($keysToSave, $newdata, $default, $save->id, $hotelId);

            $this->processTranslateTexts($newdata, $save);
            return $save;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function processTranslateTexts ($request, $model){
        
        $msg_title = $request->msg_title['es'] ?? $model->msg_title['es'];
        $msg_text = $request->msg_text['es'] ?? $model->msg_text['es'];
        $in_stay_msg_title = $request->in_stay_msg_title['es'] ?? $model->in_stay_msg_title['es'];
        $in_stay_msg_text = $request->in_stay_msg_text['es'] ?? $model->in_stay_msg_text['es'];
        $arrToTranslate = [
            'msg_title' => $msg_title,'msg_text' => $msg_text,
            'in_stay_msg_title' => $in_stay_msg_title,'in_stay_msg_text' => $in_stay_msg_text
        ];
        
        TranslateGenericMultipleJob::dispatch($arrToTranslate, $this, $model);
    }

    public function updateTranslation($model, $translation) {
        Log::info('execute updateTranslation RequestReviewsSettingsServices '. json_encode($translation));
        // Asegurarse de que $translation sea un arreglo
        $translationFormat = json_decode(json_encode($translation), true);
        
    
        foreach ($translationFormat as $key => &$categories) {
            // Log::info('actual '. json_encode($model[$key]));
            $currentTranslations = $model[$key];
            $anchor="[Link a las OTAs]";
            // if($key == 'msg_title'){
            //     $currentTranslations = $model->msg_title;
            //     $anchor="[nombre del hotel]";
            // }
            foreach ($categories as $lang => &$details) {
                // Asegurarse de que 'text' existe antes de intentar accederlo
                if (isset($details['text'])) {
                    $details = $details['text'];
                    if ($lang == 'es') {
                        // Para español, conservar la traducción original
                        $details = isset($currentTranslations['es']) ? $currentTranslations['es'] : $details;
                    } else {
                        preg_match('/\[(.*?)\]/', $details, $matches);
                        if (!empty($matches)) {
                            $foundText = $matches[0];  // Texto encontrado entre corchetes
                            // Sustituir el texto encontrado por el ancla correspondiente
                            $details = str_replace($foundText, $anchor, $details);
                        }
                    }
                }
            }
        }
        $model->msg_title = isset($translationFormat['msg_title']) && $translationFormat['msg_title'] ? $translationFormat['msg_title'] : $model->msg_title;
        $model->msg_text = isset($translationFormat['msg_text']) && $translationFormat['msg_text'] ? $translationFormat['msg_text'] : $model->msg_text;
        $model->in_stay_msg_title = isset($translationFormat['in_stay_msg_title']) && $translationFormat['in_stay_msg_title'] ? $translationFormat['in_stay_msg_title'] : $model->in_stay_msg_title;
        $model->in_stay_msg_text = isset($translationFormat['in_stay_msg_text']) && $translationFormat['in_stay_msg_text'] ? $translationFormat['in_stay_msg_text'] : $model->in_stay_msg_text;
        // //
        $model->save();
        Log::info('nueva traduccion guardada');
    }

    public function createHistory($keysToSave, $newdata, $oldData, $settingId, $hotelId) {

        try {
            $inStayActivate = null;
            $requestTo = null;
            
            $diffActivate = boolval($newdata->in_stay_activate) !== boolval($oldData->in_stay_activate);
            $diffRequestTo = json_encode($newdata->request_to) !== json_encode($oldData->request_to);
            
            if(in_array('in_stay_activate', $keysToSave) && $diffActivate){
                $inStayActivate = $oldData->in_stay_activate;
            }
            if(in_array('request_to', $keysToSave) && $diffRequestTo){
                $requestTo = $oldData->request_to;
            }

            if(
                in_array('in_stay_activate', $keysToSave) && $diffActivate || 
                in_array('request_to', $keysToSave) && $diffRequestTo
            ){
                return RequestSettingsHistory::create([
                    'hotel_id' => $hotelId,
                    'request_setting_id' => $settingId,
                    'in_stay_activate' => $inStayActivate,
                    'request_to' => $requestTo
                ]);
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function fieldAtTheMoment($field, $moment, $hotelId) {
        try {
            $latestHistory = RequestSettingsHistory::where('hotel_id', $hotelId)
                ->where('created_at', '>=', $moment)
                ->whereNotNull($field)
                ->orderBy('created_at', 'asc')
                ->first();
    
            if ($latestHistory) {
                // Retornar el valor del campo desde el historial encontrado
                return $latestHistory->$field;
            } else {
                $currentSettings = $this->getAll($hotelId);
                return $currentSettings->$field;
            }
        } catch (\Exception $e) {
            Log::error('Error en fieldAtTheMoment: ' . $e->getMessage());
            return null; // O considera lanzar una excepción personalizada
        }
    }
    
}
