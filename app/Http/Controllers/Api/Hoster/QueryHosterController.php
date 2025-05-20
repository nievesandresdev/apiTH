<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Queries\QueryHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;

class QueryHosterController extends Controller
{
    public $service;

    function __construct(
        QueryHosterServices $_QueryHosterController
    )
    {
        $this->service = $_QueryHosterController;
    }

    public function getGeneralReport(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');

            $model = $this->service->getGeneralReport($hotel, $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGeneralReport');
        }
    }
}
