<?php
namespace App\Services\Hoster\Stay;

use App\Jobs\TranslateGenericMultipleJob;
use App\Models\CheckinSetting;
use App\Utils\Enums\EnumsStay\CheckinSettingsDefaultEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckinHosterServices {

    public function getAll ($hotelId) {
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

    public function updateSettings ($hotelId, $keysToSave, $newdata) {
        try {
            
            $default = $this->getAll($hotelId);
            $save = CheckinSetting::updateOrCreate(['hotel_id' => $hotelId],
                [
                    'succes_message' => in_array('succes_message', $keysToSave) ? $newdata->succes_message : $default->succes_message,
                    'first_step' => in_array('first_step', $keysToSave) ? $newdata->first_step : $default->first_step,
                    'second_step' => in_array('second_step', $keysToSave) ? $newdata->second_step : $default->second_step,
                    'show_prestay_query' => in_array('show_prestay_query', $keysToSave) ? $newdata->show_prestay_query : $default->show_prestay_query,
                ]
            );
            
            $this->processTranslateTexts($newdata, $save);
            return $save;
            
        } catch (\Exception $e) {
            Log::error('error updateSettings: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function processTranslateTexts ($request, $model){

        try {
            $succes_message = $request->succes_message['es'] ?? $model->succes_message['es'];
            $arrToTranslate = [
                'succes_message' => $succes_message,
            ];
            
            TranslateGenericMultipleJob::dispatch($arrToTranslate, $this, $model);
        } catch (\Exception $e) {
            Log::error('error processTranslateTextsCHECKIN: '.$e->getMessage());
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
            
            $model->succes_message = isset($translationFormat['succes_message']) ? $translationFormat['succes_message'] : $model->succes_message;
            //
            $model->save();
        } catch (\Exception $e) {
            Log::error('error processTranslateTextsCHECKIN: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
