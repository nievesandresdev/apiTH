<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function getDossierData($tabNumber, $type, $domain)
    {
        //$dossierData = DossierData::where('tab_number', $tabNumber);

        $typeQuery = $type == '-' ? 'A' : $type;


        if($type != null){
            $dossierData = DossierData::whereHas('dossier', function($query) use ($typeQuery, $domain){
                $query->where('domain', $domain)->where('type', $typeQuery);
            })->where('numeral', $tabNumber);
        }else{
            $dossierData = DossierData::where('numeral', $tabNumber)->whereHas('dossier', function($query) use ($domain){
                $query->where('domain', $domain);
            });
        }

        $dossierData = $dossierData->first();


        return response()->json($dossierData);
    }

    //store update or create
    public function storeUpdateOrCreate(Request $request)
    {
        //buscar dossier domain
        //$dossier = Dossier::where('id', $request->dossier_id)->first();
        //buscar ambosos tipos a y b
        $type = 'A';
        if($request->rooms >= 1 && $request->rooms <= 100){
            $type = 'B';
        }else if($request->rooms >= 101){
            $type = 'A';
        }



        $dossierTypes = Dossier::where('domain', $request->domain)->where('type', $type)->get();
        $dossierDataResponse = DossierData::whereHas('dossier', function($query) use ($request, $type){
            $query->where('domain', $request->domain)->where('type', $type);
        })->first();



        //return response()->json(['dossierTypes' => $dossierTypes]);

        $requestType = $type; // Siempre usar el tipo calculado basado en rooms

        // Debug log
        /* Log::info('DossierController Debug', [
            'rooms' => $request->rooms,
            'calculated_type' => $type,
            'request_type' => $request->type,
            'final_requestType' => $requestType,
            'tab_number' => $request->tab_number,
            'domain' => $request->domain
        ]); */

        // Crear o actualizar el registro
        $requestData = $request->except(['dossier_id']);
        $requestData['type'] = $requestType; // Sobrescribir el type con el valor calculado

        // Buscar el registro DossierData existente con el numeral y tipo específicos para este dominio
        $existingRecord = DossierData::whereHas('dossier', function($query) use ($request) {
            $query->where('domain', $request->domain);
        })->where('numeral', $request->tab_number)
          ->where('type', $requestType)
          ->first();

                if ($existingRecord) {
            // Si existe, verificar si cambió el tipo
            $updateData = $requestData;

            // Si cambió el tipo (B→A o A→B), preservar el pricePerRoomPerMonth del registro existente
            if ($existingRecord->type !== $requestType) {
                unset($updateData['pricePerRoomPerMonth']); // Preservar el pricePerRoomPerMonth existente
            }
            // Si el tipo es el mismo (B→B o A→A), sí actualizar el pricePerRoomPerMonth

            $dossierData = DossierData::updateOrCreate(
                [
                    'numeral' => $request->tab_number,
                    'type' => $requestType,
                    'dossier_id' => $existingRecord->dossier_id
                ],
                $updateData
            );
        } else {
            // Si no existe, buscar el dossier correcto para crear uno nuevo
            $targetDossier = Dossier::where('domain', $request->domain)->where('type', $requestType)->first();
            $dossierData = DossierData::updateOrCreate(
                [
                    'numeral' => $request->tab_number,
                    'type' => $requestType,
                    'dossier_id' => $targetDossier->id
                ],
                $requestData
            );
        }


        /* foreach($dossierTypes as $d){
             // Actualizar todos los registros que tengan el mismo dossier_id
            DossierData::where('dossier_id', $d->id)
            ->update([
                    //'pricePerRoomPerMonth' => $request->pricePerRoomPerMonth,
                    //'implementationPrice' => $request->implementationPrice,
                    //'rooms' => $request->rooms,
                ]);
        } */


        //$dossier = Dossier::find($dossierData->dossier_id);
        return response()->json($dossierDataResponse->dossier->load('dossierData'));
    }

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
        $dossierTypeResponse = Dossier::where('domain', $request->domain)->where('type', $type)->first();
        $dossierTypes = Dossier::where('domain', $request->domain)->get();

        //return response()->json(['dossierTypes' => $dossierTypes]);

        foreach($dossierTypes as $d){
            $lastData = DossierData::where('dossier_id', $d->id)
                ->latest()
                ->first();

            //return response()->json(['lastData' => $lastData,'dossierType' => $dossierType]);

            $newDossierData = $lastData->replicate();
            $newDossierData->tab_number = null;
            $newDossierData->numeral = $lastData->numeral + 1;

            // IMPORTANTE: Establecer el dossier_id correcto para el tipo correspondiente
            $newDossierData->dossier_id = $d->id;

            $newDossierData->save();
        }

        // Debug: Ver los IDs antes y después
       /*  $debug = [
            'dossierType_id' => $dossierType->id,
            'newDossierData_after_save' => $newDossierData->dossier_id,
            'newDossierData_fresh' => $newDossierData->fresh()->dossier_id,
        ]; */

        // Retornar el dossier correcto con todos sus datos incluyendo el nuevo registro
        return response()->json($dossierTypeResponse->load('dossierData'));
    }

    //store dossierdata new data
    /* public function storeDossierData(Request $request)
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
    } */

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
