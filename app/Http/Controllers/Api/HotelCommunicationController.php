<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\HotelCommunicationServices;
use App\Http\Controllers\Controller;
use App\Utils\Enums\EnumResponse;


class HotelCommunicationController extends Controller
{
    protected $hotelCommunicationServices;


    public function __construct(HotelCommunicationServices $hotelCommunicationServices)
    {
        $this->hotelCommunicationServices = $hotelCommunicationServices;
    }

    public function getHotelCommunication(Request $request)
    {
        $hotel = $request->attributes->get('hotel');
        $hotelCommunication = $this->hotelCommunicationServices->getHotelCommunication($hotel->id,'email');
        return bodyResponseRequest(EnumResponse::ACCEPTED, $hotelCommunication);
    }

    public function updateHotelCommunication($hotelId, Request $request)
    {
        $this->hotelCommunicationServices->updateHotelCommunication($hotelId, $request->all());
        return bodyResponseRequest(EnumResponse::ACCEPTED, ['message' => 'Hotel communication updated successfully']);
    }


}
