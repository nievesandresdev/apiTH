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

    public function getDossierData($tabNumber, $type)
    {
        $dossierData = DossierData::where('tab_number', $tabNumber)->first();
        return response()->json($dossierData);
    }

    //store update or create
    public function storeUpdateOrCreate(Request $request)
    {
        if($request->rooms >=1 && $request->rooms <= 100){
            $type = 'B';
        }else if($request->rooms >=101 ){
            $type = 'A';
        }else{
            $type = 'A';
        }

        //buscar dossier domain
        $dossier = Dossier::where('domain', $request->domain)->where('type', $type)->first();
        //buscar ambosos tipos a y b
        $dossierTypes = Dossier::where('domain', $request->domain)->get();

        // Crear o actualizar el registro
        /* $dossierData = DossierData::updateOrCreate(
            ['tab_number' => $request->tab_number],
            $request->all()
        ); */
        $dossierData = DossierData::updateOrCreate(
            ['tab_number' => $request->tab_number],
            $request->all()
        );
        //foreach($dossierTypes as $d){
             // Actualizar todos los registros que tengan el mismo dossier_id
            DossierData::where('dossier_id', $dossier->id)
            ->update([
                    //'pricePerRoomPerMonth' => $request->pricePerRoomPerMonth,
                    'implementationPrice' => $request->implementationPrice,
                    'rooms' => $request->rooms,
                    //'dossier_id' => $dossier->id,
                ]);
       //}


        //$dossier = Dossier::find($dossierData->dossier_id);
        return response()->json($dossier->load('dossierData'));
    }

    //store dossierdata new data
    public function storeDossierData(Request $request)
    {
        //$dossier_id = $request->dossier_id;
        $type = 'A';
        if($request->rooms >=1 && $request->rooms <= 100){
            $type = 'B';
        }else if($request->rooms >=101 ){
            $type = 'A';
        }

        //return response()->json(['type' => $type,'request' => $request->all()]);

        //$domainDossier = Dossier::where('id', $dossier_id)->first();
        $dossierType = Dossier::where('domain', $request->domain)->where('type', $type)->first();

        //return response()->json(['dossierType' => $dossierType]);

        $lastData = DossierData::where('dossier_id', $dossierType->id)
            ->latest()
            ->first();

        //return response()->json(['lastData' => $lastData,'dossierType' => $dossierType]);

        $newDossierData = $lastData->replicate();
        $newDossierData->tab_number = null;

        // IMPORTANTE: Establecer el dossier_id correcto para el tipo correspondiente
        $newDossierData->dossier_id = $dossierType->id;

        $newDossierData->save();

        // Debug: Ver los IDs antes y después
       /*  $debug = [
            'dossierType_id' => $dossierType->id,
            'newDossierData_after_save' => $newDossierData->dossier_id,
            'newDossierData_fresh' => $newDossierData->fresh()->dossier_id,
        ]; */

        // Retornar el dossier correcto con todos sus datos incluyendo el nuevo registro
        return response()->json($dossierType->load('dossierData'));
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
