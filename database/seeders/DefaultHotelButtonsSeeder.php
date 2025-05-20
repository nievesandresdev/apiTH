<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\HotelButton;

class DefaultHotelButtonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Este seeder crea botones por defecto solo para los hoteles que no tienen ningún botón.
     * Si un hotel ya tiene botones, será omitido.
     */
    public function run(): void
    {
        // Botones por defecto que se crearán para hoteles sin botones
        $defaultButtons = [
            [
                'name' => 'Llamar',
                'icon' => '1.TH.PHONE.svg',
                'is_visible' => false,
                'order' => 0
            ],
            [
                'name' => 'Check-In',
                'icon' => '1.TH Check-in.svg',
                'is_visible' => false,
                'order' => 1
            ],
            [
                'name' => 'Normas del alojamiento',
                'icon' => 'normas.svg',
                'is_visible' => false,
                'order' => 2
            ],
            [
                'name' => 'Programa de referidos',
                'icon' => '1.TH.Referidos.svg',
                'is_visible' => false,
                'order' => 3
            ],
            [
                'name' => 'Reserva tu estancia',
                'icon' => '1.TH.RESERVA.AGENDA.svg',
                'is_visible' => false,
                'order' => 4
            ],
            [
                'name' => 'Redes WiFi',
                'icon' => '1.TH.WiFi.svg',
                'is_visible' => false,
                'order' => 5
            ]
        ];

        // Obtener todos los hoteles
        $hotels = Hotel::all();

        foreach ($hotels as $hotel) {
            // Verificar si el hotel ya tiene botones
            $hasButtons = HotelButton::where('hotel_id', $hotel->id)->exists();

            // Si el hotel no tiene botones, crear los botones por defecto
            if (!$hasButtons) {
                foreach ($defaultButtons as $button) {
                    HotelButton::create([
                        'hotel_id' => $hotel->id,
                        'name' => $button['name'],
                        'icon' => $button['icon'],
                        'is_visible' => $button['is_visible'],
                        'order' => $button['order']
                    ]);
                }
            }
        }
    }
}
