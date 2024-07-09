<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Stay\StayHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class StayHosterController extends Controller
{
    public $service;

    function __construct(
        StayHosterServices $_StayHosterServices
    )
    {
        $this->service = $_StayHosterServices;
    }

    public function getAllByHotel(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAllByHotel($hotel, $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllByHotel');
        }
    }


}
