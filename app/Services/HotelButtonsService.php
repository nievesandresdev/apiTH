<?php

namespace App\Services;

use App\Models\HotelButton;
use App\Models\Hotel;

class HotelButtonsService {

    public function getHotelButtons($modelHotel) {
        $buttons = $modelHotel->buttons()->get();
        $visibleCount = $buttons->where('is_visible', true)->count();
        $hiddenCount = $buttons->where('is_visible', false)->count();

        return [
            'visible' => $buttons->where('is_visible', true),
            'hidden' => $buttons->where('is_visible', false),
            'totalVisible' => $visibleCount,
            'totalHidden' => $hiddenCount,
            'total' => $visibleCount + $hiddenCount
        ];

        //return $modelHotel->buttons_home;
    }

    public function updateButtonsOrder($buttons)
    {
        // Actualizar el orden de cada botón según su posición en el array
        foreach ($buttons as $index => $button) {
            HotelButton::where('id', $button['id'])
                ->update([
                    'order' => $index,
                    'is_visible' => true
                ]);
        }

        return true;
    }

    public function getActiveHotelButtons($modelHotel) {
        $buttons = $modelHotel->activeButtons;

        return $buttons;
    }

    public function updateButtonVisibility($id)
    {
        $button = HotelButton::where('id', $id)->first();

       //return $button->is_visible;

        if (!$button) {
            return false;
        }

        // Si el botón está oculto y lo vamos a mostrar
        if (!$button->is_visible) {
            // Obtenemos el último orden de los botones visibles
            $lastOrder = HotelButton::where('is_visible', true)
                ->where('hotel_id', $button->hotel_id)
                ->max('order') ?? -1;

            // Actualizamos el botón con el nuevo orden (último) y lo hacemos visible
            $button->order = $lastOrder + 1;
            $button->is_visible = true;
        } else {
            // Si lo estamos ocultando, obtenemos el último orden de los botones ocultos
            $lastHiddenOrder = HotelButton::where('is_visible', false)
                ->where('hotel_id', $button->hotel_id)
                ->max('order') ?? -1;

            // Actualizamos el botón con el nuevo orden (último) y lo hacemos oculto
            $button->order = $lastHiddenOrder + 1;
            $button->is_visible = false;
        }

        $button->save();
        return $button;
    }
}


