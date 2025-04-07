<?php

namespace App\Services\Hoster\Hotel;

use App\Models\Customization;
use App\Models\Chain;
use App\Models\ImagesHotels;
use App\Utils\Enums\EnumResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelHosterServices
{
    public function deleteImageByHotel ($hotelId, $imageId) {

        try {
            return ImagesHotels::where('hotel_id',$hotelId)->where('id',$imageId)->delete();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisibility');
        }
    }
   
}
