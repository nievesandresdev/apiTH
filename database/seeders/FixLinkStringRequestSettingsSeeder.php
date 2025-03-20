<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RequestSetting;

class FixLinkStringRequestSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los registros de la tabla RequestSetting
        $models = RequestSetting::all();
        // $models = RequestSetting::where('hotel_id', 191)->get();

        // Iterar sobre cada modelo
        foreach ($models as $model) {
            // Procesar el campo msg_text
            $msgText = $model->msg_text;
            // Verificar que sea un arreglo
            if (is_array($msgText)) {
                foreach ($msgText as $lang => $text) {
                    // Reemplazar cualquier texto que esté entre corchetes por el ancla "[Link a las OTAs]"
                    $msgText[$lang] = preg_replace('/\[.*?\]/', '[Link a las OTAs]', $text);
                }
            }
            // Actualizar el modelo con el nuevo valor
            $model->msg_text = $msgText;

            // Procesar el campo in_stay_msg_text
            $inStayMsgText = $model->in_stay_msg_text;
            // Verificar que sea un arreglo
            if (is_array($inStayMsgText)) {
                foreach ($inStayMsgText as $lang => $text) {
                    // Reemplazar cualquier texto entre corchetes por "[Link a las OTAs]"
                    $inStayMsgText[$lang] = preg_replace('/\[.*?\]/', '[Link a las OTAs]', $text);
                }
            }
            // Actualizar el modelo con el nuevo valor
            $model->in_stay_msg_text = $inStayMsgText;

            // Guardar los cambios en la base de datos
            $model->save();

            // Se muestra un mensaje en consola para indicar que el registro fue actualizado
            $this->command->info("Actualizado RequestSetting ID: " . $model->id);
        }
    }
}
