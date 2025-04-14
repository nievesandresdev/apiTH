<?php

namespace App\Services\Hoster\CloneHotel;

use App\Models\Chain;
use App\Models\Hotel;
use Illuminate\Support\Facades\Log;

class CloneConfigGeneral
{
    public function cloneConfigGeneral($HOTEL_ID_PARENT, $HOTEL_ID_CHILD, $stringDiff)
    {
        try {
            // Obtener el hotel padre y su cadena
            $parentHotel = Hotel::with('chain')->find($HOTEL_ID_PARENT);
            if (!$parentHotel) {
                Log::error("Hotel padre no encontrado: {$HOTEL_ID_PARENT}");
                return false;
            }

            // Obtener el hotel hijo y su cadena
            $childHotel = Hotel::with('chain')->find($HOTEL_ID_CHILD);
            if (!$childHotel) {
                Log::error("Hotel hijo no encontrado: {$HOTEL_ID_CHILD}");
                return false;
            }

            // Verificar que ambas cadenas existen
            if (!$parentHotel->chain || !$childHotel->chain) {
                Log::error("Cadenas no encontradas para los hoteles: {$HOTEL_ID_PARENT}, {$HOTEL_ID_CHILD}");
                return false;
            }

            // Actualizar el subdominio de la cadena del hijo
            $childHotel->chain->update([
                'subdomain' => $parentHotel->chain->subdomain . $stringDiff
            ]);

            // Actualizar el idioma por defecto del hotel hijo
            $childHotel->update([
                'language_default_webapp' => $parentHotel->language_default_webapp
            ]);

               /*  Log::info("Configuraciones generales clonadas exitosamente", [
                    'parent_hotel_id' => $HOTEL_ID_PARENT,
                    'child_hotel_id' => $HOTEL_ID_CHILD,
                    'subdomain' => $parentHotel->chain->subdomain . $stringDiff,
                    'language_default_webapp' => $parentHotel->language_default_webapp
                ]);
 */
            return true;
        } catch (\Exception $e) {
            Log::error("Error al clonar configuraciones generales: " . $e->getMessage());
            return false;
        }
    }
}
