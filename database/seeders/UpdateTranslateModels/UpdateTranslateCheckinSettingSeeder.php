<?php

namespace Database\Seeders\UpdateTranslateModels;

use App\Models\ChatSetting;
use App\Models\CheckinSetting;
use App\Models\Language;
use App\Services\Hoster\UpdateTranslateV1;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UpdateTranslateCheckinSettingSeeder extends Seeder
{
    public function run()
    {
        try {
            // Instanciamos el servicio
            $translateService = new UpdateTranslateV1();

            $languages = ["nl","eu","gl","ca"];
            Log::info('UpdateTranslatecheckinsetting - Languages: ' . json_encode($languages));

            // Obtenemos todos los registros del modelo ChatSetting
            $models = CheckinSetting::all();

            // Definimos el array de campos a traducir
            $fieldsToTranslate = [
                'succes_message'
            ];

            // Llamamos al método del servicio, que recorrerá cada modelo y despachará el job para cada uno
            $translateService->UpdateTranslateV1GoJob($languages, $fieldsToTranslate, $models);
        } catch (\Exception $e) {
            Log::error('Error en UpdateTranslateCheckinSettingSeeder: ' . $e->getMessage());
        }
    }
}
