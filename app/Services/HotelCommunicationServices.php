<?php

namespace App\Services;

use App\Models\HotelCommunication;

class HotelCommunicationServices
{

    public function getHotelCommunication($hotelId, $type = null)
    {
        $query =  HotelCommunication::where('hotel_id', $hotelId)->type($type)->get();

        if($query->isEmpty()){
            return getDefaultHotelCommunications();
        }

        $map = [];

        foreach ($query as $item) {
            $map[$item->type] = $item;
        }

        return $map;
    }

    public function updateOrStoreHotelCommunication($hotelId, $data)
    {
      $hotelCommunication = HotelCommunication::updateOrCreate(
        ['hotel_id' => $hotelId, 'type' => $data['type']],
        $data['options']
      );

      return $hotelCommunication;
    }
}