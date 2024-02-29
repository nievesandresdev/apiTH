<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QueryServices;
use App\Services\QuerySettingsServices;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;

class QueryController extends Controller
{
    public $service;
    public $settingsService;

    function __construct(
        QuerySettingsServices $settingsService,
        QueryServices $service,
    )
    {
        $this->service = $service;
        $this->settingsService = $settingsService;
    }


    public function getCurrentPeriod(Request $request){
        
        try {
            $hotel = $request->attributes->get('hotel');
            $stayId = $request->stayId;
            $model = $this->service->getCurrentPeriod($hotel,$stayId);
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

    public function firstOrCreate(Request $request){
        
        try {
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $period = $request->period;
            $model = $this->service->firstOrCreate($stayId, $guestId, $period);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.firstOrCreate');
        }
    }

    public function getRecentlySortedResponses(Request $request){
        try {
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $period = $request->period;
            $collection = $this->service->getResponses($stayId, $guestId, $period);
            
            if(!$collection){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }else{
                $collection = $collection->sortByDesc('created_at');
            }
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, $collection);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.firstOrCreate');
        }
    }

    public function saveResponse(Request $request){
        try {
            
            $queryId = $request->queryId;
            $save = $this->service->saveResponse($queryId,$request);
            
            if(!$save){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $save);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.saveResponse');
        }
    }

    

}
