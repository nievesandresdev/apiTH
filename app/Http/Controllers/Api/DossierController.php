<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{Dossier, DossierData};

class DossierController extends Controller
{
    public function getDossier($domain, $type)
    {
        $dossier = Dossier::where('domain', 'like', '%' . $domain . '%')->where('type', $type)->first();
        return response()->json($dossier->load('dossierData'));
    }

    public function getDossierData($tabNumber)
    {
        $dossierData = DossierData::where('tab_number', $tabNumber)->first();
        return response()->json($dossierData);
    }

    //store update or create
    public function storeUpdateOrCreate(Request $request)
    {
        $dossierData = DossierData::updateOrCreate(['tab_number' => $request->tab_number], $request->all());
        return response()->json($dossierData);
    }
}
