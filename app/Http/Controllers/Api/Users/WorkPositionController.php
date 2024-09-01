<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\WorkPosition;
use App\Utils\Enums\EnumResponse;

class WorkPositionController extends Controller
{
    public function getAllWorkPosition()
    {

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'work_positions' => WorkPosition::active()->byHotel()->get()
        ]);
    }

    public function store()
    {
        $data = request()->validate([
            'name' => 'required|string|unique:work_positions',
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
                'status' => 1
            ]);

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Creado con éxito',
                'wPosition' => $work_position
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => 'Error al crear la posición de trabajo',
                'error' => $e->getMessage() // Opcional: devuelve el mensaje de error para depuración
            ]);
        }
    }


    public function update()
    {
        $work_position = WorkPosition::find(request()->id);

        $data = request()->validate([
            'name' => 'required|string|unique:work_positions,name,' . $work_position->id . ',id'
        ]);

        try {
            $work_position->update($data);
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
