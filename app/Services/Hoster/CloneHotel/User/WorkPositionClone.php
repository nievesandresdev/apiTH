<?php

namespace App\Services\Hoster\CloneHotel\User;

use App\Models\WorkPosition;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WorkPositionClone
{
    public function handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        try {
            // Obtener todos los work positions del hotel padre
            $parentWorkPositions = WorkPosition::where('hotel_id', $HOTEL_ID_PARENT)->get();

            // Obtener todos los work positions del hotel hijo
            $childWorkPositions = WorkPosition::where('hotel_id', $HOTEL_ID_CHILD)->get();

            Log::info("Iniciando sincronización de work positions", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD,
                'parent_work_positions_count' => $parentWorkPositions->count(),
                'child_work_positions_count' => $childWorkPositions->count()
            ]);

            // Si el padre no tiene work positions, eliminar todos los del hijo
            if ($parentWorkPositions->isEmpty()) {
                foreach ($childWorkPositions as $childWorkPosition) {
                    try {
                        $childWorkPosition->delete();
                        Log::info("Work position eliminado del hotel hijo", [
                            'work_position_id' => $childWorkPosition->id,
                            'hotel_id' => $HOTEL_ID_CHILD
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error al eliminar work position del hotel hijo", [
                            'work_position_id' => $childWorkPosition->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                return true;
            }

            // Procesar cada work position del padre
            foreach ($parentWorkPositions as $parentWorkPosition) {
                try {
                    // Si el padre no tiene son_id, crear uno nuevo en el hijo
                    if (!$parentWorkPosition->son_id) {
                        try {
                            // Crear nuevo work position en el hotel hijo
                            $childWorkPosition = new WorkPosition();
                            $childWorkPosition->hotel_id = $HOTEL_ID_CHILD;

                            // Copiar todos los datos del padre al hijo
                            $childWorkPosition->fill([
                                'name' => $parentWorkPosition->name,
                                'permissions' => $parentWorkPosition->permissions,
                                'notifications' => $parentWorkPosition->notifications,
                                'periodicity_chat' => $parentWorkPosition->periodicity_chat,
                                'periodicity_stay' => $parentWorkPosition->periodicity_stay,
                                'status' => $parentWorkPosition->status
                            ]);

                            if (!$childWorkPosition->save()) {
                                Log::error("Error al guardar work position hijo", [
                                    'parent_work_position_id' => $parentWorkPosition->id
                                ]);
                                continue;
                            }

                            // Actualizar el son_id en el padre
                            $parentWorkPosition->son_id = $childWorkPosition->id;
                            $parentWorkPosition->save();

                            Log::info("Nuevo work position clonado del padre al hijo", [
                                'parent_work_position_id' => $parentWorkPosition->id,
                                'child_work_position_id' => $childWorkPosition->id
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Error al crear work position hijo", [
                                'parent_work_position_id' => $parentWorkPosition->id,
                                'error' => $e->getMessage()
                            ]);
                            continue;
                        }
                    } else {
                        // Si el padre ya tiene son_id, verificar si el hijo existe
                        $childWorkPosition = WorkPosition::find($parentWorkPosition->son_id);

                        if (!$childWorkPosition) {
                            // Si el hijo fue eliminado, crear uno nuevo con el mismo ID
                            try {
                                $childWorkPosition = new WorkPosition();
                                $childWorkPosition->id = $parentWorkPosition->son_id;
                                $childWorkPosition->exists = false; // Forzar la creación con ID específico
                                $childWorkPosition->hotel_id = $HOTEL_ID_CHILD;

                                $childWorkPosition->fill([
                                    'name' => $parentWorkPosition->name,
                                    'permissions' => $parentWorkPosition->permissions,
                                    'notifications' => $parentWorkPosition->notifications,
                                    'periodicity_chat' => $parentWorkPosition->periodicity_chat,
                                    'periodicity_stay' => $parentWorkPosition->periodicity_stay,
                                    'status' => $parentWorkPosition->status
                                ]);

                                $childWorkPosition->save();

                                Log::info("Work position hijo restaurado", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'child_work_position_id' => $childWorkPosition->id
                                ]);
                            } catch (\Exception $e) {
                                Log::error("Error al restaurar work position hijo", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'son_id' => $parentWorkPosition->son_id,
                                    'error' => $e->getMessage()
                                ]);
                                continue;
                            }
                        } else {
                            // Si el hijo existe, actualizarlo
                            try {
                                $childWorkPosition->fill([
                                    'name' => $parentWorkPosition->name,
                                    'permissions' => $parentWorkPosition->permissions,
                                    'notifications' => $parentWorkPosition->notifications,
                                    'periodicity_chat' => $parentWorkPosition->periodicity_chat,
                                    'periodicity_stay' => $parentWorkPosition->periodicity_stay,
                                    'status' => $parentWorkPosition->status
                                ]);
                                $childWorkPosition->save();

                                Log::info("Work position hijo actualizado", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'child_work_position_id' => $childWorkPosition->id
                                ]);
                            } catch (\Exception $e) {
                                Log::error("Error al actualizar work position hijo", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'error' => $e->getMessage()
                                ]);
                                continue;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al procesar work position", [
                        'parent_work_position_id' => $parentWorkPosition->id,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // Eliminar work positions del hijo que no están en el padre
            $validSonIds = $parentWorkPositions->pluck('son_id')->filter();
            $orphanedWorkPositions = WorkPosition::where('hotel_id', $HOTEL_ID_CHILD)
                ->whereNotIn('id', $validSonIds)
                ->get();

            foreach ($orphanedWorkPositions as $orphanedWorkPosition) {
                try {
                    $orphanedWorkPosition->delete();
                    Log::info("Work position huérfano eliminado del hotel hijo", [
                        'work_position_id' => $orphanedWorkPosition->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error al eliminar work position huérfano", [
                        'work_position_id' => $orphanedWorkPosition->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error al sincronizar work positions: " . $e->getMessage());
            return false;
        }
    }

    private function updateWorkPosition($parentWorkPosition, $childWorkPosition)
    {
        try {
            if (!$parentWorkPosition || !$childWorkPosition) {
                Log::error("Error: parentWorkPosition o childWorkPosition es null", [
                    'parent_id' => $parentWorkPosition ? $parentWorkPosition->id : 'null',
                    'child_id' => $childWorkPosition ? $childWorkPosition->id : 'null'
                ]);
                return;
            }

            // Convertir arrays a JSON si es necesario
            $permissions = is_array($parentWorkPosition->permissions) ? json_encode($parentWorkPosition->permissions) : $parentWorkPosition->permissions;
            $notifications = is_array($parentWorkPosition->notifications) ? json_encode($parentWorkPosition->notifications) : $parentWorkPosition->notifications;
            $periodicityChat = is_array($parentWorkPosition->periodicity_chat) ? json_encode($parentWorkPosition->periodicity_chat) : $parentWorkPosition->periodicity_chat;
            $periodicityStay = is_array($parentWorkPosition->periodicity_stay) ? json_encode($parentWorkPosition->periodicity_stay) : $parentWorkPosition->periodicity_stay;

            // Actualizar el work position hijo con los datos del padre
            $childWorkPosition->fill([
                'name' => $parentWorkPosition->name,
                'permissions' => $permissions,
                'notifications' => $notifications,
                'periodicity_chat' => $periodicityChat,
                'periodicity_stay' => $periodicityStay,
                'status' => $parentWorkPosition->status
            ]);

            // Asegurarnos de que el hotel_id sea el del hotel hijo
            $childWorkPosition->hotel_id = $childWorkPosition->hotel_id;

            $childWorkPosition->save();

            Log::info("Work position actualizado", [
                'parent_id' => $parentWorkPosition->id,
                'child_id' => $childWorkPosition->id,
                'parent_hotel_id' => $parentWorkPosition->hotel_id,
                'child_hotel_id' => $childWorkPosition->hotel_id,
                'name' => $parentWorkPosition->name
            ]);
        } catch (\Exception $e) {
            Log::error("Error al actualizar work position", [
                'parent_id' => $parentWorkPosition ? $parentWorkPosition->id : 'null',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
