<?php

namespace App\Services\Hoster\CloneHotel;

use App\Models\Legal\LegalGeneral;

class CloneGeneralHotel
{
    public function cloneLegalGeneral($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        $legalGeneralParentItems = LegalGeneral::where('hotel_id', $HOTEL_ID_PARENT)->get();

        foreach ($legalGeneralParentItems as $parentItem) {
            if ($parentItem->son_id == null) {
                // Clonar
                $childItem = $parentItem->replicate();
                $childItem->hotel_id = $HOTEL_ID_CHILD;
                $childItem->parent_id = $parentItem->id;
                $childItem->save();

                // Actualizar el padre con el ID del hijo
                $parentItem->son_id = $childItem->id;
                $parentItem->save();
            } else {
                // Si el hijo ya fue creado antes
                $childItem = LegalGeneral::find($parentItem->son_id);

                if ($childItem) {
                    $childItem->hotel_id = $HOTEL_ID_CHILD;
                    $childItem->fill($parentItem->only([
                        'name', 'address', 'nif', 'email', 'protection', 'email_protection'
                    ]));
                    $childItem->save();
                } else {
                    // Si no existe el hijo, crearlo con el mismo ID (opcional)
                    $childItem = new LegalGeneral();
                    $childItem->id = $parentItem->son_id; // ⚠️ Solo si estás seguro de manejar IDs manualmente
                    $childItem->hotel_id = $HOTEL_ID_CHILD;
                    $childItem->parent_id = $parentItem->id;
                    $childItem->fill($parentItem->only([
                        'name', 'address', 'nif', 'email', 'protection', 'email_protection'
                    ]));
                    $childItem->save();
                }
            }
        }

        // Limpieza final: elimina hijos que no estén enlazados con el padre
        $validSonIds = LegalGeneral::where('hotel_id', $HOTEL_ID_PARENT)->pluck('son_id');
        LegalGeneral::where('hotel_id', $HOTEL_ID_CHILD)
            ->whereNotIn('id', $validSonIds)
            ->delete();
    }
}
