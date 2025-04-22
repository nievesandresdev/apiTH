<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Services\Hoster\Hotel\HotelHosterServices;
use Illuminate\Http\Request;
use App\Utils\Enums\EnumResponse;

class HotelHosterController extends Controller
{
    public $services;
    public function __construct(
        HotelHosterServices $_HotelHosterServices
        )
    {
        $this->services = $_HotelHosterServices;
    }

    public function deleteImageByHotel (Request $request) {
        $hotel = $request->attributes->get('hotel');

        $model = $this->services->deleteImageByHotel($hotel->id, $request->imageId);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        return bodyResponseRequest(EnumResponse::ACCEPTED, $model);

        return bodyResponseRequest(EnumResponse::SUCCESS, $data);
    }

    public function toggleChatService (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->toggleChatService($hotel->id, $request->enabled);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $model);
        }
        return bodyResponseRequest(EnumResponse::ACCEPTED, $model);
    }

    public function toggleCheckinService (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->toggleCheckinService($hotel->id, $request->enabled);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        return bodyResponseRequest(EnumResponse::ACCEPTED, $model);
    }

    public function toggleReviewsService (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->toggleReviewsService($hotel->id, $request->enabled);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        return bodyResponseRequest(EnumResponse::ACCEPTED, $model);
    }
    
}
