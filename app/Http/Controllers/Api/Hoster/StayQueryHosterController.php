<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Queries\QueryHosterServices;
use App\Services\Hoster\Stay\StayHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class StayQueryHosterController extends Controller
{
    public $service;
    public $stayHosterService;

    function __construct(
        QueryHosterServices $_QueryHosterController,
        StayHosterServices $_StayHosterServices
    )
    {
        $this->service = $_QueryHosterController;
        $this->stayHosterService = $_StayHosterServices;
    }

    public function getFeedbackSummaryByGuest(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $stayId = $request->stayId;
            $stayCheckin = $request->stayCheckin;
            $stayCheckout = $request->stayCheckout;
            $guestId = $request->guestId;

            $model = $this->service->getFeedbackSummaryByGuest($stayId, $guestId, $hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getFeedbackSummaryByGuest');
        }
    }


    public function getDetailQueryByGuest(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $stayId = $request->stayId;
            $guestId = $request->guestId;

            $model = $this->stayHosterService->getDetailQueryByGuest($stayId, $guestId, $hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getDetailQueryByGuest');
        }
    }
    
    
}
