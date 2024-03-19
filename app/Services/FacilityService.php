<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\User;

use App\Http\Resources\FacilityResource;

class FacilityService {

    function __construct()
    {

    }

    public function getCrosselling ($modelHotel) {
        try {

            $facilities = FacilityHoster::with('images', 'translate')
                            ->where('hotel_id',$modelHotel->id)
                            ->where('visible',1)
                            ->where('select',1)->limit(12)
                            ->get();

            return $facilities;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getAll ($modelHotel) {
        try {
            $facilities = FacilityHoster::with('images')
                ->where('hotel_id',$modelHotel->id)
                ->where(['status' => 1, 'select' => 1])->where('visible',1)
                ->get();
                
            return $facilities;
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function findById ($id, $modelHotel) {
        try {
            $facility = FacilityHoster::with('images')
                ->where('id',$id)
                ->where('hotel_id',$modelHotel->id)
                ->where(['status' => 1, 'select' => 1])->where('visible',1)
                ->first();
                
            return $facility;
        } catch (\Exception $e) {
            return $e;
        }
    }
    
}