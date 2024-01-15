<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\City;

use App\Http\Resources\FacilityResource;

class CityService {

    function __construct()
    {

    }

    public function getAll ($request) {
        try {

            $modelHotel = $request->attributes->get('hotel');
            $search = $request->search ?? null;

            $collectionCity = City::search($search)->limit(8)->get();

            return $collectionCity;

        } catch (\Exception $e) {
            return $e;
        }
    }
}