<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Query;
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
            $hotel = $request->attributes->get('hotel');
            $settings = $this->settingsService->getAll($hotel->id);
            $period = $request->period;
            
            if($period !== 'post-stay'){
                $periodKey = str_replace("-", "_", $period).'_activate';
                if(!$settings->$periodKey) return;
            }
            $stayId = $request->stayId;
            $guestId = $request->guestId;
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
            $collection = $this->service->getResponses($stayId, $guestId);
            
            if(!count($collection)){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }else{
                $collection = $collection->sortByDesc('created_at');
                $collection = $collection->values();
            }
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, $collection);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.firstOrCreate');
        }
    }

    public function saveResponse(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $queryId = $request->queryId;
            $save = $this->service->saveResponse( $queryId, $request, $hotel->id);
            
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

    public function existingPendingQuery(Request $request){

        try {
            $hotel = $request->attributes->get('hotel');
            $settings = $this->settingsService->getAll($hotel->id);

            
            $stayId = $request->stayId;
            $currenPeriod = $this->service->getCurrentPeriod($hotel,$stayId);
            if(!$currenPeriod) return bodyResponseRequest(EnumResponse::ACCEPTED, false);
            if($currenPeriod !== 'post-stay'){
                $periodKey = str_replace("-", "_", $currenPeriod).'_activate';
                if(!$settings->$periodKey)  return bodyResponseRequest(EnumResponse::ACCEPTED, false);
            }
            $request->merge(['period' => $currenPeriod]);
            $query = $this->service->findByParams($request);
            
            $response = false;
            if(!$query) $response = true;
            return bodyResponseRequest(EnumResponse::ACCEPTED, $response);
            
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingPendigQuery');
        }

        return $currenPeriod;
    }

}
