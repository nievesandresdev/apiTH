<?php

namespace App\Services;

use App\Models\Customization;
use App\Models\Chain;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\ChainSubdomain;
use App\Models\Hotel;
use Illuminate\Support\Facades\Log;

class ChainService
{
    public function verifySubdomainExist ($subdomain, $hotelModel, $chainModel) {

        if (!$chainModel || $chainModel->subdomain == $subdomain) {
            return  false;
        }

        $exist = ChainSubdomain::where(['name' => $subdomain])->whereNot('chain_id', $chainModel->id)->exists();
        return $exist;

    }

    public function verifySubdomainExistInHistory ($subdomain, $hotelModel, $chainModel) {
        if ($chainModel->subdomain == $subdomain) {
            return  true;
        }
        $exist = ChainSubdomain::where(['name' => $subdomain])->exists();
        return $exist;
    }

    public function updateSubdomain ($subdomain, $chainModel) {
        if ($subdomain == $chainModel->subdomain) {
            return;
        }

        ChainSubdomain::where([
            'chain_id' => $chainModel->id,
            'active' => true
        ])->update(['active'=> false]);

        $chainSubdomain = ChainSubdomain::firstOrCreate([
            'name' => $subdomain,
        ],[
            'name' => $subdomain,
            'chain_id' => $chainModel->id,
            'active' => true
        ]);
        $chainSubdomain->active = true;
        $chainSubdomain->save();

        $chainModel->subdomain = $subdomain;

        $chainModel->save();
    }

    public function updateConfigGeneral ($request, $hotelModel) {
        [
            'language_default_webapp' => $languageDefaultWebapp,
        ] = $request->all();
        $hotelModel->language_default_webapp = $languageDefaultWebapp;

        $hotelModel->save();
    }

    public function findBySubdomain ($subdomain) {
        try {
            return Chain::where('subdomain',$subdomain)->first();
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getHotelsList ($subdomain) {
        try {
            $chain = $this->findBySubdomain($subdomain);
            if($chain){
                return Hotel::where('chain_id',$chain->id)->where('del', 0)->get();
            }
            return [];
        } catch (\Exception $e) {
            return $e;
        }
    }
   
    public function getStaysGuest($chainId, $guestId, $currentStayId)
    {
        $hotels = Hotel::where('chain_id', $chainId)->active()->pluck('id');

        // Obtenemos todas las estancias de los hoteles en la cadena
        $stays = DB::table('guest_stay as gs')
        ->select(
            'gs.stay_id', 
            'gs.guest_id', 
            'gs.chain_id', 
            'stays.hotel_id', 
            'stays.room', 
            'stays.number_guests', 
            'stays.id as stayId', 
            'stays.check_in', 
            'stays.check_out', 
            'hotels.name as hotel_name', 
            'hotels.zone as hotel_zone'
        )
        ->join('stays', 'stays.id', '=', 'gs.stay_id')
        ->join('hotels', 'hotels.id', '=', 'stays.hotel_id')
        ->where('gs.chain_id', $chainId)
        ->where('gs.guest_id', $guestId)
        ->get();


        // Fecha actual para verificar estancia activa
        $currentDate = Carbon::now();

        // Filtramos y ordenamos las estancias
        $stays = $stays->map(function ($stay) use ($currentStayId) {
            $stay->isActive = $stay->stayId == $currentStayId;
            return $stay;
        });

        // Separa la estancia activa
        $activeStay = $stays->firstWhere('isActive', true);
        $otherStays = $stays->where('isActive', false)->sortByDesc('check_in');

        // Construye el listado en el formato deseado
        $result = [
            'active_stay' => $activeStay,
            'other_stays' => $otherStays->values()->all()
        ];

        return $result;
    }
}

