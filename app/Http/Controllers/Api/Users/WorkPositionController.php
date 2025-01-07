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

            $work_position->relation = $has_profile;

            return $work_position;
        });

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'work_positions' => $work_positions_mapped
        ]);
    }

    public function store()
    {

        try {
            // Guarda la posición de trabajo con los permisos y notificaciones enviados
            $work_position = WorkPosition::create([
                'name' => request()->name,
                'permissions' => json_encode(request()->permissions),
                'notifications' => json_encode(request()->notifications),
                'periodicity_chat' => json_encode(request()->periodicityChat),
                'periodicity_stay' => json_encode(request()->periodicityStay),
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
            'permissions' => 'required|array',
            'notifications' => 'required|array',
            'periodicityChat' => 'required|array',
            'periodicityStay' => 'required|array'
        ]);


        try {
            $work_position->update([
                'name' => request()->name,
                'permissions' => json_encode($data['permissions']),
                'notifications' => json_encode($data['notifications']),
                'periodicity_chat' => json_encode($data['periodicityChat']),
                'periodicity_stay' => json_encode($data['periodicityStay'])

            ]);

            foreach ($work_position->profiles as $profile) {
                $profile->user->update([
                    'permissions' => json_encode($data['permissions']),
                    'notifications' => json_encode($data['notifications']),
                    'periodicity_chat' => json_encode($data['periodicityChat']),
                    'periodicity_stay' => json_encode($data['periodicityStay'])
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

            // Si existen perfiles asociados, devolver un mensaje de error
            if ($profiles && $profiles->count() > 0) {
                return bodyResponseRequest(EnumResponse::ERROR, [
                    'message' => 'No se puede eliminar la posición de trabajo porque tiene registros asociados.'
                ]);
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
