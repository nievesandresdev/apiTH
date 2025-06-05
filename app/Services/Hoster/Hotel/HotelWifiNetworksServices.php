<?php

namespace App\Services\Hoster\Hotel;

use App\Models\HotelWifiNetworks;
use App\Utils\Enums\EnumResponse;
use Illuminate\Support\Facades\Log;

class HotelWifiNetworksServices
{

    public function getAllByHotel($hotelId){

        try{
            return HotelWifiNetworks::where('hotel_id', $hotelId)->get();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllByHotel');
        }
    }

    public function getAllByHotelAndVisible($hotelId){
        try{
            return HotelWifiNetworks::where('hotel_id', $hotelId)->where('visible', 1)->get();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.getAllByHotelAndVisible');
        }
    }

    public function store($data, $hotelId){

        try{
            $save = new HotelWifiNetworks();

            $save->hotel_id = $hotelId;
            $save->name = $data->name;
            $save->password = $data->password;

            return $save->save();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.store');
        }
    }

    public function updateById($data, $networkId){

        try{
            $update = HotelWifiNetworks::find($networkId);

            $update->name = $data->name;
            $update->password = $data->password;

            return $update->save();
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.update');
        }
    }

    public function updateVisibilityNetwork ($networkId, $value) {
        try {
            Log::info("networkId ".json_encode($networkId));
            Log::info("value ".json_encode($value));
            $update = HotelWifiNetworks::find($networkId);
            if(!$update) return;
            $update->visible = $value;
            $save = $update->save();
            return $save;
        } catch (\Exception $e) {
            return bodyResponseRequest(EnumResponse::ERROR, $e, [], self::class . '.updateVisibility');
        }
    }

}