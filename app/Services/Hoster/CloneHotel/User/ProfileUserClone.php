<?php

namespace App\Services\Hoster\CloneHotel\User;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProfileUserClone
{
    public function handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD, $CHILD_OWNER_USER_ID, $stringDiff)
    {
        try {
            // Obtener todos los usuarios del hotel padre (excluyendo el owner)
            $parentUsers = User::whereHas('hotel', function ($query) use ($HOTEL_ID_PARENT) {
                $query->where('hotel_id', $HOTEL_ID_PARENT)
                      ->where('owner', 0); // Excluir el owner
            })->get();

            if ($parentUsers->isEmpty()) {
                Log::info("No hay usuarios adicionales en el hotel padre: {$HOTEL_ID_PARENT}");
                return true; // No hay usuarios para clonar, pero no es un error
            }

            foreach ($parentUsers as $parentUser) {
                // Si el padre no tiene son_id, significa que es un nuevo usuario a clonar
                if (!$parentUser->son_id) {
                    // Crear nuevo usuario hijo
                    $childUser = new User();
                    $childUser->owner = 0; // Siempre será 0 en el hijo

                    // Actualizar el usuario hijo con los datos del padre
                    $this->updateUser($parentUser, $childUser, $stringDiff);

                    // Actualizar el perfil del usuario hijo
                    $this->updateProfile($parentUser, $childUser);

                    // Actualizar la relación hotel_user
                    $this->updateHotelUserRelation($HOTEL_ID_CHILD, $childUser);

                    // Actualizar el son_id en el usuario padre
                    $parentUser->son_id = $childUser->id;
                    $parentUser->save();

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

                        // Actualizar el perfil del usuario hijo
                        $this->updateProfile($parentUser, $childUser);

                        // Actualizar la relación hotel_user
                        $this->updateHotelUserRelation($HOTEL_ID_CHILD, $childUser);

                        Log::info("Usuario hijo actualizado", [
                            'parent_user_id' => $parentUser->id,
                            'child_user_id' => $childUser->id
                        ]);
                    } else {
                        // Si el hijo fue eliminado (del=1), restaurarlo
                        $childUser = User::withTrashed()->find($parentUser->son_id);
                        if ($childUser) {
                            $childUser->del = 0; // Restaurar el usuario
                            $childUser->owner = 0;

                            // Actualizar el usuario hijo
                            $this->updateUser($parentUser, $childUser, $stringDiff);

                            // Actualizar el perfil del usuario hijo
                            $this->updateProfile($parentUser, $childUser);

                            // Actualizar la relación hotel_user
                            $this->updateHotelUserRelation($HOTEL_ID_CHILD, $childUser);

                            Log::info("Usuario hijo restaurado", [
                                'parent_user_id' => $parentUser->id,
                                'child_user_id' => $childUser->id
                            ]);
                        }
                    }
                }
            }

            // Marcar como eliminados (del=1) los usuarios huérfanos del hijo
            $validSonIds = $parentUsers->pluck('son_id')->filter();
            if ($validSonIds->isNotEmpty()) {
                User::whereHas('hotel', function ($query) use ($HOTEL_ID_CHILD) {
                    $query->where('hotel_id', $HOTEL_ID_CHILD)
                          ->where('owner', 0); // Solo marcar usuarios no owners
                })->whereNotIn('id', $validSonIds)
                  ->where('id', '!=', $CHILD_OWNER_USER_ID) // Excluir el owner del hijo
                  ->get()
                  ->each(function ($orphanedUser) use ($HOTEL_ID_CHILD) {
                      // Marcar el usuario como eliminado
                      $orphanedUser->del = 1;
                      $orphanedUser->save();

                      // Marcar el perfil como eliminado si existe
                      if ($orphanedUser->profile) {
                          $orphanedUser->profile->del = 1;
                          $orphanedUser->profile->save();
                      }

                      Log::info("Usuario huérfano marcado como eliminado", [
                          'user_id' => $orphanedUser->id,
                          'hotel_id' => $HOTEL_ID_CHILD
                      ]);
                  });
            }

            Log::info("Perfiles de usuarios clonados exitosamente", [
                'parent_hotel_id' => $HOTEL_ID_PARENT,
                'child_hotel_id' => $HOTEL_ID_CHILD,
                'parent_users_count' => $parentUsers->count()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Error al clonar perfiles de usuarios: " . $e->getMessage());
            return false;
        }
    }

    private function updateUser($parentUser, $childUser, $stringDiff)
    {
        // Formatear el email para insertar el stringDiff antes del @
        $emailParts = explode('@', $parentUser->email);
        $formattedEmail = $emailParts[0] . $stringDiff . '@' . $emailParts[1];

        // Convertir arrays a JSON
        $permissions = is_array($parentUser->permissions) ? json_encode($parentUser->permissions) : $parentUser->permissions;
        $notifications = is_array($parentUser->notifications) ? json_encode($parentUser->notifications) : $parentUser->notifications;

        // Actualizar el usuario hijo con los datos del padre
        $childUser->fill([
            'name' => $parentUser->name . $stringDiff, // Agregar el stringDiff al final del nombre
            'email' => $formattedEmail,
            'password' => $parentUser->password, // Copiar la contraseña del padre
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
            'slug' => $parentUser->slug,
            'owner' => 0, // Siempre será 0 en el hijo
            'color' => $parentUser->color ?? $this->generateRandomColor() // Si no tiene color, generar uno aleatorio
        ]);

        $childUser->save();
    }

    private function generateRandomColor()
    {
        $availableColors = ['C5DC69', 'FB5607', 'FF006E', '8338EC', '3A86FF', '8AC926', 'B12E2E', '1982C4', 'FF595E'];
        return $availableColors[array_rand($availableColors)];
    }

    private function updateProfile($parentUser, $childUser)
    {
        $parentProfile = $parentUser->profile;
        $childProfile = $childUser->profile;

        if (!$parentProfile) {
            Log::error("Perfil del usuario padre no encontrado: {$parentUser->id}");
            return;
        }

        if (!$childProfile) {
            // Crear nuevo perfil para el usuario hijo
            $childProfile = new Profile();
            $childProfile->user_id = $childUser->id;
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
            'work_position_id' => $parentProfile->work_position_id
        ]);

        $childProfile->save();

        // Actualizar el son_id en el perfil padre
        $parentProfile->son_id = $childProfile->id;
        $parentProfile->save();
    }

    private function updateHotelUserRelation($HOTEL_ID_CHILD, $childUser)
    {
        // Actualizar o crear la relación del hotel hijo con el usuario hijo
        DB::table('hotel_user')
            ->updateOrInsert(
                [
                    'hotel_id' => $HOTEL_ID_CHILD,
                    'user_id' => $childUser->id
                ],
                [
                    'manager' => true,
                    'permissions' => json_encode([]),
                    'is_default' => true
                ]
            );
    }
}
