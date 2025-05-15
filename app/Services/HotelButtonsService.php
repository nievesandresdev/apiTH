<?php

namespace App\Services;

use App\Models\HotelButton;
use App\Models\Hotel;

class HotelButtonsService {

    public function getHotelButtons($modelHotel) {
        $buttons = $modelHotel->buttons;

        return [
            'visible' => $buttons['visible'],
            'hidden' => $buttons['hidden']
        ];
    }

    public function updateButtonsOrder($visibleButtons, $hiddenButtons)
    {
        // Actualizar botones visibles con su nuevo orden
        foreach ($visibleButtons as $index => $button) {
            HotelButton::where('id', $button['id'])
                ->update([
                    'order' => $index,
                    'is_visible' => true
                ]);
        }

        // Actualizar botones ocultos
        foreach ($hiddenButtons as $button) {
            HotelButton::where('id', $button['id'])
                ->update([
                    'is_visible' => false
                ]);
        }

        return true;
    }
}


