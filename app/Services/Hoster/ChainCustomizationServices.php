<?php

namespace App\Services\Hoster;

use App\Models\Customization;
use App\Models\Chain;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ChainCustomizationServices
{
    public function createOrUpdate ($request, $hotelModel, $chainModel) {

        $colors = $request->colors ? json_encode($request->colors) : null;
        $dataUpdate = [
            'colors' => $colors,
            'logo' => $request->logo,
            'name' => $request->name,
            'type_header' => $request->type_header,
            'tonality_header' => $request->tonality_header,
            'chain_id' => $chainModel->id
        ];
        $customizationModel = Customization::updateOrCreate([
            'chain_id' => $chainModel->id
        ], $dataUpdate);

        return $customizationModel;

    }
   
}
