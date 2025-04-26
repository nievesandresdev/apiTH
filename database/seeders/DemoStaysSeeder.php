<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\Stay;
use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoStaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener todos los hoteles
        $hotels = Hotel::all();

        foreach ($hotels as $hotel) {
            // Verificar si el hotel tiene alguna estancia demo
            $hasDemoStay = $hotel->stays()->where('is_demo', true)->exists();

            if (!$hasDemoStay) {
                // Crear un huésped demo específico para este hotel
                $demoGuest = Guest::firstOrCreate(
                    ['email' => "huesped{$hotel->id}@example.com"],
                    [
                        'name' => $hotel->name,
                        'lastname' => 'Demo',
                        'phone' => '123456789',
                        'lang_web' => $hotel->language_default_webapp ?? 'es',
                        'acronym' => substr($hotel->name, 0, 2),
                        'color' => '5E7A96',
                        'complete_checkin_data' => true,
                        'checkin_email' => true,
                        'off_email' => false,
                        'password' => Hash::make('12345678')
                    ]
                );

                $checkIn = Carbon::now()->startOfDay();
                $checkOut = Carbon::create(2031, 12, 31, 0, 59, 0);

                // Crear la estancia demo
                $demoStay = Stay::create([
                    'hotel_id' => $hotel->id,
                    'room' => 'Demo Estancia',
                    'number_guests' => 1,
                    'language' => $hotel->language_default_webapp ?? 'es',
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'hour_checkin' => '14:00',
                    'hour_checkout' => '12:00',
                    'pending_queries_seen' => 0,
                    'sessions' => [],
                    'is_demo' => true
                ]);

                // Asociar el huésped demo con la estancia
                $demoStay->guests()->attach($demoGuest->id, ['chain_id' => null]);

                $this->command->info("Created demo stay for hotel: {$hotel->name} with guest: {$demoGuest->email} from {$checkIn->format('Y-m-d')} to {$checkOut->format('Y-m-d H:i')}");
            } else {
                $this->command->info("Hotel {$hotel->name} already has a demo stay, skipping...");
            }
        }

        $this->command->info('Demo stays seeding completed!');
    }
}
