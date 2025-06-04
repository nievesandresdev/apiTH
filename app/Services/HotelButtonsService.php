<?php

namespace App\Services;

use App\Models\HotelButton;
use App\Models\Hotel;

class HotelButtonsService {

    public function getHotelButtons($modelHotel) {
        $buttons = $modelHotel->buttons()->get();

        // Filtrar botón de Check-In si el servicio no está habilitado
        if (!$modelHotel->checkin_service_enabled) {
            $buttons = $buttons->filter(function($button) {
                return strtolower($button->name) !== 'check-in';
            });
        }

        // Separar en una sola iteración
        $visible = $buttons->filter(fn($button) => $button->is_visible);
        $hidden = $buttons->filter(fn($button) => !$button->is_visible);

        return [
            'visible' => $visible,
            'hidden' => $hidden,
            'totalVisible' => $visible->count(),
            'totalHidden' => $hidden->count(),
            'total' => $buttons->count()
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
                    'is_visible' => $button['is_visible']
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

        if (!$button) {
            return false;
        }

        // Si el botón está oculto y lo vamos a mostrar
        if (!$button->is_visible) {
            // Obtenemos el último orden de los botones visibles
            $lastOrder = HotelButton::where('is_visible', true)
                ->where('hotel_id', $button->hotel_id)
                ->max('order') ?? -1;

            // Actualizamos el botón con el nuevo orden (último) y se hacemos visible
            $button->order = $lastOrder + 1;
            $button->is_visible = true;
        } else {
            // Si lo estamos ocultando, agarramos el último orden de los botones ocultos
            $lastHiddenOrder = HotelButton::where('is_visible', false)
                ->where('hotel_id', $button->hotel_id)
                ->max('order') ?? -1;

            // Actualizamos el botón con el nuevo orden (último) y se hace invisible
            $button->order = $lastHiddenOrder + 1;
            $button->is_visible = false;
        }

        $button->save();
        return $button;
    }
}


