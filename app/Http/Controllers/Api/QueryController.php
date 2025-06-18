<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuerySettingsResource;
use App\Models\Query;
use App\Services\QueryServices;
use App\Services\QuerySettingsServices;
use App\Services\RequestSettingService;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use Carbon\Carbon;

class QueryController extends Controller
{
    public $service;
    public $settingsService;
    public $requestSettingService;
    function __construct(
        QuerySettingsServices $settingsService,
        QueryServices $service,
        RequestSettingService $requestSettingService
    )
    {
        $this->service = $service;
        $this->settingsService = $settingsService;
        $this->requestSettingService = $requestSettingService;
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
                $collection = $collection->sortByDesc('updated_at');
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
            $save = $this->service->saveResponse( $queryId, $request, $hotel);
            
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
            // $settings = $this->settingsService->getAll($hotel->id);

            
            $stayId = $request->stayId;
            $currenPeriod = $this->service->getCurrentPeriod($hotel,$stayId);
            if(!$currenPeriod) return bodyResponseRequest(EnumResponse::ACCEPTED, false);
            // if($currenPeriod !== 'post-stay'){
            //     $periodKey = str_replace("-", "_", $currenPeriod).'_activate';
            //     if(!$settings->$periodKey)  return bodyResponseRequest(EnumResponse::ACCEPTED, false);
            // }
            $request->merge(['period' => $currenPeriod]);

            $exist = $this->service->existingPendingQuery($request);
            
            $response = false;
            if($exist) $response = true;
            return bodyResponseRequest(EnumResponse::ACCEPTED, $response);
            
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingPendigQuery');
        }

        return $currenPeriod;
    }

    public function visited(Request $request){

        try {
            $hotel = $request->attributes->get('hotel');
            $stayId = $request->stayId;
            $guestId = $request->guestId;
            $period = $this->service->getCurrentPeriod($hotel,$stayId);
            if(!$period) return bodyResponseRequest(EnumResponse::ACCEPTED, false);

            $request->merge(['period' => $period]);
            $query = $this->service->findByParams($request);
            if(!$query) return bodyResponseRequest(EnumResponse::ACCEPTED, false);
            $queryId = $query->id;

            $query = $this->service->updateParams( $queryId, [ 'visited' => true ] );
            return bodyResponseRequest(EnumResponse::ACCEPTED, $query);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.existingPendigQuery');
        }
    }

    public function getCurrentAndSettingsQuery(Request $request){
        
        $request->validate([
            'stayId' => 'required|integer',
            'guestId' => 'required|integer',
            'period' => 'required|string',
            'guestName' => 'required|string'
        ]);

        try {

            $query = $this->service->getCurrentQuery($request);
            if(!$query) return bodyResponseRequest(EnumResponse::ACCEPTED, false);
            //get settings
            $hotel = $request->attributes->get('hotel');
            $settings = $this->settingsService->getAll($hotel->id);
            $requestData = null;
            // if($request->period == 'in-stay' || $request->period == 'post-stay' && $query->answered){
                $requestSettings = $this->requestSettingService->getAll($hotel->id);
                $guestName = $request->guestName;
                $requestData = $this->requestSettingService->getRequestData($requestSettings, $guestName, $request->period);
            // }
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, [
                'query' => $query,
                'settings' => QuerySettingsResource::make($settings),
                'requestData' => $requestData
            ]);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getCurrentQuery');
        }
    }

    public function getCurrentQuery(Request $request){
        
        $request->validate([
            'stayId' => 'required|integer',
            'guestId' => 'required|integer',
            'period' => 'required|string',
        ]);

        try {

            $query = $this->service->getCurrentQuery($request);
            if(!$query) return bodyResponseRequest(EnumResponse::ACCEPTED, false);
            //get settings
            $hotel = $request->attributes->get('hotel');
            
            return bodyResponseRequest(EnumResponse::ACCEPTED, $query);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getCurrentQuery');
        }
    }

}
