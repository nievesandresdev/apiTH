<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{Dossier, DossierData};

class DossierController extends Controller
{
    public function getDossier($domain, $type)
    {
        $dossier = Dossier::where('domain',$domain)->where('type', $type)->where('status', 1)->first();

        if (!$dossier) {
            return response()->json(null, 204);
        }

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
        //buscar dossier domain
        //$dossier = Dossier::where('id', $request->dossier_id)->first();
        //buscar ambosos tipos a y b
        $dossierTypes = Dossier::where('domain', $request->domain)->get();

        // Crear o actualizar el registro
        $dossierData = DossierData::updateOrCreate(
            ['tab_number' => $request->tab_number],
            $request->all()
        );
        foreach($dossierTypes as $d){
             // Actualizar todos los registros que tengan el mismo dossier_id
            DossierData::where('dossier_id', $d->id)
            ->update([
                    //'pricePerRoomPerMonth' => $request->pricePerRoomPerMonth,
                    'implementationPrice' => $request->implementationPrice,
                    'rooms' => $request->rooms,
                ]);
        }


        //$dossier = Dossier::find($dossierData->dossier_id);
        return response()->json($dossierData->dossier->load('dossierData'));
    }

    //store dossierdata new data
    public function storeDossierData(Request $request)
    {
        $dossier_id = $request->dossier_id;

        $lastData = DossierData::where('dossier_id', $dossier_id)
            ->latest()
            ->first();

        $newDossierData = $lastData->replicate();
        $newDossierData->tab_number = null;

        $newDossierData->save();

        $dossier = Dossier::find($dossier_id);
        return response()->json($dossier->load('dossierData'));
    }

    public function deleteDossierData($id)
    {
        $dossierData = DossierData::where('id', $id)->first();
        $dossier = Dossier::find($dossierData->dossier_id);
        if($dossierData->delete()){
            return response()->json([
                'error' => false,
                'dossier' => $dossier->load('dossierData')
            ]);
        }
        return response()->json(['error' => true]);
    }

}
