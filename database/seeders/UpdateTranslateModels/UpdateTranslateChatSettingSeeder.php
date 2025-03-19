<?php

namespace Database\Seeders\UpdateTranslateModels;

use App\Models\ChatSetting;
use App\Models\Language;
use App\Services\Hoster\UpdateTranslateV1;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UpdateTranslateChatSettingSeeder extends Seeder
{
    public function run()
    {
        try {
            // Instanciamos el servicio
            $translateService = new UpdateTranslateV1();

            $languages = ["nl","eu","gl","ca"];

            // Obtenemos todos los registros del modelo ChatSetting
            $models = ChatSetting::all();

            // Definimos el array de campos a traducir
            $fieldsToTranslate = [
                'not_available_msg',
                'first_available_msg',
                'second_available_msg',
                'three_available_msg'
            ];

            // Llamamos al método del servicio, que recorrerá cada modelo y despachará el job para cada uno
            $translateService->UpdateTranslateV1GoJob($languages, $fieldsToTranslate, $models);
        } catch (\Exception $e) {
            Log::error('Error en UpdateTranslateChatSettingSeeder: ' . $e->getMessage());
        }
    }
}
