<?php

namespace App\Http\Controllers\Api\Hoster;

use App\Http\Controllers\Controller;
use App\Http\Resources\HotelResource;
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

    public function updateContactPhones (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->updateProfileData($hotel, $request, ['phone', 'phone_optional']);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        $data = new HotelResource($model);
        return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
    }

    public function updateContactEmail (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->updateProfileData($hotel, $request, ['contact_email']);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        $data = new HotelResource($model);
        return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
    }

    public function updateContactWhatsapp    (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->updateProfileData($hotel, $request, ['contact_whatsapp_number']);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        $data = new HotelResource($model);
        return bodyResponseRequest(EnumResponse::ACCEPTED, $data);
    }

    public function getProfilePhones (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->getProfileData($hotel->id, ['phone', 'phone_optional']);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        return bodyResponseRequest(EnumResponse::SUCCESS, $model);
    }

    public function getProfileEmail (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->getProfileData($hotel->id, ['contact_email']);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        return bodyResponseRequest(EnumResponse::SUCCESS, $model);
    }

    public function getProfileWhatsapp (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->getProfileData($hotel->id, ['contact_whatsapp_number']);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }
        return bodyResponseRequest(EnumResponse::SUCCESS, $model);
    }

    public function toggleShowContact (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->toggleShowContact($hotel->id, $request->enabled);
        if(!$model){
            $data = [
                'message' => __('response.bad_request_long')        
            ];
            return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
        }   
        return bodyResponseRequest(EnumResponse::SUCCESS, $model);
    }

    public function getShowContact (Request $request) {
        $hotel = $request->attributes->get('hotel');
        $model = $this->services->getShowContact($hotel->id);
        return bodyResponseRequest(EnumResponse::SUCCESS, $model);
    }

    public function getOrderSections (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->services->getOrderSections($hotel->id);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')        
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::SUCCESS, $model);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), [], self::class . '.getOrderSections');
        }
    }

    public function updateOrderSections (Request $request) {
        try {
            $hotel = $request->attributes->get('hotel');
            $model = $this->services->updateOrderSections($hotel->id, $request->orderSections);
            if(!$model){
                $data = [
                    'message' => __('response.bad_request_long')        
                ];
                return bodyResponseRequest(EnumResponse::NOT_FOUND, $data);
            }
            return bodyResponseRequest(EnumResponse::SUCCESS, $model);
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e->getMessage(), [], self::class . '.updateOrderSections');
        }
    }
    
    
}
