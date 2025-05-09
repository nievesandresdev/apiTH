<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\WorkPosition;

class NotificationsUpdateSeeder extends Seeder
{
    public function run()
    {
        $this->updateModelNotifications(User::all());
        $this->updateModelNotifications(WorkPosition::all());
    }

    private function updateModelNotifications($models)
    {
        foreach ($models as $model) {
            $notifications = $model->notifications ?? [];

            // Asegurar que es un array (puede venir como string JSON)
            if (is_string($notifications)) {
                $notifications = json_decode($notifications, true);
            }

            if (!is_array($notifications)) {
                $notifications = [];
            }

            // Asegurar subestructuras
            $notifications['email'] = $notifications['email'] ?? [];
            $notifications['informGeneral'] = ['periodicity' => 1];

            // Agregar/actualizar los nuevos valores
            $notifications['email']['informGeneral'] = true;
            $notifications['email']['informDiscontent'] = true;

            // Guardar
            $model->notifications = json_encode($notifications);
            $model->save();
        }
    }
}
