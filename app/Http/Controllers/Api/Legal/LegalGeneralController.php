<?php

namespace App\Http\Controllers\Api\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use App\Services\LegalServices;

class LegalGeneralController extends Controller
{
    protected $legalServices;

    public function __construct(
        LegalServices $legalServices
    )
    {
        $this->legalServices = $legalServices;
    }

    public function getGeneralLegal(){

        $hotel = request()->attributes->get('hotel');

        $data = $this->legalServices->getGeneralLegal($hotel);

        return bodyResponseRequest(EnumResponse::SUCCESS, [
            'legal' => $data,
            'hotel' => $hotel
        ]);

    }

    public function storeGeneralLegal()
    {
        $hotel = request()->attributes->get('hotel');

        try {
            $data = $this->legalServices->storeOrUpdateLegalGeneral($hotel, request()->all());

            return bodyResponseRequest(EnumResponse::SUCCESS, [
                'legal' => $data,
            ]);
        } catch (\Exception $e) {

            return bodyResponseRequest(EnumResponse::INTERNAL_SERVER_ERROR, $e->getMessage(), 'Se encontró un error durante la operación', get_class($e));
        }
    }

}
