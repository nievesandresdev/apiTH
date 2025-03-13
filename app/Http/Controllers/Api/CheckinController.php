<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CheckinSettingsResource;
use App\Services\CheckinServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class CheckinController extends Controller
{
    public $service;

    function __construct(
        CheckinServices $_CheckinServices
    )
    {
        $this->service = $_CheckinServices;
    }

    public function getAllSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAllSettings($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new CheckinSettingsResource($model,['succes_message']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGeneralSettings');
        }
    }

    
}
