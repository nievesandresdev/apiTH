<?php

namespace App\Services\Hoster\Hotel;

use App\Models\Customization;
use App\Models\Chain;
use App\Models\Hotel;
use App\Models\ImagesHotels;
use App\Services\Hoster\Chat\ChatSettingsServices;
use App\Utils\Enums\EnumResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use stdClass;

class HotelHosterServices
{
    public $chatSettingsService;

    public function __construct(ChatSettingsServices $chatSettingsService) {
        $this->chatSettingsService = $chatSettingsService;
    }

    public function deleteImageByHotel ($hotelId, $imageId) {

        try {
            return ImagesHotels::where('hotel_id',$hotelId)->where('id',$imageId)->delete();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisibility');
        }
    }

    public function toggleChatService ($hotelId, $enabled) {
        try {
            $hotel = Hotel::find($hotelId); 
            if (!$hotel) {
                return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.toggleChatService');
            }
            
            if ($enabled) {
                $hotel->chat_service_enabled = true;
            } else {
                $hotel->chat_service_enabled = false;
            }
            $settings = new stdClass();
            $settings->show_guest = $enabled ? true : false;
            $this->chatSettingsService->updateSettings($hotelId, ['show_guest'], $settings);
            $hotel->save();
            return $hotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.toggleChatService');
        }
    }
   
    public function toggleCheckinService ($hotelId, $enabled) {
        try {
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.toggleCheckinService');
            }
            if ($enabled) {
                $hotel->checkin_service_enabled = true;
                $hotel->show_checkin_stay = true;
            } else {
                $hotel->checkin_service_enabled = false;
                $hotel->show_checkin_stay = false;
            }
            $hotel->save();
            return $hotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.toggleCheckinService'); 
        }
    }

    public function toggleReviewsService ($hotelId, $enabled) {
        try {
            $hotel = Hotel::find($hotelId);
            if (!$hotel) {
                return bodyResponseRequest(EnumResponse::ERROR, 'Hotel not found', [], self::class . '.toggleReviewsService');
            }
            if ($enabled) {
                $hotel->reviews_service_enabled = true;
                
            } else {
                $hotel->reviews_service_enabled = false;
            }
            $hotel->save();
            return $hotel;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.toggleReviewsService');
        }
    }
}
