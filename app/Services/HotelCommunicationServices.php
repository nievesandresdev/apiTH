<?php

namespace App\Services;

use App\Models\HotelCommunication;

class HotelCommunicationServices
{

    public function getHotelCommunication($hotelId)
    {
        return HotelCommunication::where('hotel_id', $hotelId)->first();
    }

    public function updateHotelCommunication($hotelId, $data)
    {
        $hotelCommunication = HotelCommunication::where('hotel_id', $hotelId)->first();
        $hotelCommunication->update($data);
    }
}