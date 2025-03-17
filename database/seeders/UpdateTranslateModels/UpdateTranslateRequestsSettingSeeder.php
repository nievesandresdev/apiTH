<?php

namespace Database\Seeders\UpdateTranslateModels;

use App\Models\Language;
use App\Models\RequestSetting;
use App\Services\Hoster\UpdateTranslateV1;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UpdateTranslateRequestsSettingSeeder extends Seeder
{
    public function run()
    {
        try {
            // Instanciamos el servicio
            $translateService = new UpdateTranslateV1();

            $languages = Language::whereIn('name', ['Catalán', 'Euskera', 'Gallego', 'Holandés'])
                ->pluck('abbreviation');
            Log::info('UpdateTranslateRequestsSettingSeeder - Languages: ' . json_encode($languages));

            // Obtenemos todos los registros del modelo ChatSetting
            $models = RequestSetting::all();
            // Log::info('UpdateTranslateRequestsSettingSeeder - models: ' . json_encode($models));
            // Definimos el array de campos a traducir
            $fieldsToTranslate = [
                'msg_title',
                'msg_text',
                'in_stay_msg_title',
                'in_stay_msg_text'
            ];

            // Llamamos al método del servicio, que recorrerá cada modelo y despachará el job para cada uno
            $translateService->UpdateTranslateV1GoJob($languages, $fieldsToTranslate, $models);
        } catch (\Exception $e) {
            Log::error('Error en UpdateTranslateRequestsSettingSeeder: ' . $e->getMessage());
        }
    }
}
