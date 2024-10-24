<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Chain;
use App\Models\User;
use App\Models\Hotel;
use Carbon\Carbon;

class RegisterIndependentChainsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //usuarios independientes
        $independentUsers = DB::table('users')
            ->join('hotel_user', 'users.id', '=', 'hotel_user.user_id')
            ->join('hotels', 'hotel_user.hotel_id', '=', 'hotels.id')
            ->select('users.id as user_id', 'users.name as user_name', DB::raw('COUNT(hotel_user.hotel_id) as total_hotels'), DB::raw('MAX(hotels.subdomain) as subdomain'))
            ->where('users.owner', 1)
            ->groupBy('users.id')
            ->having('total_hotels', '=', 1)
            ->get();

        foreach ($independentUsers as $user) {
            $chain = Chain::create([
                'subdomain' => $user->subdomain,
                'type' => 'INDEPENDENT',
                'created_at' => Carbon::now(),
            ]);

            User::where('id', $user->user_id)->update(['chain_id' => $chain->id]);
            Hotel::where('subdomain', $user->subdomain)->update(['chain_id' => $chain->id]);
        }

        // Obtener usuarios de cadena
        $chainUsers = DB::table('users')
            ->join('hotel_user', 'users.id', '=', 'hotel_user.user_id')
            ->join('hotels', 'hotel_user.hotel_id', '=', 'hotels.id')
            ->select('users.id as user_id', 'users.name as user_name', DB::raw('GROUP_CONCAT(hotels.subdomain) as subdomains'), DB::raw('COUNT(hotel_user.hotel_id) as total_hotels'))
            ->where('users.owner', 1)
            ->groupBy('users.id')
            ->having('total_hotels', '>', 1) // Solo usuarios con más de un hotel
            ->get();

        //  usuario de cadena
        foreach ($chainUsers as $user) {

            $firstValidHotel = DB::table('hotels')
                ->join('hotel_user', 'hotels.id', '=', 'hotel_user.hotel_id')
                ->where('hotel_user.user_id', $user->user_id)
                //->where('hotels.del', 0)
                ->orderBy('hotels.id')
                ->select('hotels.subdomain', 'hotels.id')
                ->first();

            if ($firstValidHotel) {
                $chain = Chain::create([
                    'subdomain' => $firstValidHotel->subdomain,
                    'type' => 'CHAIN',
                    'created_at' => Carbon::now(),
                ]);

                // Actualizar chain_id en el usuario OWNER
                User::where('id', $user->user_id)->update(['chain_id' => $chain->id]);

                // Actualizar chain_id en todos los hoteles de este usuario
                $allHotels = DB::table('hotels')
                    ->join('hotel_user', 'hotels.id', '=', 'hotel_user.hotel_id')
                    ->where('hotel_user.user_id', $user->user_id)
                    //->where('hotels.del', 0)
                    ->select('hotels.subdomain', 'hotels.id')
                    ->get();

                foreach ($allHotels as $hotel) {
                    Hotel::where('id', $hotel->id)->update(['chain_id' => $chain->id]);
                }
            }
        }

        // Asignar chain_id a los usuarios con parent_id
        $childUsers = User::whereNotNull('parent_id')->get();

        foreach ($childUsers as $childUser) {
            $ownerChainId = User::where('id', $childUser->parent_id)->value('chain_id');

            if ($ownerChainId) {
                // Actualizar el chain_id del usuario hijo
                User::where('id', $childUser->id)->update(['chain_id' => $ownerChainId]);
            }
        }
    }
}
