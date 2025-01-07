<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Chain;
use App\Models\ChainSubdomain;
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
            ->whereNull('hotels.chain_id') // Solo hoteles sin chain_id asignado
            ->groupBy('users.id')
            ->having('total_hotels', '=', 1)
            ->get();

        foreach ($independentUsers as $user) {
            if (empty($user->subdomain)) {
                continue;
            }

            // Crear una nueva cadena
            $chain = Chain::create([
                'subdomain' => $user->subdomain,
                'type' => 'INDEPENDENT',
                'created_at' => Carbon::now(),
            ]);

            // Registrar también en ChainSubdomain con active = 1
            ChainSubdomain::create([
                'chain_id' => $chain->id,
                'name' => $user->subdomain,
                'active' => 1,
            ]);

            // Actualizar el chain_id en el usuario y hotel
            User::where('id', $user->user_id)->update(['chain_id' => $chain->id]);
            Hotel::where('subdomain', $user->subdomain)->whereNull('chain_id')->update(['chain_id' => $chain->id]);
        }

        // Usuarios de cadena
        $chainUsers = DB::table('users')
            ->join('hotel_user', 'users.id', '=', 'hotel_user.user_id')
            ->join('hotels', 'hotel_user.hotel_id', '=', 'hotels.id')
            ->select('users.id as user_id', 'users.name as user_name', DB::raw('GROUP_CONCAT(hotels.subdomain) as subdomains'), DB::raw('COUNT(hotel_user.hotel_id) as total_hotels'))
            ->whereNull('hotels.chain_id') // Solo hoteles sin chain_id asignado
            ->groupBy('users.id')
            ->having('total_hotels', '>', 1)
            ->get();

        foreach ($chainUsers as $user) {
            $firstValidHotel = DB::table('hotels')
                ->join('hotel_user', 'hotels.id', '=', 'hotel_user.hotel_id')
                ->where('hotel_user.user_id', $user->user_id)
                ->whereNull('hotels.chain_id') // Solo hoteles sin chain_id asignado
                ->orderBy('hotels.id')
                ->select('hotels.subdomain', 'hotels.id')
                ->first();

            if (!$firstValidHotel || empty($firstValidHotel->subdomain)) {
                continue;
            }

            // Crear una nueva cadena
            $chain = Chain::create([
                'subdomain' => $firstValidHotel->subdomain,
                'type' => 'CHAIN',
                'created_at' => Carbon::now(),
            ]);

            // Registrar también en ChainSubdomain con active = 1
            ChainSubdomain::create([
                'chain_id' => $chain->id,
                'name' => $firstValidHotel->subdomain,
                'active' => 1,
            ]);

            // Actualizar el chain_id del usuario y de todos los hoteles del usuario
            User::where('id', $user->user_id)->update(['chain_id' => $chain->id]);

            $allHotels = DB::table('hotels')
                ->join('hotel_user', 'hotels.id', '=', 'hotel_user.hotel_id')
                ->where('hotel_user.user_id', $user->user_id)
                ->whereNull('hotels.chain_id') // Solo hoteles sin chain_id asignado
                ->select('hotels.subdomain', 'hotels.id')
                ->get();

            foreach ($allHotels as $hotel) {
                Hotel::where('id', $hotel->id)->update(['chain_id' => $chain->id]);
            }
        }

        // Asignar chain_id a los usuarios con parent_id
        $childUsers = User::whereNotNull('parent_id')->get();

        foreach ($childUsers as $childUser) {
            $ownerChainId = User::where('id', $childUser->parent_id)->value('chain_id');

            if (!$ownerChainId) {
                continue;
            }

            User::where('id', $childUser->id)->update(['chain_id' => $ownerChainId]);
        }
    }
}
