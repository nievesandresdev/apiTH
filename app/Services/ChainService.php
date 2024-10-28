<?php

namespace App\Services;

use App\Models\Customization;
use App\Models\Chain;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\ChainSubdomain;

class ChainService
{
    public function verifySubdomainExist ($subdomain, $hotelModel, $chainModel) {

        if (!$chainModel || $chainModel->subdomain == $subdomain) {
            return  false;
        }

        $exist = ChainSubdomain::where(['name' => $subdomain])->whereNot('chain_id', $chainModel->id)->exists();
        return $exist;

    }

    public function verifySubdomainExistInHistory ($subdomain, $hotelModel, $chainModel) {
        if ($chainModel->subdomain == $subdomain) {
            return  true;
        }
        $exist = ChainSubdomain::where(['name' => $subdomain])->exists();
        return $exist;
    }

    public function updateSubdomain ($subdomain, $chainModel) {
        if ($subdomain == $chainModel->subdomain) {
            return;
        }

        ChainSubdomain::where([
            'chain_id' => $chainModel->id,
            'active' => true
        ])->update(['active'=> false]);

        $chainSubdomain = ChainSubdomain::firstOrCreate([
            'name' => $subdomain,
        ],[
            'name' => $subdomain,
            'chain_id' => $chainModel->id,
            'active' => true
        ]);
        $chainSubdomain->active = true;
        $chainSubdomain->save();

        $chainModel->subdomain = $subdomain;

        $chainModel->save();
    }

    public function updateConfigGeneral ($request, $hotelModel) {
        [
            'language_default_webapp' => $languageDefaultWebapp,
        ] = $request->all();
        $hotelModel->language_default_webapp = $languageDefaultWebapp;

        $hotelModel->save();
    }
   
}

