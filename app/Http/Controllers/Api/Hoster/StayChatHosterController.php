<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Stay\StayHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class StayChatHosterController extends Controller
{
    public $service;
    public $stayService;

    function __construct(
        StayHosterServices $_StayHosterServices
    )
    {
        $this->stayService = $_StayHosterServices;
    }

    public function togglePendingState(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $queryId = $request->queryId;
            $bool = $request->bool;

            $model = $this->service->togglePendingState($queryId, $bool, $hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.togglePendingState');
        }
    }
    
}
