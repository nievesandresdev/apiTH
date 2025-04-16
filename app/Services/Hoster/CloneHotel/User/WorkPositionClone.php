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

            if ($parentWorkPositions->isEmpty()) {
                Log::info("No hay work positions en el hotel padre: {$HOTEL_ID_PARENT}");
                return true;
            }

            Log::info("Iniciando clonación de work positions", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD,
                'total_work_positions' => $parentWorkPositions->count()
            ]);

            foreach ($parentWorkPositions as $parentWorkPosition) {
                try {
                    // Verificar que el work position padre existe
                    if (!$parentWorkPosition) {
                        Log::warning("Work position padre no encontrado", [
                            'parent_hotel_id' => $HOTEL_ID_PARENT
                        ]);
                        continue;
                    }

                    // Verificar que el work position padre tiene un ID válido
                    if (!$parentWorkPosition->id) {
                        Log::warning("Work position padre no tiene ID válido", [
                            'parent_hotel_id' => $HOTEL_ID_PARENT
                        ]);
                        continue;
                    }

                    // Si el padre no tiene son_id, crear uno nuevo en el hijo
                    if (!$parentWorkPosition->son_id) {
                        try {
                            // Crear nuevo work position en el hotel hijo
                            $childWorkPosition = new WorkPosition();

                            // Primero establecer el hotel_id
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

                            // Guardar el work position hijo
                            if (!$childWorkPosition->save()) {
                                Log::error("Error al guardar work position hijo", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'parent_hotel_id' => $HOTEL_ID_PARENT,
                                    'child_hotel_id' => $HOTEL_ID_CHILD
                                ]);
                                continue;
                            }

                            // Verificar que el hijo se creó correctamente y tiene un ID
                            if (!$childWorkPosition->id) {
                                Log::error("Work position hijo no tiene ID después de guardar", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'parent_hotel_id' => $HOTEL_ID_PARENT,
                                    'child_hotel_id' => $HOTEL_ID_CHILD
                                ]);
                                continue;
                            }

                            // Actualizar el son_id en el padre
                            $parentWorkPosition->son_id = $childWorkPosition->id;
                            if (!$parentWorkPosition->save()) {
                                Log::error("Error al actualizar son_id en work position padre", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'child_work_position_id' => $childWorkPosition->id
                                ]);
                                continue;
                            }

                            Log::info("Nuevo work position clonado del padre al hijo", [
                                'parent_work_position_id' => $parentWorkPosition->id,
                                'child_work_position_id' => $childWorkPosition->id,
                                'parent_hotel_id' => $HOTEL_ID_PARENT,
                                'child_hotel_id' => $HOTEL_ID_CHILD
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Error al crear work position hijo", [
                                'parent_work_position_id' => $parentWorkPosition->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString()
                            ]);
                            continue;
                        }
                    } else {
                        // Si el padre ya tiene son_id, actualizar el work position hijo existente
                        $childWorkPosition = WorkPosition::find($parentWorkPosition->son_id);
                        if ($childWorkPosition) {
                            try {
                                // Actualizar el work position hijo
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
                                    'child_work_position_id' => $childWorkPosition->id,
                                    'parent_hotel_id' => $HOTEL_ID_PARENT,
                                    'child_hotel_id' => $HOTEL_ID_CHILD
                                ]);
                            } catch (\Exception $e) {
                                Log::error("Error al actualizar work position hijo", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'child_work_position_id' => $childWorkPosition->id,
                                    'error' => $e->getMessage()
                                ]);
                                continue;
                            }
                        } else {
                            try {
                                // Si el hijo fue eliminado, crear uno nuevo con el mismo son_id
                                $childWorkPosition = new WorkPosition();
                                $childWorkPosition->id = $parentWorkPosition->son_id;
                                $childWorkPosition->hotel_id = $HOTEL_ID_CHILD;
                                $childWorkPosition->exists = false;

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
                                    'child_work_position_id' => $childWorkPosition->id,
                                    'parent_hotel_id' => $HOTEL_ID_PARENT,
                                    'child_hotel_id' => $HOTEL_ID_CHILD
                                ]);
                            } catch (\Exception $e) {
                                Log::error("Error al restaurar work position hijo", [
                                    'parent_work_position_id' => $parentWorkPosition->id,
                                    'son_id' => $parentWorkPosition->son_id,
                                    'error' => $e->getMessage()
                                ]);
                                continue;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al procesar work position", [
                        'parent_work_position_id' => $parentWorkPosition ? $parentWorkPosition->id : 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // Marcar como inactivos (status=0) los work positions huérfanos del hijo
            $validSonIds = $parentWorkPositions->pluck('son_id')->filter();
            if ($validSonIds->isNotEmpty()) {
                $orphanedWorkPositions = WorkPosition::where('hotel_id', $HOTEL_ID_CHILD)
                    ->whereNotIn('id', $validSonIds)
                    ->get();

                foreach ($orphanedWorkPositions as $orphanedWorkPosition) {
                    try {
                        // Marcar el work position como inactivo
                        $orphanedWorkPosition->status = 0;
                        $orphanedWorkPosition->save();

                        Log::info("Work position huérfano marcado como inactivo", [
                            'work_position_id' => $orphanedWorkPosition->id,
                            'hotel_id' => $HOTEL_ID_CHILD
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error al marcar work position como inactivo", [
                            'work_position_id' => $orphanedWorkPosition->id ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Work positions clonados exitosamente", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD,
                'parent_work_positions_count' => $parentWorkPositions->count()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al clonar work positions: " . $e->getMessage());
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
