<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\WorkPosition;
use App\Utils\Enums\EnumResponse;

class WorkPositionController extends Controller
{
    public function getAllWorkPosition()
    {
        $work_positions = WorkPosition::active()->byHotel()->get();

        $work_positions_mapped = $work_positions->map(function($work_position) {
            $has_profile = $work_position->profiles()->exists();

            $work_position->relation = $has_profile ? 1 : 0;

            return $work_position;
        });

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'work_positions' => $work_positions_mapped
        ]);
    }

    public function store()
    {
        $data = request()->validate([
            //'name' => 'required|string|unique:work_positions',
            'permissions' => 'required|array', // Asegura que los permisos se envían como un array
            'notifications' => 'required|array', // Asegura que las notificaciones se envían como un array
            'periodicityChat' => 'required|integer',
            'periodicityStay' => 'required|integer'
        ]);


        try {
            // Guarda la posición de trabajo con los permisos y notificaciones enviados
            $work_position = WorkPosition::create([
                'name' => $data['name'],
                'permissions' => json_encode($data['permissions']), // Guarda el JSON de permisos
                'notifications' => json_encode($data['notifications']), // Guarda el JSON de notificaciones
                'periodicity_chat' => $data['periodicityChat'],
                'periodicity_stay' => $data['periodicityStay'],
            ]);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Creado con éxito',
                'wPosition' => $work_position
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => 'Error al crear la posición de trabajo',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function update()
    {
        $work_position = WorkPosition::find(request()->id);

        $data = request()->validate([
            //'name' => 'required|string|unique:work_positions,name,' . request()->id . ',id',
            'permissions' => 'required|array', // Asegura que los permisos se envían como un array
            'notifications' => 'required|array', // Asegura que las notificaciones se envían como un array
            'periodicityChat' => 'required|integer',
            'periodicityStay' => 'required|integer'
        ]);


        try {
            $work_position->update([
                'name' => $data['name'],
                'permissions' => json_encode($data['permissions']), // Guarda el JSON de permisos
                'notifications' => json_encode($data['notifications']), // Guarda el JSON de notificaciones
                'periodicity_chat' => $data['periodicityChat'],
                'periodicity_stay' => $data['periodicityStay']

            ]);

            foreach ($work_position->profiles as $profile) {
                $profile->user->update([
                    'permissions' => json_encode($data['permissions']),
                    'notifications' => json_encode($data['notifications']),
                    'periodicity_chat' => $data['periodicityChat'],
                    'periodicity_stay' => $data['periodicityStay']
                ]);
            }

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Actualizado con éxito',
                'wPosition' => $work_position
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => 'Error al actualizar la posición de trabajo'
            ]);
        }
    }

    public function delete()
    {
        $work_position = WorkPosition::find(request()->id);

        try {
            // Buscar perfiles asociados
            $profiles = $work_position->profiles;

            // Si existen perfiles, actualizar el campo work_position_id a null
            if ($profiles && $profiles->count() > 0) {
                foreach ($profiles as $profile) {
                    $profile->update(['work_position_id' => null]);
                }
            }

            // Actualizar el estado de la posición de trabajo a 0
            $work_position->update(['status' => 0]);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Eliminado con éxito',
                'wPosition' => $work_position
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => 'Error al eliminar la posición de trabajo'
            ]);
        }
    }

}
