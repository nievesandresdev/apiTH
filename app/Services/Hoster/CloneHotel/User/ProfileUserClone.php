<?php

namespace App\Services\Hoster\CloneHotel\User;

use App\Models\User;
use App\Models\Profile;
use App\Models\WorkPosition;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProfileUserClone
{
    public function handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD, $CHILD_OWNER_USER_ID, $stringDiff)
    {
        try {
            // Obtener todos los usuarios del hotel padre
            $parentUsers = User::whereHas('hotel', function ($query) use ($HOTEL_ID_PARENT) {
                $query->where('hotel_id', $HOTEL_ID_PARENT);
            })->get();

            if ($parentUsers->isEmpty()) {
                Log::info("No hay usuarios en el hotel padre: {$HOTEL_ID_PARENT}");
                return true; // No hay usuarios para clonar, pero no es un error
            }

            Log::info("Iniciando clonación de perfiles de usuario", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD,
                'total_users' => $parentUsers->count()
            ]);

            // Obtener el mapeo de work positions del padre al hijo
            $workPositionMapping = $this->getWorkPositionMapping($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);

            foreach ($parentUsers as $parentUser) {
                try {
                    // Si el padre no tiene son_id, significa que es un nuevo usuario a clonar
                    if (!$parentUser->son_id) {
                        // Crear nuevo usuario en el hotel hijo
                        $childUser = new User();

                        // Actualizar el usuario hijo con los datos del padre
                        $this->updateUser($parentUser, $childUser, $stringDiff);

                        if (!$childUser->id) {
                            Log::error("Error al crear usuario hijo", [
                                'parent_user_id' => $parentUser->id,
                                'parent_hotel_id' => $HOTEL_ID_PARENT,
                                'child_hotel_id' => $HOTEL_ID_CHILD
                            ]);
                            continue;
                        }

                        // Actualizar el son_id en el usuario padre
                        $parentUser->son_id = $childUser->id;
                        $parentUser->save();

                        // Asociar el usuario hijo con el hotel hijo
                        $childUser->hotel()->attach($HOTEL_ID_CHILD, [
                            'permissions' => json_encode([])
                        ]);

                        // Clonar el perfil del usuario
                        $this->cloneProfile($parentUser, $childUser, $workPositionMapping, $HOTEL_ID_PARENT, $HOTEL_ID_CHILD);

                        Log::info("Nuevo usuario clonado del padre al hijo", [
                            'parent_user_id' => $parentUser->id,
                            'child_user_id' => $childUser->id,
                            'parent_hotel_id' => $HOTEL_ID_PARENT,
                            'child_hotel_id' => $HOTEL_ID_CHILD
                        ]);
                    } else {
                        // Si el padre ya tiene son_id, actualizar el usuario hijo existente
                        $childUser = User::find($parentUser->son_id);
                        if ($childUser) {
                            // Actualizar el usuario hijo
                            $this->updateUser($parentUser, $childUser, $stringDiff);

                            // Actualizar la relación con el hotel hijo
                            $childUser->hotel()->sync([$HOTEL_ID_CHILD => ['permissions' => json_encode([])]]);

                            // Actualizar el perfil del usuario
                            $this->cloneProfile($parentUser, $childUser, $workPositionMapping, $HOTEL_ID_PARENT, $HOTEL_ID_CHILD);

                            Log::info("Usuario hijo actualizado", [
                                'parent_user_id' => $parentUser->id,
                                'child_user_id' => $childUser->id
                            ]);
                        } else {
                            Log::warning("Usuario hijo no encontrado", [
                                'parent_user_id' => $parentUser->id,
                                'son_id' => $parentUser->son_id
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al procesar usuario", [
                        'parent_user_id' => $parentUser->id ?? 'unknown',
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // Marcar como inactivos (status=0) los usuarios huérfanos del hijo
            $validSonIds = $parentUsers->pluck('son_id')->filter();
            if ($validSonIds->isNotEmpty()) {
                $orphanedUsers = User::whereHas('hotel', function ($query) use ($HOTEL_ID_CHILD) {
                    $query->where('hotel_id', $HOTEL_ID_CHILD);
                })->whereNotIn('id', $validSonIds)
                  ->where('id', '!=', $CHILD_OWNER_USER_ID)
                  ->get();

                foreach ($orphanedUsers as $orphanedUser) {
                    try {
                        // Marcar el usuario como inactivo
                        $orphanedUser->status = 0;
                        $orphanedUser->save();

                        Log::info("Usuario huérfano marcado como inactivo", [
                            'user_id' => $orphanedUser->id,
                            'hotel_id' => $HOTEL_ID_CHILD
                        ]);
                    } catch (\Exception $e) {
                        Log::error("Error al marcar usuario como inactivo", [
                            'user_id' => $orphanedUser->id ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            Log::info("Perfiles de usuario clonados exitosamente", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD,
                'parent_users_count' => $parentUsers->count()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al clonar perfiles de usuario: " . $e->getMessage());
            return false;
        }
    }

    private function getWorkPositionMapping($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        $mapping = [];
        $parentWorkPositions = WorkPosition::where('hotel_id', $HOTEL_ID_PARENT)->get();

        foreach ($parentWorkPositions as $parentWorkPosition) {
            if ($parentWorkPosition->son_id) {
                $mapping[$parentWorkPosition->id] = $parentWorkPosition->son_id;
            }
        }

        return $mapping;
    }

    private function cloneProfile($parentUser, $childUser, $workPositionMapping, $HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        $parentProfile = $parentUser->profile;
        if (!$parentProfile) {
            Log::warning("No se encontró perfil para el usuario padre", [
                'user_id' => $parentUser->id
            ]);
            return;
        }

        $childProfile = $childUser->profile ?? new Profile();
        $childProfile->user_id = $childUser->id;

        // Obtener el work position del padre
        $parentWorkPosition = WorkPosition::find($parentProfile->work_position_id);

        // Si el work position del padre existe y tiene un son_id, usamos ese ID para el hijo
        $childWorkPositionId = null;
        if ($parentWorkPosition && $parentWorkPosition->son_id) {
            $childWorkPositionId = $parentWorkPosition->son_id;
            Log::info("Mapeando work position de padre a hijo", [
                'parent_work_position_id' => $parentWorkPosition->id,
                'child_work_position_id' => $childWorkPositionId,
                'parent_hotel_id' => $parentWorkPosition->hotel_id,
                'child_hotel_id' => $HOTEL_ID_CHILD
            ]);
        } else {
            Log::warning("No se encontró work position hijo correspondiente", [
                'parent_work_position_id' => $parentProfile->work_position_id,
                'parent_hotel_id' => $HOTEL_ID_PARENT
            ]);
        }

        // Actualizar el perfil hijo con los datos del padre
        $childProfile->fill([
            'firstname' => $parentProfile->firstname,
            'lastname' => $parentProfile->lastname,
            'gender' => $parentProfile->gender,
            'phone' => $parentProfile->phone,
            'gestor' => $parentProfile->gestor,
            'razon' => $parentProfile->razon,
            'nif' => $parentProfile->nif,
            'identify' => $parentProfile->identify,
            'city' => $parentProfile->city,
            'cp' => $parentProfile->cp,
            'address' => $parentProfile->address,
            'province' => $parentProfile->province,
            'type' => $parentProfile->type,
            'platform_steps' => $parentProfile->platform_steps,
            'goal_achieved' => $parentProfile->goal_achieved,
            'image' => $parentProfile->image,
            'logo' => $parentProfile->logo,
            'name_hoster' => $parentProfile->name_hoster,
            'work_position_id' => $childWorkPositionId
        ]);

        $childProfile->save();

        // Actualizar el son_id en el perfil padre si no existe
        if (!$parentProfile->son_id) {
            $parentProfile->son_id = $childProfile->id;
            $parentProfile->save();
        }

        Log::info("Perfil clonado", [
            'parent_profile_id' => $parentProfile->id,
            'child_profile_id' => $childProfile->id,
            'parent_user_id' => $parentUser->id,
            'child_user_id' => $childUser->id,
            'parent_work_position_id' => $parentProfile->work_position_id,
            'child_work_position_id' => $childWorkPositionId
        ]);
    }

    private function updateUser($parentUser, $childUser, $stringDiff)
    {
        // Formatear el email para insertar 'B' antes del @
        $emailParts = explode('@', $parentUser->email);
        $timestamp = time();
        $formattedEmail = $emailParts[0] . $stringDiff . $timestamp . '@' . $emailParts[1];

        // Convertir arrays a JSON
        $permissions = is_array($parentUser->permissions) ? json_encode($parentUser->permissions) : $parentUser->permissions;
        $notifications = is_array($parentUser->notifications) ? json_encode($parentUser->notifications) : $parentUser->notifications;

        // Actualizar el usuario hijo con los datos del padre
        $childUser->fill([
            'name' => $parentUser->name . $stringDiff,
            'email' => $formattedEmail,
            'password' => $parentUser->password,
            'code' => $parentUser->code,
            'sessions_current_period' => $parentUser->sessions_current_period,
            'last_session' => $parentUser->last_session,
            'del' => $parentUser->del,
            'permissions' => $permissions,
            'notifications' => $notifications,
            'status' => $parentUser->status,
            'periodicity_chat' => $parentUser->periodicity_chat,
            'periodicity_stay' => $parentUser->periodicity_stay,
            'feedback_last_notified_at' => $parentUser->feedback_last_notified_at,
            'chat_last_notified_at' => $parentUser->chat_last_notified_at,
            'stripe_id' => $parentUser->stripe_id,
            'pm_type' => $parentUser->pm_type,
            'trial_duration' => $parentUser->trial_duration,
            'trial_ends_at' => $parentUser->trial_ends_at,
            'trial_starts_at' => $parentUser->trial_starts_at,
            'chain_id' => $parentUser->chain_id,
            'owner' => 0,
            'color' => $parentUser->color ?? $this->generateRandomColor()
        ]);

        $childUser->save();
    }

    private function generateRandomColor()
    {
        $availableColors = ['C5DC69', 'FB5607', 'FF006E', '8338EC', '3A86FF', '8AC926', 'B12E2E', '1982C4', 'FF595E'];
        return $availableColors[array_rand($availableColors)];
    }
}
