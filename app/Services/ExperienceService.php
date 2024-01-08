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

class ExperienceService {

    function __construct()
    {

    }

    public function getCrosselling ($hotel) {
        try {
            $lengthAExpFeatured = 12;
            $user = $hotel->user[0];
            $experiencesFeatured = Products::activeToShow()
                                    ->whereCity($city, $model_activity_language)
                                    ->whereVisibleByHoster($user, $hotel)
                                    ->orderByFeatured($hotel)
                                    ->limit($lengthAExpFeatured)
                                    ->get();
                            
            return $experiencesFeatured;

        } catch (\Exception $e) {
            return $e;
        }
    }

    public function scopeWhereVisibleByHoster($query, $user, $hotel = null){
        $query->where(function($query)use($user, $hotel){
            $query->whereHas('toggleableHotels', function($query)use($user, $hotel){
                // $query->where('select', 0)
                $query->where(function($query)use($user, $hotel){
                    $query->where('hotel_id', $hotel->id);
                    // if($user){
                    //     $query->orWhere('user_id', $user->id);
                    // }
                });
            });
            // $query->orWhere('select', 1);
        })
        ->whereDoesntHave('productHidden', function($query)use($user, $hotel){
            $query->where('hotel_id', $hotel->id);
            // if($user){
            //     $query->orWhere('user_id', $user->id);
            // }
        });
    }

}
