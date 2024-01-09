<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Places;
use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\User;

use App\Http\Resources\FacilityResource;

class PlaceService {

    function __construct()
    {

    }

    public function getCrosselling ($typePlaceName, $modelHotel) {
        try {
            $lengthPlaceFeatured = 12;
            $hotelId = $modelHotel->id;
            $cityName = $modelHotel->zone;

            $modelExperiencesFeatured = Places::activeToShow()
                                    ->whereCity($cityName)
                                    ->whereTypePlaceByName($typePlaceName)
                                    ->whereVisibleByHoster($hotelId)
                                    ->orderByFeatured($hotelId)
                                    ->limit($lengthPlaceFeatured)
                                    ->get();
                            
            return $modelExperiencesFeatured;

        } catch (\Exception $e) {
            return $e;
        }
    }

}
