<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Queries\QueryHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class StayQueryHosterController extends Controller
{
    public $service;

    function __construct(
        QueryHosterServices $_QueryHosterController
    )
    {
        $this->service = $_QueryHosterController;
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

            $model = $this->service->getDetailByGuest($guestId, $stayId, $hotel);
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
