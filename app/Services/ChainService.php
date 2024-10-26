<?php

namespace App\Services;

use App\Models\Customization;
use App\Models\Chain;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\ChainSubdomain;

class ChainService
{
    public function verifySubdomainExist ($request, $hotelModel, $chainModel) {

        $subdomain = $request->subdomain;

        if (!$chainModel || $chainModel->subdomain == $subdomain) {
            return  false;
        }

        $exist = ChainSubdomain::where(['name' => $subdomain])->exists();
        return $exist;

    }
   
}

