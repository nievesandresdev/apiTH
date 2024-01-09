<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\HotelOtaService;

use App\Http\Resources\HotelOtaResource;

use App\Utils\Enums\EnumResponse;

class HotelOtaController extends Controller
{
    function __construct(
        HotelOtaService $_HotelOtaService
    )
    {
        $this->service = $_HotelOtaService;
    }

    public function getAll (Request $request) {
        try {

            $modelHotel = $request->attributes->get('hotel');
            $otas = $this->service->getAll($modelHotel);
            $data = HotelOtaResource::collection($otas);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

}
