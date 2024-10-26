<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelCardResource;
use App\Services\ChainServices;
use App\Utils\Enums\EnumResponse;
use Illuminate\Http\Request;

class ChainController extends Controller
{
    public $service;

    function __construct(
        ChainServices $_ChainServices
    )
    {
        $this->service = $_ChainServices;
        // 
    }

    public function getHotelsList (Request $request) {
        try {
            $chainSubdomain = $request->attributes->get('chainSubdomain');
            $hotels = $this->service->getHotelsList($chainSubdomain);
            $hotels = HotelCardResource::collection($hotels);
            if(!$hotels){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $hotels);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

}
