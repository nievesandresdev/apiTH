<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StayResource;
use Illuminate\Http\Request;

use App\Utils\Enums\EnumResponse;
use App\Services\StayService;
class StayController extends Controller
{
    public $service;

    function __construct(
        StayService $_StayService
    )
    {
        $this->service = $_StayService;
    }

    public function findAndValidAccess (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->findAndValidAccess($request->stayId,$hotel);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.findAndValidAccess');
        }
    }

    public function createAndInviteGuest (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->service->createAndInviteGuest($hotel,$request);
            return response()->json($model);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);  
            }
            $data = new StayResource($model);
            return bodyResponseRequest(EnumResponse::ACCEPTED, $data);

        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.store');
        }
    }

   

}
