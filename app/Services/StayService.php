<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Stay;

class StayService {

    public function findAndValidAccess($stay_id,$hotel)
    {
        try {
            $stay = Stay::where('id',$stay_id)->where('hotel_id',$hotel->id)->first();
            $checkoutDate = $stay ? Carbon::parse($stay->check_out) : null;
            // Verifica si han pasado más de 10 días desde el checkout

            if ($checkoutDate && !$checkoutDate->isBefore(Carbon::now()->subDays(10))) {
                //si no han pasado retorna la estancia
                return $stay;
            }
            return null;

        } catch (\Exception $e) {
            return $e;
        }
    }
}