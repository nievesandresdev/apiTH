<?php

namespace App\Services\Hoster\CloneHotel;

use App\Models\Guest;
use App\Models\Stay;
use App\Models\GuestStay;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CreateInifiteStay
{
    public function handle($hotelId)
    {
        try {
            // Buscar o crear el huésped
            $guest = Guest::where('email', 'presentacion@thehoster.es')->first();

            if (!$guest) {
                $guest = Guest::create([
                    'name' => 'Presentacion',
                    'email' => 'presentacion@thehoster.es',
                    'lang_web' => 'es'
                ]);
                Log::info('Huésped creado para estancia infinita', ['guest_id' => $guest->id]);
            }

            // Verificar si ya existe una estancia infinita
            $existingStay = Stay::where('hotel_id', $hotelId)
                ->where('guest_id', $guest->id)
                ->where('check_out', '>=', Carbon::create(2030, 12, 31))
                ->first();

            if ($existingStay) {
                Log::info('Estancia infinita ya existe', [
                    'stay_id' => $existingStay->id,
                    'guest_id' => $guest->id,
                    'hotel_id' => $hotelId
                ]);
                return $existingStay;
            }

            // Crear la estancia infinita
            $stay = Stay::create([
                'hotel_id' => $hotelId,
                'guest_id' => $guest->id,
                'number_guests' => 1,
                'check_in' => Carbon::now(),
                'check_out' => Carbon::create(2030, 12, 31),
                'language' => 'es'
            ]);

            // Verificar si ya existe el registro en guest_stay
            $existingGuestStay = GuestStay::where('stay_id', $stay->id)
                ->where('guest_id', $guest->id)
                ->first();

            if (!$existingGuestStay) {
                // Crear el registro en la tabla pivot guest_stay solo si no existe
                GuestStay::create([
                    'stay_id' => $stay->id,
                    'guest_id' => $guest->id,
                    'chain_id' => $stay->hotel->chain_id
                ]);
                Log::info('Guest stay creado', [
                    'stay_id' => $stay->id,
                    'guest_id' => $guest->id
                ]);
            }

            Log::info('Estancia infinita creada', [
                'stay_id' => $stay->id,
                'guest_id' => $guest->id,
                'hotel_id' => $hotelId
            ]);

            return $stay;

        } catch (\Exception $e) {
            Log::error('Error creando estancia infinita', [
                'error' => $e->getMessage(),
                'hotel_id' => $hotelId
            ]);
            throw $e;
        }
    }
}
