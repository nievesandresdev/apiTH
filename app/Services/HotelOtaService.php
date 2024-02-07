<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Hotel;
use App\Models\User;

use App\Http\Resources\HotelResource;

class HotelOtaService {

    function __construct()
    {

    }

    public function getAll ($hotelModel) {
        try {

            $otas = $hotelModel->otas;

            return $otas;

        } catch (\Exception $e) {
            return $e;
        }
    }
}