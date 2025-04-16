<?php

namespace App\Services\Hoster\CloneHotel;

use App\Models\HotelCommunication;
use Illuminate\Support\Facades\Log;
class CloneTriggersCommunicationsHotel
{
    public function cloneHotelCommunications($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        // Obtener todas las configuraciones de comunicación del hotel padre
        $parentCommunications = HotelCommunication::where('hotel_id', $HOTEL_ID_PARENT)->get();

        //Log::info('parentCommunications '.json_encode($parentCommunications->count(), JSON_PRETTY_PRINT));

        if($parentCommunications->count() === 0){
            // Crear configuración por defecto para el padre
            $parentCommunication = HotelCommunication::create([
                'hotel_id' => $HOTEL_ID_PARENT,
                'type' => 'email',
                'welcome_email' => true,
                'pre_checkin_email' => true,
                'post_checkin_email' => true,
                'checkout_email' => true,
                'pre_checkout_email' => true,
                'new_chat_email' => true,
                'referent_email' => true,
            ]);

            // Crear configuración por defecto para el hijo
            $childCommunication = HotelCommunication::create([
                'hotel_id' => $HOTEL_ID_CHILD,
                'type' => 'email',
                'welcome_email' => true,
                'pre_checkin_email' => true,
                'post_checkin_email' => true,
                'checkout_email' => true,
                'pre_checkout_email' => true,
                'new_chat_email' => true,
                'referent_email' => true,
            ]);

            // Establecer la relación padre-hijo
            $parentCommunication->son_id = $childCommunication->id;
            $parentCommunication->save();

            return;
        }

        // Array para guardar los IDs de las configuraciones hijas válidas
        $validSonIds = [];

        foreach ($parentCommunications as $parentCommunication) {
            if ($parentCommunication->son_id == null) {
                // Primera clonación - Nueva configuración en el padre
                $childCommunication = $parentCommunication->replicate();
                $childCommunication->hotel_id = $HOTEL_ID_CHILD;
                $childCommunication->son_id = null; // El hijo no debe tener son_id
                $childCommunication->save();

                // Actualizar el padre con el ID del hijo
                $parentCommunication->son_id = $childCommunication->id;
                $parentCommunication->save();

                $validSonIds[] = $childCommunication->id;
            } else {
                // Actualización
                $childCommunication = HotelCommunication::find($parentCommunication->son_id);
                if ($childCommunication) {
                    // Actualizar los datos del hijo
                    $childCommunication->fill($parentCommunication->only([
                        'type',
                        'welcome_email',
                        'pre_checkin_email',
                        'post_checkin_email',
                        'checkout_email',
                        'pre_checkout_email',
                        'new_chat_email',
                        'referent_email'
                    ]));
                    $childCommunication->son_id = null; // El hijo no debe tener son_id
                    $childCommunication->save();
                    $validSonIds[] = $childCommunication->id;
                } else {
                    // Si el hijo fue eliminado, crear uno nuevo con el mismo ID que estaba en son_id
                    $childCommunication = $parentCommunication->replicate();
                    $childCommunication->id = $parentCommunication->son_id; // Mantener el mismo ID
                    $childCommunication->hotel_id = $HOTEL_ID_CHILD;
                    $childCommunication->son_id = null; // El hijo no debe tener son_id
                    $childCommunication->exists = false; // Forzar la inserción con el ID específico
                    $childCommunication->save();
                    $validSonIds[] = $childCommunication->id;
                }
            }
        }

        // Eliminar las configuraciones del hijo que no están enlazadas con el padre
        HotelCommunication::where('hotel_id', $HOTEL_ID_CHILD)
            ->whereNotIn('id', $validSonIds)
            ->delete();

        // Eliminar las configuraciones del padre que no tienen son_id (si se eliminaron en el padre)
        HotelCommunication::where('hotel_id', $HOTEL_ID_PARENT)
            ->whereNull('son_id')
            ->delete();
    }
}
