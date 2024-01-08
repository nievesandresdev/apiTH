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

    public function getCrosselling ($hotel) {
        try {
            $facilities = FacilityHoster::with('images', 'translate')->where('hotel_id',$hotel->id)->where('select',1)->limit(12)->get();

            $facilities = $facilities->map(function ($item) {
                return [
                    'id' => $item->id,
                    'images' => $item->images,
                    'title' => $item?->title,
                ];
            })->values();

            return $facilities;

        } catch (\Exception $e) {
            return $e;
        }
    }
}