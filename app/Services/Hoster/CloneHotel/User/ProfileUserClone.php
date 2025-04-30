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

            // Obtener todos los usuarios del hotel hijo
            $childUsers = User::whereHas('hotel', function ($query) use ($HOTEL_ID_CHILD) {
                $query->where('hotel_id', $HOTEL_ID_CHILD);
            })->get();

            Log::info("Iniciando sincronización de usuarios", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD,
                'parent_users_count' => $parentUsers->count(),
                'child_users_count' => $childUsers->count()
            ]);

            // Si el padre no tiene usuarios, eliminar todos los del hijo excepto el owner
            if ($parentUsers->isEmpty()) {
                foreach ($childUsers as $childUser) {
                    if ($childUser->id != $CHILD_OWNER_USER_ID) {
                        try {
                            // Eliminar la relación con el hotel
                            $childUser->hotel()->detach($HOTEL_ID_CHILD);

                            // Eliminar el perfil si existe
                            if ($childUser->profile) {
                                $childUser->profile->delete();
                            }

                            // Eliminar el usuario
                            $childUser->delete();

                            Log::info("Usuario eliminado del hotel hijo", [
                                'user_id' => $childUser->id,
                                'hotel_id' => $HOTEL_ID_CHILD
                            ]);
                        } catch (\Exception $e) {
                            Log::error("Error al eliminar usuario del hotel hijo", [
                                'user_id' => $childUser->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }
                return true;
            }

            // Obtener el mapeo de work positions del padre al hijo
            $workPositionMapping = $this->getWorkPositionMapping($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);

            foreach ($parentUsers as $parentUser) {
                try {
                    // Si el padre no tiene son_id, crear uno nuevo en el hijo
                    if (!$parentUser->son_id) {
                        // Crear nuevo usuario en el hotel hijo
                        $childUser = new User();
                        $this->updateUser($parentUser, $childUser, $stringDiff);
                        $childUser->save();

                        // Asociar el usuario hijo con el hotel hijo
                        $childUser->hotel()->attach($HOTEL_ID_CHILD, [
                            'permissions' => json_encode([])
                        ]);

                        // Clonar el perfil del usuario
                        $this->cloneProfile($parentUser, $childUser, $workPositionMapping, $HOTEL_ID_PARENT, $HOTEL_ID_CHILD);

                        // Actualizar el son_id en el usuario padre
                        $parentUser->son_id = $childUser->id;
                        $parentUser->save();

                        Log::info("Nuevo usuario clonado del padre al hijo", [
                            'parent_user_id' => $parentUser->id,
                            'child_user_id' => $childUser->id
                        ]);
                    } else {
                        // Si el padre ya tiene son_id, actualizar el usuario hijo existente
                        $childUser = User::find($parentUser->son_id);
                        if ($childUser) {
                            $this->updateUser($parentUser, $childUser, $stringDiff);
                            $childUser->save();

                            // Actualizar la relación con el hotel hijo
                            $childUser->hotel()->sync([$HOTEL_ID_CHILD => ['permissions' => json_encode([])]]);

                            // Actualizar el perfil del usuario
                            $this->cloneProfile($parentUser, $childUser, $workPositionMapping, $HOTEL_ID_PARENT, $HOTEL_ID_CHILD);

                            Log::info("Usuario hijo actualizado", [
                                'parent_user_id' => $parentUser->id,
                                'child_user_id' => $childUser->id
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al procesar usuario", [
                        'parent_user_id' => $parentUser->id,
                        'error' => $e->getMessage()
                    ]);
                    continue;
                }
            }

            // Eliminar usuarios del hijo que no están en el padre (excepto el owner)
            $validSonIds = $parentUsers->pluck('son_id')->filter();
            $orphanedUsers = User::whereHas('hotel', function ($query) use ($HOTEL_ID_CHILD) {
                $query->where('hotel_id', $HOTEL_ID_CHILD);
            })->whereNotIn('id', $validSonIds)
              ->where('id', '!=', $CHILD_OWNER_USER_ID)
              ->get();

            foreach ($orphanedUsers as $orphanedUser) {
                try {
                    // Eliminar la relación con el hotel
                    $orphanedUser->hotel()->detach($HOTEL_ID_CHILD);

                    // Eliminar el perfil si existe
                    if ($orphanedUser->profile) {
                        $orphanedUser->profile->delete();
                    }

                    // Eliminar el usuario
                    $orphanedUser->delete();

                    Log::info("Usuario huérfano eliminado del hotel hijo", [
                        'user_id' => $orphanedUser->id
                    ]);
                } catch (\Exception $e) {
                    Log::error("Error al eliminar usuario huérfano", [
                        'user_id' => $orphanedUser->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Error al sincronizar usuarios: " . $e->getMessage());
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
