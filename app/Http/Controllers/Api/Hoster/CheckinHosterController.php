<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\Hoster\CheckinSettingsHosterResource;
use App\Services\Hoster\Stay\CheckinHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\DB;

class CheckinHosterController extends Controller
{
    public $service;

    function __construct(
        CheckinHosterServices $_CheckinHosterServices
    )
    {
        $this->service = $_CheckinHosterServices;
    }
    
    public function updateGeneralSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, ['succes_message'], $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateGeneralSettings');
        }
    }

    public function getGeneralSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new CheckinSettingsHosterResource($model,['succes_message']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGeneralSettings');
        }
    }

    public function updateFormSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateSettings($hotel->id, ["first_step","second_step","show_prestay_query"], $request);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateFormSettings');
        }
    }

    public function getFormSettings(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getAll($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            $model = new CheckinSettingsHosterResource($model,['first_step','second_step','show_prestay_query']);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getFormSettings');
        }
    }

    public function updateToggleShowCheckinHotel(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->updateToggleShowCheckinHotel($hotel->id, $request->value);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateToggleShowCheckinHotel');
        }
    }

    public function getToggleShowCheckinHotel(Request $request){
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->getToggleShowCheckinHotel($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getToggleShowCheckinHotel');
        }
    }

    //operation
    public function getGuestsForTabsCheckinStay(Request $request){
        try {
            $model = $this->service->getGuestsForTabsCheckinStay($request->stayId);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }   
            return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getGuestsForTabsCheckinStay');
        }
    }
    
}
