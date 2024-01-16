<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\TypePlaces;

use App\Services\StaySurveyService;

use App\Http\Resources\StaySurveyResource;

use App\Utils\Enums\EnumResponse;

class StaySurveyController extends Controller
{
    function __construct(
        StaySurveyService $_StaySurveyService
    )
    {
        $this->service = $_StaySurveyService;
    }

    public function findByParams (Request $request) {
        try {

            $model = $this->service->findByParams($request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }

            $data = new StaySurveyResource($model);

            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findByParams');
        }
    }

    public function store (Request $request) {
        try {

            $modelHotel = $request->attributes->get('hotel');
            $responseService = $this->service->store($request, $modelHotel);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $responseService);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.store');
        }
    }

}