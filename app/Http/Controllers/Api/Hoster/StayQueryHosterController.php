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
    
    public function togglePendingState(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $queryId = $request->queryId;
            $bool = $request->bool;

            $model = $this->service->togglePendingState($queryId, $bool, $hotel->id, $request);
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

    public function countPendingByHotel(Request $request){

        try {
            $hotel = $request->attributes->get('hotel');

            $model = $this->service->countPendingByHotel($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.countPendingByHotel');
        }
        
    }

    public function pendingCountByStay($stayId){

        try {
            $model = $this->service->pendingCountByStay($stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.pendingCountByStay');
        }
        
    }
}
