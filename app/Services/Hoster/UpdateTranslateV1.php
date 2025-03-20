<?php

namespace App\Services\Hoster;

use App\Jobs\TranslateGenericMultipleJob;
use Illuminate\Support\Facades\Log;

class UpdateTranslateV1 {

    /**
     * Despacha el job de traducción para cada modelo de la colección.
     *
     * @param mixed $languages Colección de abreviaciones de idiomas.
     * @param array $fieldsToTranslate Array de nombres de campos a traducir.
     * @param \Illuminate\Support\Collection $models Colección de modelos a actualizar.
     */
    public function UpdateTranslateV1GoJob($languages, array $fieldsToTranslate, $models) {
        try {
            foreach ($models as $model) {
                // Log::info('Modelo a procesar: ' . json_encode($model->toArray()));
                // Se arma el arreglo de campos a traducir usando el array proporcionado
                $fields = [];
                foreach ($fieldsToTranslate as $field) {
                    $fields[$field] = $model->{$field};
                }
                
                // Despacha el job para cada registro
                TranslateGenericMultipleJob::dispatch($fields, $this, $model, $languages, false);
            }
        } catch (\Exception $e) {
            Log::error('error UpdateTranslate: ' . $e->getMessage());
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
                $updatedTranslations = array_merge($currentTranslations, $processedNewTranslations);
                
                // Actualiza el campo en el modelo.
                $model->{$field} = $updatedTranslations;
            }
            
            // Guarda los cambios en la base de datos.
            $model->save();
            Log::info('Modelo actualizado: ' . json_encode($model->id));
        } catch (\Exception $e) {
            Log::error('error UpdateTranslateV1 updateTranslation: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
