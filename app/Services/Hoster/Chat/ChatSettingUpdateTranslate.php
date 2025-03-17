<?php

namespace App\Services\Hoster\Chat;

use App\Jobs\TranslateGenericMultipleJob;
use App\Models\ChatSetting;
use App\Models\Language;
use Illuminate\Support\Facades\Log;

class ChatSettingUpdateTranslate {
   

    
    public function UpdateTranslateChatSetting($languages) {
        try {
            $languages = Language::whereIn('name', ['Catalán','Euskera','Gallego','Holandés'])->pluck('abbreviation');
            Log::info('UpdateTranslateChatSetting'.json_encode($languages));

            $model = ChatSetting::all();
            
            $arrToTranslate = [
                'not_available_msg' => $model->not_available_msg,
                'first_available_msg' => $model->first_available_msg,
                'second_available_msg' => $model->second_available_msg,
                'three_available_msg' => $model->three_available_msg,
            ];
            
            TranslateGenericMultipleJob::dispatch($arrToTranslate, $this, $model, $languages);
        } catch (\Exception $e) {
            Log::error('error UpdateTranslateChatSetting: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateTranslation($model, $translation) {
        try {
            foreach ($translation as $field => $newTranslations) {
                // Obtiene las traducciones actuales del campo
                $currentTranslations = $model->{$field} ?? [];
                if (!is_array($currentTranslations)) {
                    $currentTranslations = (array)$currentTranslations;
                }
                
                // Asegura que newTranslations sea un array
                if (!is_array($newTranslations)) {
                    $newTranslations = (array)$newTranslations;
                }
                
                // Procesa las nuevas traducciones para extraer la propiedad "text"
                $processedNewTranslations = [];
                foreach ($newTranslations as $lang => $value) {
                    if (is_object($value) && property_exists($value, 'text')) {
                        $processedNewTranslations[$lang] = $value->text;
                    } elseif (is_array($value) && isset($value['text'])) {
                        $processedNewTranslations[$lang] = $value['text'];
                    } else {
                        $processedNewTranslations[$lang] = $value;
                    }
                }
                
                // Fusiona las traducciones actuales con las nuevas procesadas.
                // Las nuevas sobrescribirán las existentes en caso de conflicto.
                $updatedTranslations = array_merge($currentTranslations, $processedNewTranslations);
                
                // Actualiza el campo en el modelo.
                $model->{$field} = $updatedTranslations;
            }
            
            // Guarda los cambios en la base de datos.
            $model->save();
            Log::info('Modelo actualizado: ' . json_encode($model->toArray()));
        } catch (\Exception $e) {
            Log::error('error UpdateTranslateChatSetting updateTranslation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    
    
    
}
