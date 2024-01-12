<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\User;
use App\Models\Stay;

use App\Http\Resources\FacilityResource;

class UtilityService {

    function __construct()
    {

    }

    public function getExpAndPlace ($request, $modelHotel) {
        try {
            $routeName = $request->routeName;
            $search = $request->search ?? null;
            $user = $modelHotel->user()->first();
            $city = $modelHotel->zone;

            $data = collect([]);
            $total_length = 8;
            
            if($routeName == 'places.list' || $route_name == 'places.show'){
                $data = $this->service->getPlacesBySearch ($search, $placelang, $total_length, $city, $hotel, $user);
            }else{
                $data = $this->getExperiencesBySearch($search, $actlang, $total_length, $city, $hotel, $user);
            };

        } catch (\Exception $e) {
            $e;
        }

    }
}