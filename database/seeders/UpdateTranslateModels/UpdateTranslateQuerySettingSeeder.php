<?php

namespace Database\Seeders\UpdateTranslateModels;

use App\Models\ChatSetting;
use App\Models\CheckinSetting;
use App\Models\Language;
use App\Models\QuerySetting;
use App\Services\Hoster\UpdateTranslateV1;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UpdateTranslateQuerySettingSeeder extends Seeder
{
    public function run()
    {
        try {
            // Instanciamos el servicio
            $translateService = new UpdateTranslateV1();

            // $languages = Language::whereIn('name', ['Catalán', 'Euskera', 'Gallego', 'Holandés'])
            //     ->pluck('abbreviation');
            $languages = ["nl","eu","gl","ca"];
            Log::info('UpdateTranslatequerysetting - Languages: ' . json_encode($languages));

            // Obtenemos todos los registros del modelo ChatSetting
            $models = QuerySetting::all();

            // Definimos el array de campos a traducir
            $fieldsToTranslate = [
                'pre_stay_thanks',
                'pre_stay_comment',
                'in_stay_thanks_good',
                'in_stay_thanks_normal',
                'in_stay_comment',
                'post_stay_thanks_good',
                'post_stay_thanks_normal',
                'post_stay_comment',
                'in_stay_assessment_good',
                'in_stay_assessment_normal',
                'post_stay_assessment_good',
                'post_stay_assessment_normal'
            ];

            // Llamamos al método del servicio, que recorrerá cada modelo y despachará el job para cada uno
            $translateService->UpdateTranslateV1GoJob($languages, $fieldsToTranslate, $models);
        } catch (\Exception $e) {
            Log::error('Error en UpdateTranslateQuerySettingSeeder: ' . $e->getMessage());
        }
    }
}