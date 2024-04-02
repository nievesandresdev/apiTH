<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\StayAccess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class StayAccessService {

    public function save($stayId,$guestId)
    {
        try {
            if($stayId && $guestId){
                $device = 'PC';
                $agent = new Agent();
                $agent->isMobile() ? $device = 'Movil' : '';
                $agent->isTablet() ? $device = 'Tablet' : '';
                $access = StayAccess::firstOrCreate(
                    ['stay_id' => $stayId, 'device' => $device, 'guest_id' => $guestId]
                );
                return $access;
            }
        } catch (\Exception $e) {
            return $e;
        }
    }

}