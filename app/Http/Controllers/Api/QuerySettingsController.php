<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuerySettingsServices;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;

class QuerySettingsController extends Controller
{
    public $service;

    function __construct(
        QuerySettingsServices $service
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

}
