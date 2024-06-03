<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RequestSettingService;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;

class RequestSettingController extends Controller
{
    public $service;

    function __construct(
        RequestSettingService $service
    )
    {
        $this->service = $service;
    }


    public function getAll(Request $request){
        
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAll');
        }
    }

    public function getPostStayRequestData(Request $request){
        
        try {
            $hotel = $request->attributes->get('hotel');
            $settings = $this->service->getAll($hotel->id);
            $model = $this->service->getPostStayRequestData($settings, $hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getPostStayRequestData');
        }
    }


}
