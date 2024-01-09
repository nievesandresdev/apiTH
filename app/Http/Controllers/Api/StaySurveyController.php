<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\StaySurveyService;

use App\Http\Resources\SurveyResource;

use App\Utils\Enums\EnumResponse;

class StaySurveyController extends Controller
{
    function __construct(
        StaySurveyService $_StaySurveyService
    )
    {
        $this->service = $_StaySurveyService;
    }

    public function store (Request $request) {
        try {

            $modelHotel = $request->attributes->get('hotel');
            $responseService = $this->service->store($request, $modelHotel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, true);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.store');
        }
    }

}