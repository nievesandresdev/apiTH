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
    public function verifySubdomainExist ($request, $hotelModel, $chainModel) {

        $subdomain = $request->subdomain;

        if (!$chainModel || $chainModel->subdomain == $subdomain) {
            return  false;
        }

        $exist = ChainSubdomain::where(['name' => $subdomain])->exists();
        return $exist;

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
   
}

