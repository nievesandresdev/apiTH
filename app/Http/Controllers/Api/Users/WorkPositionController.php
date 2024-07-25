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
        $hotel = request()->attributes->get('hotel');
        $data = request()->validate([
            'name' => 'required|string|unique:work_positions',
        ]);

       /*  return [
            'currentHotelis' => $hotel
        ]; */

        try {
            $work_position = WorkPosition::create($data);
            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'message' => 'Creado con éxito',
                'wPosition' => $work_position
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, [
                'message' => 'Error al crear la posición de trabajo'
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
