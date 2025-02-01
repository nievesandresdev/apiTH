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

        $dossier = Dossier::find($dossierData->dossier_id);
        return response()->json($dossier->load('dossierData'));
    }

    //store dossierdata new data
    public function storeDossierData(Request $request)
    {
        $dossierData = new DossierData();
        $dossierData->dossier_id = $request->dossier_id;
        //$dossierData->tab_number = $request->tab_number;
        $dossierData->rooms = $request->rooms;
        $dossierData->averagePrice = $request->averagePrice;
        $dossierData->occupancyRate = $request->occupancyRate;
        $dossierData->reputationIncrease = $request->reputationIncrease;
        $dossierData->pricePerNightIncrease = $request->pricePerNightIncrease;
        $dossierData->occupancyRateIncrease = $request->occupancyRateIncrease;
        $dossierData->pricePerRoomPerMonth = $request->pricePerRoomPerMonth;
        $dossierData->implementationPrice = $request->implementationPrice;
        $dossierData->investmentInHoster = $request->investmentInHoster;
        $dossierData->benefit = $request->benefit;
        $dossierData->save();

        $dossier = Dossier::find($dossierData->dossier_id);
        return response()->json($dossier->load('dossierData'));
    }
}
