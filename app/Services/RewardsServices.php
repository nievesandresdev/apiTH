<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

use App\Models\Facility;
use App\Models\FacilityHoster;
use App\Models\FacilityHosterLanguage;
use App\Models\User;

use App\Http\Resources\FacilityResource;

use App\Jobs\TranslateModelJob;

class RewardsServices {

    function getRewards($request, $modelHotel){
        return 'servicio rewards';
    }

}
