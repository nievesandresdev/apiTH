<?php

namespace App\Services;

use App\Models\HotelCommunication;

class HotelCommunicationServices
{

    public function getHotelCommunication($hotelId, $type = null)
    {
        $query =  HotelCommunication::where('hotel_id', $hotelId)
            ->where('type', $type)
            ->get();

        if($query->isEmpty()){
            return getDefaultHotelCommunications();
        }

        return $query;
    }

    public function updateHotelCommunication($hotelId, $data)
    {
        $hotelCommunication = HotelCommunication::where('hotel_id', $hotelId)->first();
        $hotelCommunication->update($data);
    }
}