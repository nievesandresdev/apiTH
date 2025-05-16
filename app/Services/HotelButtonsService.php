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
        // Primero, actualizamos todos los botones a no visibles
        HotelButton::whereIn('id', array_merge(
            array_column($visibleButtons, 'id'),
            array_column($hiddenButtons, 'id')
        ))->update(['is_visible' => false]);

        // Luego actualizamos los botones visibles con su nuevo orden
        foreach ($visibleButtons as $index => $button) {
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
}


