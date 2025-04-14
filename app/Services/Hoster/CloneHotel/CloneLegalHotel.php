<?php

namespace App\Services\Hoster\CloneHotel;

use App\Models\Legal\LegalGeneral;
use App\Models\Legal\PolicyLegals;
use Illuminate\Support\Facades\Log;
class CloneLegalHotel
{
    public function handle($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        $this->cloneLegalGeneral($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
        $this->clonePolicyLegals($HOTEL_ID_PARENT, $HOTEL_ID_CHILD);
    }

    public function cloneLegalGeneral($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        $legalGeneralParentItems = LegalGeneral::where('hotel_id', $HOTEL_ID_PARENT)->get();

        foreach ($legalGeneralParentItems as $parentItem) {
            if ($parentItem->son_id == null) {
                // Clonar
                $childItem = $parentItem->replicate();
                $childItem->hotel_id = $HOTEL_ID_CHILD;
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
                    $childItem->id = $parentItem->son_id;
                    $childItem->hotel_id = $HOTEL_ID_CHILD;
                    $childItem->fill($parentItem->only([
                        'name', 'address', 'nif', 'email', 'protection', 'email_protection'
                    ]));
                    $childItem->save();
                }
            }
        }

        // elimina datos que no estén enlazados con el padre
        $validSonIds = LegalGeneral::where('hotel_id', $HOTEL_ID_PARENT)->pluck('son_id');
        LegalGeneral::where('hotel_id', $HOTEL_ID_CHILD)
            ->whereNotIn('id', $validSonIds)
            ->delete();
    }

    public function clonePolicyLegals($HOTEL_ID_PARENT, $HOTEL_ID_CHILD)
    {
        // Obtener todas las políticas del hotel padre
        $parentPolicies = PolicyLegals::where('hotel_id', $HOTEL_ID_PARENT)->get();

        // Array para guardar los IDs de las políticas hijas válidas
        $validSonIds = [];

        foreach ($parentPolicies as $parentPolicy) {
            if ($parentPolicy->son_id == null) {
                // Primera clonación - Nueva norma en el padre
                $childPolicy = $parentPolicy->replicate();
                $childPolicy->hotel_id = $HOTEL_ID_CHILD;
                $childPolicy->son_id = null; // El hijo no debe tener son_id
                $childPolicy->save();

                // Actualizar el padre con el ID del hijo
                $parentPolicy->son_id = $childPolicy->id;
                $parentPolicy->save();

                $validSonIds[] = $childPolicy->id;
            } else {
                // Actualización
                $childPolicy = PolicyLegals::find($parentPolicy->son_id);
                if ($childPolicy) {
                    // Actualizar los datos del hijo
                    $childPolicy->fill($parentPolicy->only([
                        'title',
                        'description',
                        'penalization',
                        'penalization_details'
                    ]));
                    $childPolicy->son_id = null; // El hijo no debe tener son_id
                    $childPolicy->save();
                    $validSonIds[] = $childPolicy->id;
                } else {
                    // Si el hijo fue eliminado, crear uno nuevo con el mismo ID que estaba en son_id
                    $childPolicy = $parentPolicy->replicate();
                    $childPolicy->id = $parentPolicy->son_id; // Mantener el mismo ID
                    $childPolicy->hotel_id = $HOTEL_ID_CHILD;
                    $childPolicy->son_id = null; // El hijo no debe tener son_id
                    $childPolicy->exists = false; // Forzar la inserción con el ID específico
                    $childPolicy->save();
                    $validSonIds[] = $childPolicy->id;
                }
            }
        }

        // Eliminar las políticas del hijo que no están enlazadas con el padre
        PolicyLegals::where('hotel_id', $HOTEL_ID_CHILD)
            ->whereNotIn('id', $validSonIds)
            ->delete();

        // Eliminar las políticas del padre que no tienen son_id (si se eliminaron en el padre)
        PolicyLegals::where('hotel_id', $HOTEL_ID_PARENT)
            ->whereNull('son_id')
            ->delete();
    }
}
