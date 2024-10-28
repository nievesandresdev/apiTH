<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Chain;
use App\Models\User;
use App\Models\Hotel;
use Carbon\Carbon;

class RegisterChainsWithoutOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Usuarios independientes
        $independentUsers = DB::table('users')
            ->join('hotel_user', 'users.id', '=', 'hotel_user.user_id')
            ->join('hotels', 'hotel_user.hotel_id', '=', 'hotels.id')
            ->select('users.id as user_id', 'users.name as user_name', DB::raw('COUNT(hotel_user.hotel_id) as total_hotels'), DB::raw('MAX(hotels.subdomain) as subdomain'))
            ->whereNull('hotels.chain_id')
            ->groupBy('users.id')
            ->having('total_hotels', '=', 1)
            ->get();

        foreach ($independentUsers as $user) {
            // Verificar que el subdomain no sea nulo antes de crear la cadena
            if (empty($user->subdomain)) {
                continue;
            }

            $chain = Chain::create([
                'subdomain' => $user->subdomain,
                'type' => 'INDEPENDENT',
                'created_at' => Carbon::now(),
            ]);

            // Verificar que el usuario y el hotel existen antes de actualizar
            User::where('id', $user->user_id)->update(['chain_id' => $chain->id]);
            Hotel::where('subdomain', $user->subdomain)->update(['chain_id' => $chain->id]);
        }

        // Usuarios de cadena
        $chainUsers = DB::table('users')
            ->join('hotel_user', 'users.id', '=', 'hotel_user.user_id')
            ->join('hotels', 'hotel_user.hotel_id', '=', 'hotels.id')
            ->select('users.id as user_id', 'users.name as user_name', DB::raw('GROUP_CONCAT(hotels.subdomain) as subdomains'), DB::raw('COUNT(hotel_user.hotel_id) as total_hotels'))
            ->where('users.owner', 1)
            ->groupBy('users.id')
            ->having('total_hotels', '>', 1) // Solo usuarios con más de un hotel
            ->get();

        foreach ($chainUsers as $user) {
            // Encuentra el primer hotel válido y verifica que no sea nulo
            $firstValidHotel = DB::table('hotels')
                ->join('hotel_user', 'hotels.id', '=', 'hotel_user.hotel_id')
                ->where('hotel_user.user_id', $user->user_id)
                ->orderBy('hotels.id')
                ->select('hotels.subdomain', 'hotels.id')
                ->first();

            if (!$firstValidHotel || empty($firstValidHotel->subdomain)) {
                continue;
            }

            $chain = Chain::create([
                'subdomain' => $firstValidHotel->subdomain,
                'type' => 'CHAIN',
                'created_at' => Carbon::now(),
            ]);

            // Verificar que el usuario existe antes de actualizar
            User::where('id', $user->user_id)->update(['chain_id' => $chain->id]);

            $allHotels = DB::table('hotels')
                ->join('hotel_user', 'hotels.id', '=', 'hotel_user.hotel_id')
                ->where('hotel_user.user_id', $user->user_id)
                ->select('hotels.subdomain', 'hotels.id')
                ->get();

            foreach ($allHotels as $hotel) {
                if ($hotel) {
                    Hotel::where('id', $hotel->id)->update(['chain_id' => $chain->id]);
                }
            }
        }

        // Asignar chain_id a los usuarios con parent_id
        $childUsers = User::whereNotNull('parent_id')->get();

        foreach ($childUsers as $childUser) {
            $ownerChainId = User::where('id', $childUser->parent_id)->value('chain_id');

            // Si el chain_id del propietario no existe, omitir este usuario hijo
            if (!$ownerChainId) {
                continue;
            }

            // Actualizar el chain_id del usuario hijo
            User::where('id', $childUser->id)->update(['chain_id' => $ownerChainId]);
        }
    }
}
