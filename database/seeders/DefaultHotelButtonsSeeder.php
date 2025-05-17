<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\HotelButton;

class DefaultHotelButtonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //  botones por defecto
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
                'icon' => '1.TH.RESERVA.AGENDA.SVG',
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

        $hotels = Hotel::all();

        foreach ($hotels as $hotel) {
            $existingButtons = HotelButton::where('hotel_id', $hotel->id)->count();

            if ($existingButtons === 0) {
                // Crear los botones por defecto para el hotel
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
